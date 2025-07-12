<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\arena;

use Closure;
use Generator;
use JavierLeon9966\ProperDuels\config\DatabaseType;
use JavierLeon9966\ProperDuels\RawQueries;
use pocketmine\math\Vector3;
use pocketmine\utils\AssumptionFailedError;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\poggit\libasynql\SqlError;
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\AwaitGenerator\Await;

final readonly class ArenaManager{

	private function __construct(private RawQueries $queries){
	}

	/**
	 * @param null|\Closure(ArenaManager): Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void> $extraSetup
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ArenaManager>
	 */
	public static function create(RawQueries $queries, DatabaseType $databaseType, ?Closure $extraSetup = null): Generator{
		$arenaManager = new self($queries);
		$gen = $queries->initArenas();
		if($databaseType === DatabaseType::Sqlite3){
			/** @var array{array{'migrationNeeded': int<0, 1>}} $rows */
			[$rows, ] = yield from Await::all([$queries->checkForMigrationArenas(), $gen]);
			if($rows[0]['migrationNeeded'] === 1){
				yield from $queries->migrateArenas();
			}
			return $arenaManager;
		}
		try{
			yield from $gen;
		}catch(SqlError $e){
			if(preg_match('/^procedure [^ ]+ already exists$/i', $e->getErrorMessage()) === 1){
				// ignore
			}else{
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}

		try{
			/** @var list<array{Name: string, Arena: string}> $rows */
			$rows = yield from $queries->loadOldArenas();
		}catch(SqlError $e){
			if(str_contains(strtolower($e->getMessage()), 'unknown column')){
				return $arenaManager;
			}else{
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		/** @var int<0, 1> $migrationNeeded */
		[['migrationNeeded' => $migrationNeeded]] = yield from $queries->checkForMigrationArenas();
		if($migrationNeeded === 0){
			return $arenaManager;
		}
		yield from $arenaManager->migrateOldRows($rows);
		if($extraSetup !== null){
			yield from $extraSetup($arenaManager);
		}
		return $arenaManager;
	}

	/**
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, \JavierLeon9966\ProperDuels\arena\ArenaCreationStatus>
	 */
	public function add(Arena $arena): Generator{
		$name = $arena->getName();

		$firstSpawnPos = $arena->getFirstSpawnPos();
		$secondSpawnPos = $arena->getSecondSpawnPos();
		try{
			yield from $this->queries->registerArena(
				$name,
				$arena->getLevelName(),
				$firstSpawnPos->x,
				$firstSpawnPos->y,
				$firstSpawnPos->z,
				$secondSpawnPos->x,
				$secondSpawnPos->y,
				$secondSpawnPos->z,
				$arena->getKit()
			);
			return ArenaCreationStatus::Success;
		}catch(SqlError $e){
			// lowercase for easier searching
			$msg = strtolower($e->getMessage());

			// 1) Duplicate‐key / unique constraint
			//   MySQL:   "Duplicate entry 'foo' for key 'PRIMARY'"
			//   SQLite:  "unique constraint failed: users.email"
			//            "primary key must be unique"
			if (
				str_contains($msg, 'duplicate entry')
				|| str_contains($msg, 'unique constraint failed')
				|| (str_contains($msg, 'constraint failed') && str_contains($msg, 'unique'))
				|| (str_contains($msg, 'must be unique') && str_contains($msg, 'primary key'))
			) {
				return ArenaCreationStatus::AlreadyExists;
			}

			// 2) Foreign‐key violation
			//   MySQL:   "Cannot add or update a child row: a foreign key constraint fails..."
			//   SQLite:  "foreign key constraint failed"
			if (
				str_contains($msg, 'foreign key constraint failed')
				|| str_contains($msg, 'foreign key constraint fails')
				|| str_contains($msg, 'foreign key failed')
			) {
				return ArenaCreationStatus::InvalidKit;
			}

			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ?Arena> */
	public function get(string $arena): Generator{
		/** @var array{0?: array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}} $rows */
		$rows = yield from $this->queries->getArena($arena);
		if(count($rows) === 0){
			return null;
		}
		return $this->createFromRow($rows[0]);
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ?Arena> */
	public function getRandom(): Generator{
		/** @var array{0?: array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}} $rows */
		$rows = yield from $this->queries->getRandomArena();
		if(count($rows) === 0){
			return null;
		}
		return $this->createFromRow($rows[0]);
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, bool> */
	public function remove(string $arena): Generator{
		$rowsChanged = yield from $this->queries->deleteArena($arena);
		return $rowsChanged > 0;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, list<Arena>> */
	public function getList(int $offset, int $limit): Generator{
		/** @var list<array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}> $rows */
		$rows = yield from $this->queries->listArenas($offset, $limit);
		$arenas = [];
		foreach($rows as $row){
			$arenas[] = $this->createFromRow($row);
		}
		return $arenas;
	}

	/**
	 * @param array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string} $row
	 */
	private function createFromRow(array $row): Arena{
		return new Arena(
			$row['Name'],
			$row['LevelName'],
			new Vector3($row['FirstSpawnPosX'], $row['FirstSpawnPosY'], $row['FirstSpawnPosZ']),
			new Vector3($row['SecondSpawnPosX'], $row['SecondSpawnPosY'], $row['SecondSpawnPosZ']),
			$row['Kit']
		);
	}

	/**
	 * @param list<array{Name: string, Arena: string}> $rows
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	public function migrateOldRows(array $rows): Generator{
		/** @var array<string, Arena> $unserializedArenas */
		$unserializedArenas = array_map(unserialize(...), array_column($rows, 'Arena', 'Name'));
		try{
			$gens = array_map($this->add(...), $unserializedArenas);
		}catch(RuntimeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
		yield from Await::all($gens);
	}
}