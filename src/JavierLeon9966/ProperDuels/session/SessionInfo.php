<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use pocketmine\item\Item;
use pocketmine\utils\Utils;


final class SessionInfo{

	/** @var array<int, Item> */
	private array $armor;

	/** @var array<int, Item> */
	private array $inventory;

	/**
	 * @param array<int, Item> $armor
	 * @param array<int, Item> $inventory
	 */
	public function __construct(array $armor, array $inventory, private readonly int $totalXp){
		Utils::validateArrayValueType(array_merge($armor, $inventory), static function(Item $_): void{});
		$this->armor = Utils::cloneObjectArray($armor);
		$this->inventory = Utils::cloneObjectArray($inventory);
	}

	/** @return array<int, Item> */
	public function getArmor(): array{
		return Utils::cloneObjectArray($this->armor);
	}

	/** @return array<int, Item> */
	public function getInventory(): array{
		return Utils::cloneObjectArray($this->inventory);
	}

	public function getTotalXp(): int{
		return $this->totalXp;
	}
}