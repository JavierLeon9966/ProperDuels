<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use Closure;
use Generator;
use JavierLeon9966\ProperDuels\config\DatabaseType;
use JavierLeon9966\ProperDuels\RawQueries;
use JavierLeon9966\ProperDuels\utils\ContentsSerializer;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\NbtDataException;
use pocketmine\utils\AssumptionFailedError;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\poggit\libasynql\SqlError;
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Await;

final readonly class KitManager{

	private function __construct(private RawQueries $queries){
	}

	/**
	 * @param null|Closure(KitManager): Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void> $extraSetup
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, KitManager>
	 */
	public static function create(RawQueries $queries, DatabaseType $type, ?Closure $extraSetup = null): Generator{
		$kitManager = new self($queries);
		if($type === DatabaseType::Sqlite3){
			yield from $kitManager->sqlite3Init();
		}else{
			yield from $kitManager->mysqlInit();
		}
		if($extraSetup !== null){
			yield from $extraSetup($kitManager);
		}
		return $kitManager;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void> */
	private function sqlite3Init(): Generator{
		/** @var array{array{'migrationNeeded': int<0, 1>}} $rows */
		[$rows,] = yield from Await::all([$this->queries->checkForMigrationKits(), (function(): Generator{
			yield from $this->queries->initForeignKeys();
			yield from $this->queries->initKits();
		})()]);
		if($rows[0]['migrationNeeded'] === 1){
			yield from $this->queries->migrateKits();
		}
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void> */
	private function mysqlInit(): Generator{
		try{
			yield from $this->queries->initKits();
		}catch(SqlError $e){
			if(preg_match('/^procedure [^ ]+ already exists$/i', $e->getErrorMessage()) === 1){
				// ignore
			}else{
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}

		try{
			/**
			 * @var array{array{'migrationNeeded': int<0, 1>}} $migrationRows
			 * @var list<array{Name: string, Kit: string}> $rows
			 */
			[$migrationRows, $rows] = yield from Await::all([$this->queries->checkForMigrationKits(), $this->queries->loadOldKits()]);
			if($migrationRows[0]['migrationNeeded'] === 0){
				return;
			}
		}catch(SqlError $e){
			if(str_contains(strtolower($e->getMessage()), 'unknown column')){
				return;
			}else{
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		yield from $this->migrateOldRows($rows);
	}

	/**
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 * @throws \RuntimeException
	 */
	public function add(Kit $kit): Generator{
		$name = $kit->getName();

		try{
			yield from $this->queries->registerKit(
				$name,
				ContentsSerializer::serializeItemContents($kit->getArmor()),
				ContentsSerializer::serializeItemContents($kit->getInventory()),
			);
		}catch(SqlError $e){
			throw new RuntimeException('Failed to register kit: ' . $e->getMessage(), 0, $e);
		}
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ?Kit> */
	public function get(string $kit): Generator{
		/** @var array{0?: array{'Name': string, 'Armor': string, 'Inventory': string}} $rows */
		$rows = yield from $this->queries->getKit($kit);

		if(!isset($rows[0])){
			return null;
		}

		$data = $rows[0];
		try{
			return new Kit(
				$data['Name'],
				ContentsSerializer::deserializeItemContents($data['Armor']),
				ContentsSerializer::deserializeItemContents($data['Inventory'])
			);
		}catch(NbtDataException|SavedDataLoadingException|ItemTypeDeserializeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ?Kit> */
	public function getRandom(): Generator{
		/** @var array{0?: array{'Name': string, 'Armor': string, 'Inventory': string}} $rows */
		$rows = yield from $this->queries->getRandomKit();

		if(!isset($rows[0])){
			return null;
		}

		$data = $rows[0];
		try{
			return new Kit(
				$data['Name'],
				ContentsSerializer::deserializeItemContents($data['Armor']),
				ContentsSerializer::deserializeItemContents($data['Inventory'])
			);
		}catch(NbtDataException|SavedDataLoadingException|ItemTypeDeserializeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, bool> */
	public function remove(string $kit): Generator{
		$changedRows = yield from $this->queries->deleteKit($kit);
		return $changedRows > 0;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, \JavierLeon9966\ProperDuels\kit\KitUpdateStatus> */
	public function update(string $kitName, Kit $kit): Generator{
		/**
		 * @var ?Kit $oldKit
		 * @var int<0, 1> $changedRows
		 */
		[$oldKit, $changedRows] = yield from Await::all([$this->get($kitName), $this->queries->updateKit(
			$kitName,
			ContentsSerializer::serializeItemContents($kit->getArmor()),
			ContentsSerializer::serializeItemContents($kit->getInventory()),
			$kit->getName()
		)]);
		if($oldKit === null){
			return KitUpdateStatus::NOT_FOUND;
		}
		if($changedRows === 0){
			return KitUpdateStatus::NO_CHANGES;
		}
		return KitUpdateStatus::SUCCESS;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, \JavierLeon9966\ProperDuels\kit\KitUpdateStatus> */
	public function setEnabled(string $kitName, bool $enabled): Generator{
		/**
		 * @var ?Kit $oldKit
		 * @var int<0, 1> $changedRows
		 */
		[$oldKit, $changedRows] = yield from Await::all([$this->get($kitName), $this->queries->setEnabledKit($kitName, $enabled)]);
		if($oldKit === null){
			return KitUpdateStatus::NOT_FOUND;
		}
		if($changedRows === 0){
			return KitUpdateStatus::NO_CHANGES;
		}
		return KitUpdateStatus::SUCCESS;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, list<Kit>> */
	public function getList(int $offset, int $limit): Generator{
		/** @var list<array{'Name': string, 'Armor': string, 'Inventory': string, 'Enabled': bool|int<0, 1>}> $rows */
		$rows = yield from $this->queries->listKits($offset, $limit);

		if(count($rows) === 0){
			return [];
		}

		$kits = [];
		foreach($rows as $data){
			try{
				$kits[] = new Kit(
					$data['Name'],
					ContentsSerializer::deserializeItemContents($data['Armor']),
					ContentsSerializer::deserializeItemContents($data['Inventory']),
					(bool)($data['Enabled'])
				);
			}catch(NbtDataException|SavedDataLoadingException|ItemTypeDeserializeException $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		return $kits;
	}

	/**
	 * @param list<array{Name: string, Kit: string}|array{Name: string, Armor: string, Inventory: string}> $rows
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	public function migrateOldRows(array $rows): Generator{
		/** @var array<string, Kit> $rows */
		$rows = array_map(unserialize(...), array_column($rows, 'Kit', 'Name'));
		try{
			$gens = array_map($this->add(...), $rows);
		}catch(RuntimeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
		yield from Await::all($gens);
	}
}