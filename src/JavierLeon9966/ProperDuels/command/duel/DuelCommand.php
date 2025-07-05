<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\command\duel\subcommand\{AcceptSubCommand, DenySubCommand, QueueSubCommand};
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\QueueManager;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use SOFe\InfoAPI\InfoAPI;

class DuelCommand extends BaseCommand{

	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly Config $config,
		private readonly SessionManager $sessionManager,
		private readonly GameManager $gameManager,
		private readonly ArenaManager $arenaManager,
		private readonly QueueManager $queueManager,
		private readonly KitManager $kitManager,
		string $description = '',
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		/** @var array{'player': string, 'arena'?: string} $args */

		$player = $sender->getServer()->getPlayerExact($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}elseif($player === $sender){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->sameTarget, [], $sender));
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$session = $this->sessionManager->get($player->getUniqueId()->getBytes())
			?? throw new AssumptionFailedError('This should not be null at this point');
		
		if($session->hasInvite($rawUUID = $sender->getUniqueId()->getBytes())){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->failure, [], $sender));
			return;
		}
		if(isset($args['arena']) and !$this->arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}
		
		if($session->getGame() !== null){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->playerInDuel, [], $sender));
			return;
		}
		
		$session->addInvite(
		$this->sessionManager->get($rawUUID)
				?? throw new AssumptionFailedError('This should not be null at this point'),
			isset($args['arena']) ? $this->arenaManager->get($args['arena']) : null
		);
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'properduels.command.duel.accept',
			'properduels.command.duel.deny',
			'properduels.command.duel.queue'
		]);

		try{
			$this->registerArgument(0, new RawStringArgument('player'));
			$this->registerArgument(1, new RawStringArgument('arena', true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof PluginBase);
		$this->registerSubCommand(new AcceptSubCommand($plugin, 'accept', $this->config, $this->sessionManager, $this->gameManager, $this->kitManager, $this->queueManager));
		$this->registerSubCommand(new DenySubCommand($plugin, 'deny', $this->config, $this->sessionManager));
		$this->registerSubCommand(new QueueSubCommand($plugin, 'queue', $this->arenaManager, $this->queueManager));
	}
}