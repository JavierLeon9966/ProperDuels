<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};

final readonly class SessionListener implements Listener{

	public function __construct(private SessionManager $sessionManager){
	}

	/**
	 * @priority MONITOR
	 */
	public function onPlayerJoin(PlayerJoinEvent $event): void{
		$this->sessionManager->add($event->getPlayer());
	}

	/**
	 * @priority MONITOR
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$rawUUID = $event->getPlayer()->getUniqueId()->getBytes();
		$session = $this->sessionManager->get($rawUUID);
		$session?->close();
		$this->sessionManager->remove($rawUUID);
	}
}
