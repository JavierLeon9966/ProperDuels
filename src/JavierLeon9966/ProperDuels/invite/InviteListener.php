<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\invite;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

final readonly class InviteListener implements Listener{

	public function __construct(
		private Invite $invite
	){}

	/**
	 * @priority MONITOR
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		if($event->getPlayer() === $this->invite->getInviter()->getPlayer()){
			$this->invite->expire();
		}
	}
}
