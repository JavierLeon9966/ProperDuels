<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\event;

use JavierLeon9966\ProperDuels\game\Game;

use pocketmine\player\Player;

class GameFinishEvent extends GameEvent{
	public function __construct(
		Game $game,
		private Player $winner,
		private Player $loser
	){
		parent::__construct($game);
	}

	public function getWinner(): Player{
		return $this->winner;
	}

	public function getLoser(): Player{
		return $this->loser;
	}
}