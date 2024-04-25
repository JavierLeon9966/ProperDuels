<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\PacketHooker;

use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\command\arena\ArenaCommand;
use JavierLeon9966\ProperDuels\command\duel\DuelCommand;
use JavierLeon9966\ProperDuels\command\kit\KitCommand;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\session\SessionManager;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\poggit\libasynql\libasynql;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

use Symfony\Component\Filesystem\Path;

final class ProperDuels extends PluginBase{
	private static $instance = null;

	private $arenaManager;

	private $kitManager;

	private $gameManager = null;

	private $queueManager;

	private $sessionManager = null;

	public static function getInstance(): ?self{
		return self::$instance;
	}

	public function getArenaManager(): ArenaManager{
		return $this->arenaManager;
	}

	public function getGameManager(): ?GameManager{
		return $this->gameManager;
	}

	public function getKitManager(): KitManager{
		return $this->kitManager;
	}

	public function getQueueManager(): QueueManager{
		return $this->queueManager;
	}

	public function getSessionManager(): ?SessionManager{
		return $this->sessionManager;
	}

	public function onLoad(): void{
		self::$instance = $this;

		$config = $this->getConfig();
		$config->setDefaults([
			'database' => [
				'type' => 'sqlite3',
				'mysql' => [
					'host' => '127.0.0.1',
					'username' => 'ProperDuels',
					'password' => 'mypassword123',
					'schema' => 'ProperDuels',
					'port' => 3306
				],
				'worker-limit' => 1
			],
			'match' => [
				'allow-commands' => false,
				'countdown' => [
					'message' => TextFormat::AQUA.'Match starting in '.TextFormat::BLUE.'{seconds}',
					'time' => 5
				],
				'failure' => [
					'levelNotFound' => TextFormat::RED.'Couldn\'t start match as no level was found',
					'kitNotFound' => TextFormat::RED.'Couldn\'t start match as no kit was found'
				],
				'finish' => TextFormat::GREEN.'{winner}'.TextFormat::GRAY.' won a match against '.TextFormat::RED.'{defeated}'.TextFormat::GRAY.' with type '.TextFormat::BLUE.'{arena}',
				'inUse' => TextFormat::RED.'There is currently a match in that arena!',
				'start' => TextFormat::GREEN.'Duel!'
			],
			'request' => [
				'accept' => [
					'message' => '{player} '.TextFormat::GREEN.'accepted the Duel request!',
					'playerInDuel' => TextFormat::RED.'You cannot accept this player to a Duel!',
					'success' => TextFormat::GREEN.'You accepted '.TextFormat::RESET.'{player}'.TextFormat::GREEN.'\'s Duel request!'
				],
				'deny' => [
					'message' => '{player} '.TextFormat::RED.'denied the Duel request!',
					'success' => TextFormat::RED.'You denied '.TextFormat::RESET.'{player}'.TextFormat::RED.'\'s Duel request!'
				],
				'expire' => [
					'from' => TextFormat::YELLOW.'The Duel request from '.TextFormat::RESET.'{player}'.TextFormat::YELLOW.' has expired.',
					'to' => TextFormat::YELLOW.'The Duel request to '.TextFormat::RESET.'{player}'.TextFormat::YELLOW.' has expired.',
					'time' => 60
				],
				'invite' => [
					'failure' => TextFormat::RED.'You have already invited to Duel!',
					'message' => '{player}'.TextFormat::AQUA.' has invited you to {arena} Duels! You have {seconds} seconds to accept.',
					'playerInDuel' => TextFormat::RED.'You cannot invite this player to a Duel!',
					'playerNotFound' => TextFormat::RED.'You haven\'t been invited to Duel, or the invitation has expired!',
					'sameTarget' => TextFormat::RED.'You can\'t send a invite to yourself!',
					'success' => TextFormat::YELLOW.'You invited '.TextFormat::RESET.'{player}'.TextFormat::YELLOW.' to {arena} Duels! They have {seconds} seconds to accept.'
				]
			]
		]);
		$config->save();

		$statements = [
			'sqlite' => Path::join('sqlite', 'stmt.sql'),
			'mysql' => Path::join('mysql', 'stmt.sql')
		];

		$databaseConfig = $config->get('database');
		$arenaData = $databaseConfig;
		$arenaData['sqlite']['file'] = 'arenas.sqlite';
		$this->arenaManager = new ArenaManager(libasynql::create(
			$this,
			$arenaData,
			$statements
		));

		$kitData = $databaseConfig;
		$kitData['sqlite']['file'] = 'kits.sqlite';
		$this->kitManager = new KitManager(libasynql::create(
			$this,
			$kitData,
			$statements
		));

		$this->queueManager = new QueueManager;

	}

	public function onEnable(): void{
		if(!PacketHooker::isRegistered()) PacketHooker::register($this);

		$this->gameManager = $gameManager = new GameManager($this);
		$this->sessionManager = new SessionManager($this);
		$this->getServer()->getCommandMap()->registerAll('properduels', [
			new ArenaCommand(
				$this,
				'arena',
				$this->arenaManager,
				$this->kitManager,
				'Manage arenas for duel matches.'
			),
			new DuelCommand(
				$this,
				'duel',
				$this->getConfig(),
				$this->sessionManager,
				$gameManager,
				$this->arenaManager,
				$this->queueManager,
				'Duel players and queue to a match.'
			),
			new KitCommand(
				$this,
				'kit',
				$this->kitManager,
				'Manage kits for duel matches.'
			)
		]);
	}

	public function onDisable(): void{
		self::$instance = null;

		$this->arenaManager->close();
		$this->kitManager->close();
		$this->gameManager->close();
		$this->sessionManager->close();
	}
}