<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\Game;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\QueueManager;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\InfoAPI;

class AcceptSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly Config $config,
		private readonly SessionManager $sessionManager,
		private readonly GameManager $gameManager,
		private readonly KitManager $kitManager,
		private readonly QueueManager $queueManager,
		string $description = '',
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/**
	 * @param array<array-key, mixed> $args
	 *
	 * @throws \RuntimeException
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		/** @var array{player: string} $args */
		$server = $sender->getServer();
		$player = $server->getPlayerExact($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$session = $this->sessionManager->get($sender->getUniqueId()->getBytes())
			?? throw new AssumptionFailedError('This should not be null at this point');

		$arena = $session->getInvite($playerUUID = $player->getUniqueId()->getBytes());
		if($arena === null){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->playerNotFound, [], $sender));
			return;
		}

		if($this->gameManager->has($arena->getName())){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->match->inUse, [], $sender));
			return;
		}

		if($session->getGame() !== null){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->playerInDuel, [], $sender));
			return;
		}

		$this->gameManager->add(new Game($this->config, $this->gameManager, $this->kitManager, $server->getWorldManager(), $this->queueManager, $this->plugin, $arena, [
			$session,
			$this->sessionManager->get($playerUUID) ??
				throw new AssumptionFailedError('This should not be null at this point')
		]));

		$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->accept->success, ['player' => $player], $sender));
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.accept');

		try{
			$this->registerArgument(0, new RawStringArgument('player'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}