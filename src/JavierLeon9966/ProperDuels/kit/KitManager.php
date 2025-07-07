<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\kit;

use Generator;
use JavierLeon9966\ProperDuels\config\DatabaseType;
use JavierLeon9966\ProperDuels\RawQueries;
use JavierLeon9966\ProperDuels\utils\ContentsSerializer;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\NbtDataException;
use pocketmine\utils\AssumptionFailedError;
use poggit\libasynql\SqlError;
use RuntimeException;
use SOFe\AwaitGenerator\Await;

final readonly class KitManager{

	private function __construct(private RawQueries $queries){
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, KitManager> */
	public static function create(RawQueries $mergedDb, DatabaseType $type): Generator{
		$gen = $mergedDb->initKits();
		if($type === DatabaseType::Mysql){
			yield from $gen;
		}else{
			Await::g2c($gen);
		}
		return new self($mergedDb);
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

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, list<Kit>> */
	public function getList(int $offset, int $limit): Generator{
		/** @var list<array{'Name': string, 'Armor': string, 'Inventory': string}> $rows */
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
					ContentsSerializer::deserializeItemContents($data['Inventory'])
				);
			}catch(NbtDataException|SavedDataLoadingException|ItemTypeDeserializeException $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		return $kits;
	}

}
