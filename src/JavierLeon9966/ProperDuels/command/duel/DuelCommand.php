<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\duel\subcommand\{AcceptSubCommand, DenySubCommand, QueueSubCommand};
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\ProperDuels;

use JavierLeon9966\ProperDuels\QueueManager;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, Config, TextFormat};
use pocketmine\plugin\PluginBase;

class DuelCommand extends BaseCommand{

	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly Config $config,
		private readonly SessionManager $sessionManager,
		private readonly GameManager $gameManager,
		private readonly ArenaManager $arenaManager,
		private readonly QueueManager $queueManager,
		string $description = "",
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof ProperDuels);
		$config = $plugin->getConfig();
		
		$player = $sender->getServer()->getPlayerByPrefix($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}elseif($player === $sender){
			$sender->sendMessage($config->getNested('request.invite.sameTarget'));
			return;
		}

		$sessionManager = $plugin->getSessionManager();
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
		
		$arenaManager = $plugin->getArenaManager();
		if(isset($args['arena']) and !$arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}
		
		if($session->getGame() !== null){
			$sender->sendMessage($plugin->getConfig()->getNested('request.invite.playerInDuel'));
			return;
		}
		
		$session->addInvite($sessionManager->get($rawUUID), isset($args['arena']) ? $arenaManager->get($args['arena']) : null);
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'properduels.command.duel.accept',
			'properduels.command.duel.deny',
			'properduels.command.duel.queue'
		]);

		$this->registerArgument(0, new RawStringArgument('player'));
		$this->registerArgument(1, new RawStringArgument('arena', true));

		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof ProperDuels);
		$this->registerSubCommand(new AcceptSubCommand($plugin, 'accept', $this->config, $this->sessionManager, $this->gameManager));
		$this->registerSubCommand(new DenySubCommand($plugin, 'deny', $this->config, $this->sessionManager));
		$this->registerSubCommand(new QueueSubCommand($plugin, 'queue', $this->arenaManager, $this->queueManager));
	}
}