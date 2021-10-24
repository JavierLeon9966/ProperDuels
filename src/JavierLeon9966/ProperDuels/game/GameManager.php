<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

use JavierLeon9966\ProperDuels\ProperDuels;
use JavierLeon9966\ProperDuels\session\Session;

final class GameManager{

	private $games = [];

	public function __construct(ProperDuels $plugin){
		$plugin->getServer()->getPluginManager()->registerEvents(new GameListener, $plugin);
	}

	public function close(){
		foreach($this->games as $game){
			$game->stop();
		}
	}

	public function add(Game $game): void{
		$arenaName = $game->getArena()->getName();
		if(isset($this->games[$arenaName])){
			return;
		}

		$this->games[$arenaName] = $game;
		$this->games[$arenaName]->start();
	}

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
