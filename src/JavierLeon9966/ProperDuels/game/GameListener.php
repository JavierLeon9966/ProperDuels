<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\game;

use InvalidArgumentException;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerDeathEvent, PlayerQuitEvent};
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

final readonly class GameListener implements Listener{

	public function __construct(private Config $config, private SessionManager $sessionManager){
	}

	/**
	 * @priority HIGHEST
	 */
	public function onCommandEvent(CommandEvent $event): void{
		$player = $event->getSender();
		if($player instanceof Player && !$this->config->match->allowCommands){
			$session = $this->sessionManager->get($player->getUniqueId()->getBytes());
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
	 *
	 * @throws \RuntimeException
	 */
	public function onPlayerDeath(PlayerDeathEvent $event): void{
		$session = $this->sessionManager->get($event->getPlayer()->getUniqueId()->getBytes());
		if($session !== null){
			$game = $session->getGame();
			if($game !== null){
				$game->stop($session);

				$event->setKeepInventory(true);
				try{
					$event->setXpDropAmount(0);
				}catch(InvalidArgumentException $e){
					throw new AssumptionFailedError('This should never happen', 0, $e);
				}
			}
		}
	}

	/**
	 * @priority LOWEST
	 *
	 * @throws \RuntimeException
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$session = $this->sessionManager->get($event->getPlayer()->getUniqueId()->getBytes());
		if($session !== null){
			$game = $session->getGame();
			$game?->stop($session);
		}
	}
}
