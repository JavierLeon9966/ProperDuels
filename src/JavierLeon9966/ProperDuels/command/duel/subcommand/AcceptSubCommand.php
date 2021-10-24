<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\game\Game;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AcceptSubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$player = $sender->getServer()->getPlayer($args['player']);
		if($player === null){
			$sender->sendTranslation(TextFormat::RED."%commands.generic.player.notFound");
			return;
		}

		$config = $this->plugin->getConfig();

		$sessionManager = $this->plugin->getSessionManager();
		$session = $sessionManager->get($senderUUID = $sender->getRawUniqueId());
		if($session === null){
			$sessionManager->add($sender);
			$session = $sessionManager->get($senderUUID);
		}

		if(!$session->hasInvite($playerUUID = $player->getRawUniqueId())){
			$sender->sendMessage($config->getNested('request.accept.playerNotFound'));
			return;
		}

		$arena = $session->getInvite($playerUUID);
		$gameManager = $this->plugin->getGameManager();
		if($gameManager->has($arena->getName())){
			$sender->sendMessage($config->getNested('match.inUse'));
			return;
		}

		if($session->getGame() !== null){
			$sender->sendMessage($config->getNested('request.invite.playerInDuel'));
			return;
		}

		$gameManager->add(new Game($arena, [$session, $sessionManager->get($playerUUID)]));

		$sender->sendMessage(str_replace('{player}', $player->getDisplayName(), $config->getNested('request.accept.success')));
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.accept');

		$this->registerArgument(0, new RawStringArgument('player'));
	}
}
