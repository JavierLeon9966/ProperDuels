<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\game\Game;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

final class QueueManager{

	private $queues = [];

	public function add(string $rawUUID, ?Arena $arena = null): void{
		$arenaManager = ProperDuels::getInstance()->getArenaManager();
		$this->queues[$rawUUID] = $arena ?? (count($this->queues) === 0 ? $arenaManager->get(array_rand($arenaManager->all())) : $this->queues[array_rand($this->queues)]);

		$this->update();
	}

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

	public function update(): void{
		$properDuels = ProperDuels::getInstance();
		$gameManager = $properDuels->getGameManager();
		foreach(array_unique($this->queues, \SORT_REGULAR) as $arena){
			if(!$gameManager->has($arena->getName())){
				$sessions = [];
				foreach(array_slice(array_keys($this->queues, $arena, true), 0, 2) as $rawUUID){
					$session = $properDuels->getSessionManager()->get($rawUUID);
					if($session === null){
						unset($this->queues[$rawUUID]);
						continue 2;
					}

					$sessions[] = $session;
				}

				if(count($sessions) > 1){
					$gameManager->add(new Game($arena, $sessions));
				}
			}
		}
	}
}
