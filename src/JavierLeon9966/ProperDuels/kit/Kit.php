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
		$this->armor = array_map(static function(Item $item): Item{ return clone $item; }, $armor);
		$this->inventory = array_map(static function(Item $item): Item{ return clone $item; }, $inventory);
	}

	public function getArmor(): array{
		return array_map(static function(Item $item): Item{ return clone $item; }, $this->armor);
	}

	public function getInventory(): array{
		return array_map(static function(Item $item): Item{ return clone $item; }, $this->inventory);
	}

	public function getName(): string{
		return $this->name;
	}

	public function __serialize(): array{
		$itemSerializerFunc = static function(Item $item): array{ return $item->jsonSerialize(); };

		return [
			'armor' => array_map($itemSerializerFunc, $this->armor),
			'inventory' => array_map($itemSerializerFunc, $this->inventory),

			'name' => $this->name
		];
	}

	public function __unserialize(array $data): void{
		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'jsonDeserialize']);

		$this->armor = array_map($itemDeserializerFunc, $data['armor']);
		$this->inventory = array_map($itemDeserializerFunc, $data['inventory']);

		$this->name = $data['name'];
	}
}
