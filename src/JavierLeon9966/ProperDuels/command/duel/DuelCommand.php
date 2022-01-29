<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\duel\subcommand\{AcceptSubCommand, DenySubCommand, QueueSubCommand};
use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, TextFormat};

class DuelCommand extends BaseCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$config = $this->plugin->getConfig();
		
		$player = $sender->getServer()->getPlayerByPrefix($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}elseif($player === $sender){
			$sender->sendMessage($config->getNested('request.invite.sameTarget'));
			return;
		}
		
		if(!$this->plugin instanceof ProperDuels){
			throw new \UnexpectedValueException('This command wasn\'t created by ' . ProperDuels::class);
		}
		$sessionManager = $this->plugin->getSessionManager();
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$session = $sessionManager->get($playerUUID = $player->getUniqueId()->getBytes());
		if($session === null){
			$sessionManager->add($player);
			$session = $sessionManager->get($playerUUID);
		}
		
		if($session->hasInvite($rawUUID = $sender->getUniqueId()->getBytes())){
			$sender->sendMessage($config->getNested('request.invite.failure'));
			return;
		}
		
		$arenaManager = $this->plugin->getArenaManager();
		if(isset($args['arena']) and !$arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}
		
		if($session->getGame() !== null){
			$sender->sendMessage($this->plugin->getConfig()->getNested('request.invite.playerInDuel'));
			return;
		}
		
		$session->addInvite($sessionManager->get($rawUUID), isset($args['arena']) ? $arenaManager->get($args['arena']) : null);
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission(implode(';', [
			'properduels.command.duel.accept',
			'properduels.command.duel.deny',
			'properduels.command.duel.queue'
		]));

		$this->registerArgument(0, new RawStringArgument('player'));
		$this->registerArgument(1, new RawStringArgument('arena', true));

		$this->registerSubCommand(new AcceptSubCommand($this->plugin, 'accept'));
		$this->registerSubCommand(new DenySubCommand($this->plugin, 'deny'));
		$this->registerSubCommand(new QueueSubCommand($this->plugin, 'queue'));
	}
}