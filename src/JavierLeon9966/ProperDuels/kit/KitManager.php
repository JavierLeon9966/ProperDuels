<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use JavierLeon9966\ProperDuels\utils\ContentsSerializer;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\poggit\libasynql\base\DataConnectorImpl;

final class KitManager{

	private $kits = [];

	private $database;

	public function __construct(DataConnectorImpl $database){
		$this->database = $database;
		$this->database->executeGeneric('properduels.init.kits', [], function(): void{
			$this->database->executeSelect('properduels.load.kits', [], function(array $kits): void{
				if(count($kits) === 0){
					return;
				}
				if(isset($kits[0]['Kit'])){
					$this->kits = array_map('unserialize', array_column($kits, 'Kit', 'Name'));
					$this->database->executeGeneric('properduels.reset.kits', [], function(): void{
						foreach($this->kits as $kit){
							$this->add($kit);
						}
					});
					return;
				}
				/**
				 * @var string $name
				 * @var string $serializedArmor
				 * @var string $serializedInventory
				 */
				foreach($kits as ['Name' => $name, 'Armor' => $serializedArmor, 'Inventory' => $serializedInventory]){
					$armorContents = ContentsSerializer::deserializeItemContents($serializedArmor);
					$inventoryContents = ContentsSerializer::deserializeItemContents($serializedInventory);
					$this->kits[$name] = new Kit($name, $armorContents, $inventoryContents);
				}
			});
		});
		$this->database->waitAll();
	}

	public function add(Kit $kit): void{
		$name = $kit->getName();
		$this->kits[$name] = $kit;

		if(isset($this->database)){
			$this->database->executeInsert('properduels.register.kit', [
				'name' => $name,
				'armor' => ContentsSerializer::serializeItemContents($kit->getArmor()),
				'inventory' => ContentsSerializer::serializeItemContents($kit->getInventory()),
			]);
		}
	}

	public function all(): array{
		return $this->kits;
	}

	public function close(): void{
		$this->database->waitAll();
		$this->database->close();
	}

	public function get(string $kit): ?Kit{
		return $this->kits[$kit] ?? null;
	}

	public function has(string $kit): bool{
		return isset($this->kits[$kit]);
	}

	public function remove(string $kit): void{
		unset($this->kits[$kit]);

		if(isset($this->database)){
			$this->database->executeChange('properduels.delete.kit', [
				'name' => $kit
			]);
		}
	}

}