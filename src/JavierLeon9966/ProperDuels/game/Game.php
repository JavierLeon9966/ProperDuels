<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\ProperDuels;
use JavierLeon9966\ProperDuels\session\Session;

use pocketmine\level\Position;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Utils;

final class Game{
	private $arena;

	private $started = false;

	private $sessions;

	public function __construct(Arena $arena, array $sessions){
		$this->arena = $arena;

		Utils::validateArrayValueType($sessions, static function(Session $_): void{});
		$this->sessions = $sessions;
	}

	public function getArena(): Arena{
		return $this->arena;
	}

	public function getSessions(): array{
		return $this->sessions;
	}

	public function hasStarted(): bool{
		return $this->started;
	}

	public function start(): void{
		if($this->started){
			return;
		}

		$this->sessions[1]->removeInvite($this->sessions[0]->getPlayer()->getRawUniqueId());
		$this->sessions[0]->removeInvite($this->sessions[1]->getPlayer()->getRawUniqueId());

		$properDuels = ProperDuels::getInstance();
		$config = $properDuels->getConfig();
		$gameManager = $properDuels->getGameManager();
		$kitManager = $properDuels->getKitManager();

		$arenaName = $this->arena->getName();

		$kit = $this->arena->getKit();
		if(($kit !== null and !$kitManager->has($kit)) or count($kitManager->all()) === 0){
			$gameManager->remove($arenaName);
			foreach($this->sessions as $session){
				$session->getPlayer()->sendMessage($config->getNested('match.failure.kitNotFound'));
			}
			return;
		}
		$kit = $kitManager->get($kit !== null ? $kit : array_rand($kitManager->all()));
		
		$level = $properDuels->getServer()->getLevelByName($this->arena->getLevelName());
		if($level === null){
			$gameManager->remove($arenaName);
			foreach($this->sessions as $session){
				$session->getPlayer()->sendMessage($config->getNested('match.failure.levelNotFound'));
			}
			return;
		}

		foreach($this->sessions as $session){
			$session->setGame($this);

			$session->saveInfo();

			$player = $session->getPlayer();
			$properDuels->getQueueManager()->remove($player->getRawUniqueId());

			$player->getArmorInventory()->setContents($kit->getArmor());
			$player->getInventory()->setContents($kit->getInventory());

			$player->setCurrentTotalXp(0);

			$player->extinguish();
			$player->setAirSupplyTicks($player->getMaxAirSupplyTicks());
			$player->noDamageTicks = (int)(20 * $config->getNested('match.countdown.time'));
			
			$player->removeAllEffects();
			$player->setHealth($player->getMaxHealth());
			
			foreach($player->getAttributeMap()->getAll() as $attr){
				$attr->resetToDefault();
			}

			$player->setImmobile();
		}

		$this->sessions[0]->getPlayer()->teleport(Position::fromObject($this->arena->getFirstSpawnPos(), $level));
		$this->sessions[1]->getPlayer()->teleport(Position::fromObject($this->arena->getSecondSpawnPos(), $level));

		$task = ProperDuels::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use($config, &$task): void{
			static $countdown = null;
			if($countdown === null){
				$countdown = (int)$config->getNested('match.countdown.time');
			}

			if($countdown > 0 and $this->started){
				foreach($this->sessions as $session){
					$session->getPlayer()->sendMessage(str_replace('{seconds}', (string)$countdown, $config->getNested('match.countdown.message')));
				}

				--$countdown;
			}else{
				$task->cancel();

				foreach($this->sessions as $session){
					$player = $session->getPlayer();

					$player->setImmobile(false);

					$player->sendMessage($config->getNested('match.start'));
				}
			}
		}), 20);

		$this->started = true;
	}

	public function stop(?Session $defeated = null): void{
		if(!$this->started){
			return;
		}
		$this->started = false;

		$properDuels = ProperDuels::getInstance();

		foreach($this->sessions as $key => $session){
			$info = $session->getInfo();

			$player = $session->getPlayer();
			$player->getArmorInventory()->setContents($info->getArmor());
			$player->getInventory()->setContents($info->getInventory());

			$player->setCurrentTotalXp($info->getTotalXp());

			if($session !== $defeated){
				$player->teleport($player->getSpawn());

				if($defeated !== null){
					$player->getServer()->broadcastMessage(str_replace(
						['{winner}', '{defeated}', '{arena}'],
						[$player->getDisplayName(), $defeated->getPlayer()->getDisplayName(), $this->arena->getName()],
						$properDuels->getConfig()->getNested('match.finish')
					));
				}
			}

			$session->setGame(null);
			unset($this->sessions[$key]);
		}

		$properDuels->getGameManager()->remove($this->arena->getName());
	}
}
