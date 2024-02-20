<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\arena;

use pocketmine\math\Vector3;

final class Arena{

	private $name;

	private $levelName;

	private $firstSpawnPos;
	private $secondSpawnPos;

	private $kit = null;

	public function __construct(string $name, string $levelName, Vector3 $firstSpawnPos, Vector3 $secondSpawnPos, ?string $kit = null){
		$this->firstSpawnPos = clone $firstSpawnPos;
		$this->secondSpawnPos = clone $secondSpawnPos;

		$this->name = $name;
		$this->levelName = $levelName;
		$this->kit = $kit;
	}

	public function getFirstSpawnPos(): Vector3{
		return clone $this->firstSpawnPos;
	}

	public function getSecondSpawnPos(): Vector3{
		return clone $this->secondSpawnPos;
	}

	public function getKit(): ?string{
		return $this->kit;
	}

	public function getLevelName(): string{
		return $this->levelName;
	}

	public function getName(): string{
		return $this->name;
	}
}