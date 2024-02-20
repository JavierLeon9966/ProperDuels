<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};

final class SessionListener implements Listener{

	/**
	 * @priority MONITOR
	 */
	public function onPlayerJoin(PlayerJoinEvent $event): void{
		ProperDuels::getInstance()->getSessionManager()->add($event->getPlayer());
	}

	/**
	 * @priority MONITOR
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$rawUUID = $event->getPlayer()->getUniqueId()->getBytes();
		$sessionManager = ProperDuels::getInstance()->getSessionManager();
		$sessionManager->get($rawUUID)?->close();
		$sessionManager->remove($rawUUID);
	}
}