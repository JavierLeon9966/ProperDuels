<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\kit\Kit;
use JavierLeon9966\ProperDuels\kit\KitManager;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use pocketmine\plugin\PluginBase;

class CreateSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if($this->kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."A kit with the name '$args[kit]' already exists");
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$this->kitManager->add(new Kit(
			$args['kit'],
			$sender->getArmorInventory()->getContents(),
			$sender->getInventory()->getContents()
		));
		$sender->sendMessage("Added new kit '$args[kit]' successfully");
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.kit.create');

		$this->registerArgument(0, new RawStringArgument('kit'));
	}
}