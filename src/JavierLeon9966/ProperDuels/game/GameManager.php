<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

final class GameManager{

	/** @var array<string, Game> */
	private array $games = [];

	/** @throws \RuntimeException */
	public function close(): void{
		foreach($this->games as $game){
			$game->stop();
		}
	}

	/** @throws \RuntimeException */
	public function add(Game $game): void{
		$arenaName = $game->getArena()->getName();
		if(isset($this->games[$arenaName])){
			return;
		}

		$this->games[$arenaName] = $game;
		$this->games[$arenaName]->start();
	}

	/** @return array<string, Game> */
	public function all(): array{
		return $this->games;
	}

	public function remove(string $arenaName): void{
		unset($this->games[$arenaName]);
	}

	public function has(string $arenaName): bool{
		return isset($this->games[$arenaName]);
	}
}