<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\command\arena\ArenaCommand;
use JavierLeon9966\ProperDuels\command\duel\DuelCommand;
use JavierLeon9966\ProperDuels\command\kit\KitCommand;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\GameListener;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\session\SessionListener;
use JavierLeon9966\ProperDuels\session\SessionManager;
use JsonException;
use JsonMapper;
use JsonMapper_Exception;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\ConfigLoadException;
use poggit\libasynql\ExtensionMissingException;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use SOFe\InfoAPI\InfoAPI;
use Symfony\Component\Filesystem\Path;

final class ProperDuels extends PluginBase{

	private static ArenaManager $arenaManager;

	private static KitManager $kitManager;

	private static GameManager $gameManager;

	private static QueueManager $queueManager;

	private static SessionManager $sessionManager;

	public static function getArenaManager(): ArenaManager{
		return self::$arenaManager;
	}

	public static function getGameManager(): GameManager{
		return self::$gameManager;
	}

	public static function getKitManager(): KitManager{
		return self::$kitManager;
	}

	public static function getQueueManager(): QueueManager{
		return self::$queueManager;
	}

	public static function getSessionManager(): SessionManager{
		return self::$sessionManager;
	}

	/** @throws DisablePluginException */
	public function onEnable(): void{
		InfoAPI::addKind($this, 'properduels/arena', static fn(Arena $arena, ?CommandSender $sender): string => $arena->getName(), 'Arena', 'A duel arena');
		InfoAPI::addMapping($this, ['firstSpawnPosition', 'firstSpawnPos'], static fn(Arena $arena): Vector3 => $arena->getFirstSpawnPos(), help: 'Arena first spawn position');
		InfoAPI::addMapping($this, ['secondSpawnPosition', 'secondSpawnPos'], static fn(Arena $arena): Vector3 => $arena->getSecondSpawnPos(), help: 'Arena second spawn position');
		InfoAPI::addMapping($this, 'kit', static fn(Arena $arena): string => $arena->getKit() ?? 'Random', help: 'Arena kit');

		try{
			$config = $this->getConfig();
		}catch(ConfigLoadException $e){
			$this->getLogger()->error($e->getMessage());
			throw new DisablePluginException();
		}
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
					'message' => '{aqua}Match starting in {blue}{seconds}',
					'time' => 5
				],
				'failure' => [
					'levelNotFound' => '{red}Couldn\'t start match as no level was found',
					'kitNotFound' => '{red}Couldn\'t start match as no kit was found'
				],
				'finish' => '{green}{winner}{gray} won a match against {red}{defeated}{gray} with type {blue}{arena}',
				'inUse' => '{red}There is currently a match in that arena!',
				'start' => '{green}Duel!'
			],
			'request' => [
				'accept' => [
					'message' => '{player} {green}accepted the Duel request!',
					'playerInDuel' => '{red}You cannot accept this player to a Duel!',
					'success' => '{green}You accepted {white}{player}{green}\'s Duel request!'
				],
				'deny' => [
					'message' => '{player} {red}denied the Duel request!',
					'success' => '{red}You denied {white}{player}{red}\'s Duel request!'
				],
				'expire' => [
					'from' => '{yellow}The Duel request from {white}{player}{yellow} has expired.',
					'to' => '{yellow}The Duel request to {white}{player}{yellow} has expired.',
					'time' => 60
				],
				'invite' => [
					'failure' => '{red}You have already invited to Duel!',
					'message' => '{player}{aqua} has invited you to {arena} Duels! You have {seconds} seconds to accept.',
					'playerInDuel' => '{red}You cannot invite this player to a Duel!',
					'playerNotFound' => '{red}You haven\'t been invited to Duel, or the invitation has expired!',
					'sameTarget' => '{red}You can\'t send a invite to yourself!',
					'success' => '{yellow}You invited {white}{player}{yellow} to {arena} Duels! They have {seconds} seconds to accept.'
				]
			]
		]);
		try{
			if($config->hasChanged()){
				$config->save();
			}
		}catch(JsonException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		$statements = [
			'sqlite' => Path::join('sqlite', 'stmt.sql'),
			'mysql' => Path::join('mysql', 'stmt.sql')
		];

		$mapper = new JsonMapper();
		$mapper->bEnforceMapType = false;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			/** @var Config $unMarshaledConfig */
			$unMarshaledConfig = $mapper->map($config->getAll(), Config::class);
		}catch(JsonMapper_Exception $e){
			$this->getLogger()->error("Configuration error: {$e->getMessage()}");
			throw new DisablePluginException();
		}
		try{
			self::$arenaManager = new ArenaManager(libasynql::create(
				$this,
				[
					'type' => $unMarshaledConfig->database->type->value,
					'sqlite' => [
						'file' => 'arenas.sqlite'
					],
					'mysql' => [
						'host' => $unMarshaledConfig->database->mysql->host,
						'username' => $unMarshaledConfig->database->mysql->username,
						'password' => $unMarshaledConfig->database->mysql->password,
						'schema' => $unMarshaledConfig->database->mysql->schema,
						'port' => $unMarshaledConfig->database->mysql->port
					],
					'worker-limit' => $unMarshaledConfig->database->workerLimit
				],
				$statements
			));

			self::$kitManager = new KitManager(libasynql::create(
				$this,
				[
					'type' => $unMarshaledConfig->database->type->value,
					'sqlite' => [
						'file' => 'kits.sqlite'
					],
					'mysql' => [
						'host' => $unMarshaledConfig->database->mysql->host,
						'username' => $unMarshaledConfig->database->mysql->username,
						'password' => $unMarshaledConfig->database->mysql->password,
						'schema' => $unMarshaledConfig->database->mysql->schema,
						'port' => $unMarshaledConfig->database->mysql->port
					],
					'worker-limit' => $unMarshaledConfig->database->workerLimit
				],
				$statements
			));
		}catch(ExtensionMissingException|SqlError $e){
			$this->getLogger()->error($e->getMessage());
			throw new DisablePluginException;
		}

		self::$gameManager = new GameManager();
		$server = $this->getServer();
		$pluginManager = $server->getPluginManager();
		self::$sessionManager = new SessionManager(
			self::$arenaManager,
			self::$gameManager,
			$unMarshaledConfig,
			$this,
			$pluginManager
		);
		self::$queueManager = new QueueManager(
			self::$arenaManager,
			self::$gameManager,
			self::$sessionManager,
			self::$kitManager,
			$server->getWorldManager(),
			$this,
			$unMarshaledConfig
		);
		try{
			$pluginManager->registerEvents(new GameListener($unMarshaledConfig, self::$sessionManager), $this);
			$pluginManager->registerEvents(new SessionListener(self::$sessionManager), $this);
		}catch(PluginException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		if(!PacketHooker::isRegistered()){
			try{
				PacketHooker::register($this);
			}catch(HookAlreadyRegistered $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}

		$server->getCommandMap()->registerAll('properduels', [
			new ArenaCommand(
				$this,
				'arena',
				self::$arenaManager,
				self::$kitManager,
				'Manage arenas for duel matches.'
			),
			new DuelCommand(
				$this,
				'duel',
				$unMarshaledConfig,
				self::$sessionManager,
				self::$gameManager,
				self::$arenaManager,
				self::$queueManager,
				self::$kitManager,
				'Duel players and queue to a match.'
			),
			new KitCommand(
				$this,
				'kit',
				self::$kitManager,
				'Manage kits for duel matches.'
			)
		]);
	}

	/** @throws \RuntimeException */
	public function onDisable(): void{
		if(isset(self::$arenaManager)){
			self::$arenaManager->close();
		}
		if(isset(self::$kitManager)){
			self::$kitManager->close();
		}
		if(isset(self::$gameManager)){
			self::$gameManager->close();
		}
		if(isset(self::$sessionManager)){
			self::$sessionManager->close();
		}
	}
}
