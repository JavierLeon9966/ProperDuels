<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\invite;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\session\Session;
use pocketmine\event\HandlerListManager;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginException;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\AssumptionFailedError;
use SOFe\InfoAPI\InfoAPI;

final class Invite{

	private ?TaskHandler $task = null;
	private InviteListener $listener;

	public function __construct(
		private readonly Config $config,
		private readonly Plugin $plugin,
		private readonly Arena   $arena,
		private readonly Session $invited,
		private readonly Session $inviter,
		PluginManager $pluginManager,
		int                      $time
	){
		if($time > 0){
			$this->task = $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask($this->expire(...)), $time);
		}
		$this->listener = new InviteListener($this);
		try{
			$pluginManager->registerEvents($this->listener, $plugin);
		}catch(PluginException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}

	public function getArena(): Arena{
		return $this->arena;
	}

	public function getInviter(): Session{
		return $this->inviter;
	}

	public function expire(): void{
		$inviterP = $this->inviter->getPlayer();
		$invitedP = $this->invited->getPlayer();
		$inviterP->sendMessage(InfoAPI::render($this->plugin, $this->config->request->expire->to, [
			'player' => $invitedP
		], $inviterP));
		$invitedP->sendMessage(InfoAPI::render($this->plugin, $this->config->request->expire->from, [
			'player' => $inviterP
		], $invitedP));
		
		$this->invited->removeInvite($inviterP->getUniqueId()->getBytes());
	}

	public function close(): void{
		$this->task?->cancel();
		HandlerListManager::global()->unregisterAll($this->listener);
	}
}
