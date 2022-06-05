<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\event;

use JavierLeon9966\ProperDuels\game\Game;

use pocketmine\event\Event;

abstract class GameEvent extends Event{
	public function __construct(private Game $game){}

	public function getGame(): Game{
		return $this->game:
	}
}
