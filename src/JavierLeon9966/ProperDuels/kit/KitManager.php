<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use JavierLeon9966\ProperDuels\utils\ContentsSerializer;
use pocketmine\utils\AssumptionFailedError;
use poggit\libasynql\DataConnector;

final class KitManager{

	/** @var array<string, Kit> */
	private array $kits = [];

	public function __construct(private readonly DataConnector $database){
		$this->database->executeGeneric('properduels.init.kits', [], function(): void{
			$this->database->executeSelect('properduels.load.kits', [], function(array $kits): void{
				/** @var list<array{Name: string, Kit: string}|array{Name: string, Armor: string, Inventory: string}> $kits */
				if(count($kits) === 0){
					return;
				}
				if(isset($kits[0]['Kit'])){
					$this->kits = array_map(static function(string $serialized): Kit{
						$deserialized = unserialize($serialized);
						if(!$deserialized instanceof Kit){
							throw new AssumptionFailedError('This should never happen');
						}
						return $deserialized;
					}, array_column($kits, 'Kit', 'Name'));
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

	/** @return array<string, Kit> */
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
