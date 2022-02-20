<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use poggit\libasynql\base\DataConnectorImpl;

final class KitManager{

	private $kits = [];

	private $database;

	public function __construct(DataConnectorImpl $database){
		$this->database = $database;
		$this->database->executeGeneric('properduels.init.kits', [], function(): void{
			$this->database->executeSelect('properduels.load.kits', [], function(array $kits): void{
				$this->kits = array_map('unserialize', array_column($kits, 'Kit', 'Name'));
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
				'kit' => serialize($kit)
			]);
		}
	}

	public function all(): array{
		return $this->kits;
	}

	public function close(): void{
		$this->database->close();
		unset($this->database);
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
