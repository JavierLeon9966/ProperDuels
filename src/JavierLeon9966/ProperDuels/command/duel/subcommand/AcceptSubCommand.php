<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\game\Game;
use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, TextFormat};

class AcceptSubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$player = $sender->getServer()->getPlayerByPrefix($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}

		$config = $this->plugin->getConfig();

		if(!$this->plugin instanceof ProperDuels){
			throw new \InvalidStateException('This command wasn\'t created by ' . ProperDuels::class);
		}
		$sessionManager = $this->plugin->getSessionManager();
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this')
		}
		$session = $sessionManager->get($senderUUID = $sender->getUniqueId()->getBytes());
		if($session === null){
			$sessionManager->add($sender);
			$session = $sessionManager->get($senderUUID);
		}

		if(!$session->hasInvite($playerUUID = $player->getUniqueId()->getBytes())){
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
