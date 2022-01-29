<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use pocketmine\item\Item;
use pocketmine\utils\Utils;


final class SessionInfo{
	private $armor;

	private $inventory;

	private $totalXp;

	public function __construct(array $armor, array $inventory, int $totalXp){
		Utils::validateArrayValueType(array_merge($armor, $inventory), static function(Item $_): void{});
		$this->armor = Utils::cloneObjectArray($armor);
		$this->inventory = Utils::cloneObjectArray($inventory);

		$this->totalXp = $totalXp;
	}

	public function getArmor(): array{
		return Utils::cloneObjectArray($this->armor);
	}
	
	public function getInventory(): array{
		return Utils::cloneObjectArray($this->inventory);
	}

	public function getTotalXp(): int{
		return $this->totalXp;
	}
}
