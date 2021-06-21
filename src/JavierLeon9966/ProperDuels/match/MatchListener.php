<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\match;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerCommandPreprocessEvent, PlayerDeathEvent, PlayerQuitEvent};
use pocketmine\utils\TextFormat;

final class MatchListener implements Listener{

	/**
	 * @priority HIGHEST
	 * @ignoreCancelled
	 */
	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event): void{
		$properDuels = ProperDuels::getInstance();
		if($event->getMessage()[0] === '/' and !$properDuels->getConfig()->getNested('match.allow-commands')){
			$player = $event->getPlayer();
			$session = $properDuels->getSessionManager()->get($player->getRawUniqueId());
			if($session !== null){
				$match = $session->getMatch();
				if($match !== null){
					$event->setCancelled();

					$player->sendMessage(TextFormat::RED.'You can\'t use commands while in a match!');
				}
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onPlayerDeath(PlayerDeathEvent $event): void{
		$session = ProperDuels::getInstance()->getSessionManager()->get($event->getPlayer()->getRawUniqueId());
		if($session !== null){
			$match = $session->getMatch();
			if($match !== null){
				$match->stop($session);

				$event->setKeepInventory(true);
				$event->setXpDropAmount(0);
			}
		}
	}

	/**
	 * @priority LOWEST
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$session = ProperDuels::getInstance()->getSessionManager()->get($event->getPlayer()->getRawUniqueId());
		if($session !== null){
			$match = $session->getMatch();
			if($match !== null){
				$match->stop($session);
			}
		}
	}
}
