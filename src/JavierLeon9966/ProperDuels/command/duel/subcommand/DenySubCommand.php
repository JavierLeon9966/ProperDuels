<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\session\SessionManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI\InfoAPI;

class DenySubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly Config $config,
		private readonly SessionManager $sessionManager,
		string $description = '',
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		/** @var array{'player': string} $args */
		$player = $sender->getServer()->getPlayerExact($args['player']);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$session = $this->sessionManager->get($sender->getUniqueId()->getBytes())
			?? throw new AssumptionFailedError('This should not be null at this point');

		if(!$session->hasInvite($playerUUID = $player->getUniqueId()->getBytes())){
			$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->playerNotFound, [], $sender));
			return;
		}

		$session->removeInvite($playerUUID);

		$sender->sendMessage(InfoAPI::render($this->plugin, $this->config->request->deny->success, ['player' => $player], $sender));
		$player->sendMessage(InfoAPI::render($this->plugin, $this->config->request->deny->message, ['player' => $sender], $player));
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.deny');

		try{
			$this->registerArgument(0, new RawStringArgument('player'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}