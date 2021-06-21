<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\duel\subcommand\{AcceptSubCommand, DenySubCommand, QueueSubCommand};

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DuelCommand extends BaseCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$config = $this->plugin->getConfig();
		
		$player = $sender->getServer()->getPlayer($args['player']);
		if($player === null){
			$sender->sendTranslation(TextFormat::RED."%commands.generic.player.notFound");
			return;
		}elseif($player === $sender){
			$sender->sendMessage($config->getNested('request.invite.sameTarget'));
			return;
		}
		
		$sessionManager = $this->plugin->getSessionManager();
		$session = $sessionManager->get($playerUUID = $player->getRawUniqueId());
		if($session === null){
			$sessionManager->add($player);
			$session = $sessionManager->get($playerUUID);
		}
		
		if($session->hasInvite($rawUUID = $sender->getRawUniqueId())){
			$sender->sendMessage($config->getNested('request.invite.failure'));
			return;
		}
		
		$arenaManager = $this->plugin->getArenaManager();
		if(isset($args['arena']) and !$arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}
		
		if($session->getMatch() !== null){
			$sender->sendMessage($this->plugin->getConfig()->getNested('request.invite.playerInDuel'));
			return;
		}
		
		$session->addInvite($sessionManager->get($rawUUID), isset($args['arena']) ? $arenaManager->get($args['arena']) : null);
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel');

		$this->registerArgument(0, new RawStringArgument('player'));
		$this->registerArgument(1, new RawStringArgument('arena', true));

		$this->registerSubCommand(new AcceptSubCommand($this->plugin, 'accept'));
		$this->registerSubCommand(new DenySubCommand($this->plugin, 'deny'));
		$this->registerSubCommand(new QueueSubCommand($this->plugin, 'queue'));
	}
}