<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\Game;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\plugin\Plugin;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\WorldManager;
use const SORT_REGULAR;

final class QueueManager{

	/** @var array<string, Arena> */
	private array $queues = [];

	public function __construct(
		private readonly GameManager $gameManager,
		private readonly SessionManager $sessionManager,
		private readonly KitManager $kitManager,
		private readonly WorldManager $worldManager,
		private readonly Plugin $plugin,
		private readonly Config $config,
	){
	}

	/** @throws \RuntimeException */
	public function add(string $rawUUID, Arena $arena): void{
		$this->queues[$rawUUID] = $arena;

		$this->update();
	}

	/** @return array<string, Arena> */
	public function all(): array{
		return $this->queues;
	}

	public function get(string $rawUUID): ?Arena{
		return $this->queues[$rawUUID] ?? null;
	}
	
	public function has(string $rawUUID): bool{
		return isset($this->queues[$rawUUID]);
	}

	public function remove(string $rawUUID): void{
		unset($this->queues[$rawUUID]);
	}

	/** @throws \RuntimeException */
	public function update(): void{
		$arenas = array_map(fn(Arena $arena) => $arena->getName(), $this->queues);
		foreach(array_unique($arenas, SORT_REGULAR) as $k => $arenaName){
			if(!$this->gameManager->has($arenaName)){
				$sessions = [];
				foreach(array_slice(array_keys($arenas, $arenaName, true), 0, 2) as $rawUUID){
					if(!is_string($rawUUID)){
						throw new AssumptionFailedError('This should never happen');
					}
					$session = $this->sessionManager->get($rawUUID);
					if($session === null){
						unset($this->queues[$rawUUID]);
						continue 2;
					}

					$sessions[] = $session;
				}

				if(count($sessions) === 2){
					$this->gameManager->add(new Game(
						$this->config,
						$this->gameManager,
						$this->kitManager,
						$this->worldManager,
						$this,
						$this->plugin,
						$this->queues[$k],
						[$sessions[0], $sessions[1]]
					));
				}
			}
		}
	}
}
