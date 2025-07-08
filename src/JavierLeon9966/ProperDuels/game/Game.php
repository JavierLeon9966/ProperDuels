<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

use Generator;
use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\event\GameFinishEvent;
use JavierLeon9966\ProperDuels\event\GameStartEvent;
use JavierLeon9966\ProperDuels\event\GameStopEvent;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\QueueManager;
use JavierLeon9966\ProperDuels\session\Session;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\Position;
use pocketmine\world\WorldManager;
use SOFe\AwaitGenerator\Await;
use SOFe\InfoAPI\InfoAPI;
use SOFe\Zleep\Zleep;

final class Game{

	private bool $started = false;

	/**
	 * @var array<int, Session>
	 * @phpstan-var array{0?: Session, 1?: Session}
	 */
	private array $sessions;

	/**
	 * @param array<int, Session> $sessions
	 * @phpstan-param array{Session, Session} $sessions
	 */
	public function __construct(
		private readonly Config $config,
		private readonly GameManager $gameManager,
		private readonly KitManager $kitManager,
		private readonly WorldManager $worldManager,
		private readonly QueueManager $queueManager,
		private readonly Plugin $plugin,
		private readonly Arena $arena,
		array $sessions
	){
		Utils::validateArrayValueType($sessions, static function(Session $_): void{});
		$this->sessions = $sessions;
	}

	public function getArena(): Arena{
		return $this->arena;
	}

	/**
	 * @return array<int, Session>
	 * @phpstan-return array{0?: Session, 1?: Session}
	 */
	public function getSessions(): array{
		return $this->sessions;
	}

	public function hasStarted(): bool{
		return $this->started;
	}

	/** @throws \RuntimeException */
	public function start(): void{
		Await::f2c(function(): Generator{
			if($this->started){
				return;
			}
			$this->started = true;

			$secondSession = $this->sessions[1] ?? throw new AssumptionFailedError('This should never happen');
			$firstSession = $this->sessions[0] ?? throw new AssumptionFailedError('This should never happen');
			$secondSession->removeInvite($firstSession->getPlayer()->getUniqueId()->getBytes());
			$secondSession->setGame($this);
			$firstSession->removeInvite($secondSession->getPlayer()->getUniqueId()->getBytes());
			$firstSession->setGame($this);

			$arenaName = $this->arena->getName();

			$world = $this->worldManager->getWorldByName($this->arena->getLevelName());
			if($world === null){
				$this->gameManager->remove($arenaName);
				foreach($this->sessions as $session){
					$session->setGame(null);
					$player2 = $session->getPlayer();
					$player2->sendMessage(InfoAPI::render($this->plugin, $this->config->match->failure->levelNotFound, [

					], $player2));
				}
				return;
			}

			$kitName = $this->arena->getKit();
			$kit = $kitName === null ? yield from $this->kitManager->getRandom() : yield from $this->kitManager->get($kitName);
			if(!$this->started){
				return;
			}
			if($kit === null){
				$this->gameManager->remove($arenaName);
				foreach($this->sessions as $session){
					$session->setGame(null);
					$player1 = $session->getPlayer();
					$player1->sendMessage(InfoAPI::render($this->plugin, $this->config->match->failure->kitNotFound, [

					], $player1));
				}
				return;
			}

			$spawns = [
				Position::fromObject($this->arena->getFirstSpawnPos(), $world),
				Position::fromObject($this->arena->getSecondSpawnPos(), $world)
			];

			foreach($this->sessions as $session){
				$player = $session->getPlayer();
				$player->removeCurrentWindow();
				$session->saveInfo();

				$this->queueManager->remove($player->getUniqueId()->getBytes());

				$player->teleport(array_shift($spawns) ?? throw new AssumptionFailedError('This should never happen'));

				$player->getArmorInventory()->setContents($kit->getArmor());
				$player->getInventory()->setContents($kit->getInventory());

				$player->getXpManager()->setCurrentTotalXp(0);

				$player->extinguish();
				$player->setAirSupplyTicks($player->getMaxAirSupplyTicks());
				$player->noDamageTicks = Server::TARGET_TICKS_PER_SECOND * $this->config->match->countdown->time;

				$player->getEffects()->clear();
				$player->setHealth($player->getMaxHealth());

				foreach($player->getAttributeMap()->getAll() as $attr){
					$attr->resetToDefault();
				}

				$player->setNoClientPredictions();
			}

			for($i = 0; $i < $this->config->match->countdown->time; $i++){
				foreach($this->sessions as $session){
					$player = $session->getPlayer();
					$player->sendMessage(InfoAPI::render($this->plugin, $this->config->match->countdown->message, [
						'seconds'  => $this->config->match->countdown->time - $i
					], $player));
				}
				yield from Zleep::sleepSeconds($this->plugin, 1.0);
			}

			foreach($this->sessions as $session){
				$player = $session->getPlayer();

				$player->setNoClientPredictions(false);

				$player->sendMessage(InfoAPI::render($this->plugin, $this->config->match->start, [], $player));
			}

			(new GameStartEvent($this, $firstSession->getPlayer(), $secondSession->getPlayer()))->call();
		});
	}

	/** @throws \RuntimeException */
	public function stop(?Session $defeated = null): void{
		if(!$this->started){
			return;
		}

		$this->started = false;

		if($defeated === null){
			(new GameStopEvent($this))->call();
		}else{
			foreach($this->sessions as $session){
				if($session !== $defeated){
					(new GameFinishEvent($this, $session->getPlayer(), $defeated->getPlayer()))->call();
				}
			}
		}


		foreach($this->sessions as $key => $session){
			$info = $session->getInfo();

			$player = $session->getPlayer();
			$player->getArmorInventory()->setContents($info->getArmor());
			$player->getInventory()->setContents($info->getInventory());

			$player->getXpManager()->setCurrentTotalXp($info->getTotalXp());

			if($session !== $defeated){
				$player->teleport($player->getSpawn());

				if($defeated !== null){
					$player->getServer()->broadcastMessage(InfoAPI::render($this->plugin,
						$this->config->match->finish,
						[
							'winner' => $player,
							'defeated' => $defeated->getPlayer(),
							'arena' => $this->arena
						],
						$player));
				}
			}

			$session->setGame(null);
			unset($this->sessions[$key]);
		}

		$this->gameManager->remove($this->arena->getName());

		$this->queueManager->update();
	}
}
