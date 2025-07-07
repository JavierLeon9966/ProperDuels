<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\arena;

use pocketmine\math\Vector3;

final readonly class Arena{

	private Vector3 $firstSpawnPos;
	private Vector3 $secondSpawnPos;

	public function __construct(private string $name, private string $levelName, Vector3 $firstSpawnPos, Vector3 $secondSpawnPos, private ?string $kit = null){
		$this->firstSpawnPos = clone $firstSpawnPos;
		$this->secondSpawnPos = clone $secondSpawnPos;
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
