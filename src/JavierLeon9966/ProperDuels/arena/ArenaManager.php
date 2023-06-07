<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\arena;

use pocketmine\math\Vector3;
use poggit\libasynql\base\DataConnectorImpl;

final class ArenaManager{

	private $arenas = [];

	private $database;

	public function __construct(DataConnectorImpl $database){
		$this->database = $database;
		$this->database->executeGeneric('properduels.init.arenas', [], function(): void{
			$this->database->executeSelect('properduels.load.arenas', [], function(array $arenas): void{
				if(count($arenas) === 0){
					return;
				}
				if(isset($arenas[0]['Arena'])){
					$this->arenas = array_map('unserialize', array_column($arenas, 'Arena', 'Name'));
					$this->database->executeGeneric('properduels.reset.arenas', [], function(): void{
						foreach($this->arenas as $arena){
							$this->add($arena);
						}
					});
					return;
				}

				/**
				 * @var string $name
				 * @var string $levelName
				 * @var float $firstSpawnPosX
				 * @var float $firstSpawnPosY
				 * @var float $firstSpawnPosZ
				 * @var float $secondSpawnPosX
				 * @var float $secondSpawnPosY
				 * @var float $secondSpawnPosZ
				 * @var ?string $kit
				 */
				foreach($arenas as [
					'Name' => $name,
					'LevelName' => $levelName,
					'FirstSpawnPosX' => $firstSpawnPosX,
					'FirstSpawnPosY' => $firstSpawnPosY,
					'FirstSpawnPosZ' => $firstSpawnPosZ,
					'SecondSpawnPosX' => $secondSpawnPosX,
					'SecondSpawnPosY' => $secondSpawnPosY,
					'SecondSpawnPosZ' => $secondSpawnPosZ,
					'Kit' => $kit
				]){
					$this->arenas[$name] = new Arena(
						$name,
						$levelName,
						new Vector3($firstSpawnPosX, $firstSpawnPosY, $firstSpawnPosZ),
						new Vector3($secondSpawnPosX, $secondSpawnPosY, $secondSpawnPosZ),
						$kit
					);
				}
			});
		});
		$this->database->waitAll();
	}

	public function add(Arena $arena): void{
		$name = $arena->getName();
		$this->arenas[$name] = $arena;

		$firstSpawnPos = $arena->getFirstSpawnPos();
		$secondSpawnPos = $arena->getSecondSpawnPos();
		$this->database->executeInsert('properduels.register.arena', [
			'name' => $name,
			'levelName' => $arena->getLevelName(),
			'firstSpawnPosX' => $firstSpawnPos->x
			'firstSpawnPosY' => $firstSpawnPos->y,
			'firstSpawnPosZ' => $firstSpawnPos->z,
			'secondSpawnPosX' => $secondSpawnPos->x,
			'secondSpawnPosY' => $secondSpawnPos->y,
			'secondSpawnPosZ' => $secondSpawnPos->z,
			'kit' => $arena->getKit()
		]);
	}

	public function all(): array{
		return $this->arenas;
	}

	public function close(): void{
		$this->database->waitAll();
		$this->database->close();
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
