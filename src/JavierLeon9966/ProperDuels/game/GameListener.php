<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerDeathEvent, PlayerQuitEvent};
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class GameListener implements Listener{

	/**
	 * @priority HIGHEST
	 */
	public function onCommandEvent(CommandEvent $event): void{
		$properDuels = ProperDuels::getInstance();
		$player = $event->getSender();
		if($player instanceof Player && !$properDuels->getConfig()->getNested('match.allow-commands')){
			$session = $properDuels->getSessionManager()->get($player->getUniqueId()->getBytes());
			if($session !== null){
				$game = $session->getGame();
				if($game !== null){
					$event->cancel();

					$player->sendMessage(TextFormat::RED.'You can\'t use commands while in a match!');
				}
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onPlayerDeath(PlayerDeathEvent $event): void{
		$session = ProperDuels::getInstance()->getSessionManager()->get($event->getPlayer()->getUniqueId()->getBytes());
		if($session !== null){
			$game = $session->getGame();
			if($game !== null){
				$game->stop($session);

				$event->setKeepInventory(true);
				$event->setXpDropAmount(0);
			}
		}
	}

	/**
	 * @priority LOWEST
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$session = ProperDuels::getInstance()->getSessionManager()->get($event->getPlayer()->getUniqueId()->getBytes());
		if($session !== null){
			$game = $session->getGame();
			if($game !== null){
				$game->stop($session);
			}
		}
	}
}
