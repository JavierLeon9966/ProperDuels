<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\event;

use JavierLeon9966\ProperDuels\game\Game;

use pocketmine\player\Player;

class GameStartEvent extends GameEvent{
	public function __construct(
		Game $game,
		private Player $firstOpponent,
		private Player $secondOpponent
	){
		parent::__construct($game);
	}

	public function getFirstOpponent(): Player{
		return $this->firstOpponent;
	}

	public function getSecondOpponent(): Player{
		return $this->secondOpponent;
	}
}