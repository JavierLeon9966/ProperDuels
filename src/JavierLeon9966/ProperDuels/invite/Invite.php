<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\invite;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\ProperDuels;
use JavierLeon9966\ProperDuels\session\Session;

use pocketmine\event\HandlerListManager;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

final class Invite{

	private ?TaskHandler $task = null;
	private InviteListener $listener;

	public function __construct(
		private Arena $arena,
		private Session $invited,
		private Session $inviter,
		int $time
	){
		$properDuels = ProperDuels::getInstance();
		if($time > 0){
			$this->task = $properDuels->getScheduler()->scheduleDelayedTask(new ClosureTask(fn() => $this->expire()), $time);
		}
		$this->listener = new InviteListener($this);
		$properDuels->getServer()->getPluginManager()->registerEvents($this->listener, $properDuels);
	}

	public function getArena(): Arena{
		return $this->arena;
	}

	public function getInviter(): Session{
		return $this->inviter;
	}

	public function expire(): void{
		$config = ProperDuels::getInstance()->getConfig();
		$this->inviter->getPlayer()->sendMessage(str_replace(
			'{player}',
			$this->invited->getPlayer()->getDisplayName(),
			$config->getNested('request.expire.to')
		));
		$this->invited->getPlayer()->sendMessage(str_replace(
			'{player}',
			$this->inviter->getPlayer()->getDisplayName(),
			$config->getNested('request.expire.from')
		));
		
		$this->invited->removeInvite($this->inviter->getPlayer()->getUniqueId()->getBytes());
	}

	public function close(): void{
		$this->task?->cancel();
		HandlerListManager::global()->unregisterAll($this->listener);
	}
}