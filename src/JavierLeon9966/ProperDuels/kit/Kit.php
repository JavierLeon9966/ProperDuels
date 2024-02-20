<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use pocketmine\item\Item;
use pocketmine\utils\Utils;

final class Kit{
	private $name;

	private $armor;

	private $inventory;

	public function __construct(string $name, array $armor, array $inventory){
		$this->name = $name;

		Utils::validateArrayValueType(array_merge($armor, $inventory), static function(Item $_): void{});
		$this->armor = Utils::cloneObjectArray($armor);
		$this->inventory = Utils::cloneObjectArray($inventory);
	}

	public function getArmor(): array{
		return Utils::cloneObjectArray($this->armor);
	}

	public function getInventory(): array{
		return Utils::cloneObjectArray($this->inventory);
	}

	public function getName(): string{
		return $this->name;
	}

	public function __unserialize(array $data): void{
		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'legacyJsonDeserialize']);

		$this->armor = array_map($itemDeserializerFunc, $data['armor']);
		$this->inventory = array_map($itemDeserializerFunc, $data['inventory']);

		$this->name = $data['name'];
	}
}