<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\utils\TextFormat;

class DenySubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$player = $sender->getServer()->getPlayerByPrefix($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}

		$config = $this->plugin->getConfig();

		$sessionManager = $this->plugin->getSessionManager();
		$session = $sessionManager->get($senderUUID = $sender->getUniqueId()->getBytes());
		if($session === null){
			$sessionManager->add($sender);
			$session = $sessionManager->get($senderUUID);
		}

		if(!$session->hasInvite($playerUUID = $player->getUniqueId()->getBytes())){
			$sender->sendMessage($config->getNested('request.invite.playerNotFound'));
			return;
		}

		$session->removeInvite($playerUUID);

		$sender->sendMessage(str_replace('{player}', $player->getDisplayName(), $config->getNested('request.deny.success')));
		$player->sendMessage(str_replace('{player}', $sender->getDisplayName(), $config->getNested('request.deny.message')));
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.deny');

		$this->registerArgument(0, new RawStringArgument('player'));
	}
}
