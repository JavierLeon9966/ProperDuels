<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\match;

use JavierLeon9966\ProperDuels\ProperDuels;
use JavierLeon9966\ProperDuels\session\Session;

final class MatchManager{

	private $matches = [];

	public function __construct(ProperDuels $plugin){
		$plugin->getServer()->getPluginManager()->registerEvents(new MatchListener, $plugin);
	}

	public function close(){
		foreach($this->matches as $match){
			$match->stop();
		}
	}

	public function add(Match $match): void{
		$arenaName = $match->getArena()->getName();
		if(isset($this->matches[$arenaName])){
			return;
		}

		$this->matches[$arenaName] = $match;
		$this->matches[$arenaName]->start();
	}

	public function all(): array{
		return $this->matches;
	}

	public function remove(string $arenaName): void{
		unset($this->matches[$arenaName]);
	}

	public function has(string $arenaName): bool{
		return isset($this->matches[$arenaName]);
	}
}
