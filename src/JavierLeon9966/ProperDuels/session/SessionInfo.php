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
		$this->armor = array_map(static function(Item $item): Item{ return clone $item; }, $armor);
		$this->inventory = array_map(static function(Item $item): Item{ return clone $item; }, $inventory);

		$this->totalXp = $totalXp;
	}

	public function getArmor(): array{
		return array_map(static function(Item $item): Item{ return clone $item; }, $this->armor);
	}
	
	public function getInventory(): array{
		return array_map(static function(Item $item): Item{ return clone $item; }, $this->inventory);
	}

	public function getTotalXp(): int{
		return $this->totalXp;
	}
}
