<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;

final readonly class Kit{

	/** @var array<int, Item> */
	private array $armor;

	/** @var array<int, Item> */
	private array $inventory;

	/**
	 * @param array<int, Item> $armor
	 * @param array<int, Item> $inventory
	 */
	public function __construct(private string $name, array $armor, array $inventory){
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

	public function getName(): string{
		return $this->name;
	}

	/** @param array{armor: array<int, array<array-key, mixed>>, inventory: array<int, array<array-key, mixed>>, name: string} $data */
	public function __unserialize(array $data): void{
		$itemDeserializerFunc = Item::legacyJsonDeserialize(...);

		try{
			$this->armor = array_map($itemDeserializerFunc, $data['armor']);
			$this->inventory = array_map($itemDeserializerFunc, $data['inventory']);
		}catch(SavedDataLoadingException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		$this->name = $data['name'];
	}
}
