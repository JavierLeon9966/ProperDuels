<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, Config, TextFormat};
use pocketmine\plugin\PluginBase;

class DenySubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly Config $config,
		private readonly SessionManager $sessionManager,
		string $description = "",
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$player = $sender->getServer()->getPlayerByPrefix($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}

		$config = $this->plugin->getConfig();

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$session = $this->sessionManager->get($senderUUID = $sender->getUniqueId()->getBytes());
		if($session === null){
			$this->sessionManager->add($sender);
			$session = $this->sessionManager->get($senderUUID);
		}

		if(!$session->hasInvite($playerUUID = $player->getUniqueId()->getBytes())){
			$sender->sendMessage($this->config->getNested('request.invite.playerNotFound'));
			return;
		}

		$session->removeInvite($playerUUID);

		$sender->sendMessage(str_replace('{player}', $player->getDisplayName(), $this->config->getNested('request.deny.success')));
		$player->sendMessage(str_replace('{player}', $sender->getDisplayName(), $this->config->getNested('request.deny.message')));
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.deny');

		$this->registerArgument(0, new RawStringArgument('player'));
	}
}