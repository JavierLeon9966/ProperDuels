<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\arena;

use poggit\libasynql\base\DataConnectorImpl;

final class ArenaManager{

	private $arenas = [];

	private $database;

	public function __construct(DataConnectorImpl $database){
		$this->database = $database;
		$this->database->executeGeneric('properduels.init.arenas', [], function(): void{
			$this->database->executeSelect('properduels.load.arenas', [], function(array $arenas): void{
				$this->arenas = array_map('unserialize', array_column($arenas, 'Arena', 'Name'));
			});
		});
		$this->database->waitAll();
	}

	public function add(Arena $arena): void{
		$name = $arena->getName();
		$this->arenas[$name] = $arena;

		$this->database->executeInsert('properduels.register.arena', [
			'name' => $name,
			'arena' => serialize($arena)
		]);
	}

	public function all(): array{
		return $this->arenas;
	}

	public function close(): void{
		$this->database->close();
		unset($this->database);
	}

	public function get(string $arena): ?Arena{
		return $this->arenas[$arena] ?? null;
	}

	public function has(string $arena): bool{
		return isset($this->arenas[$arena]);
	}

	public function remove(string $arena): void{
		unset($this->arenas[$arena]);

		$this->database->executeChange('properduels.delete.arena', [
			'name' => $arena
		]);
	}
}
