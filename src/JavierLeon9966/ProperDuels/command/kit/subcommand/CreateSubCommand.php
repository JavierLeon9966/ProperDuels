<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\kit\Kit;
use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, TextFormat};

class CreateSubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$this->plugin instanceof ProperDuels){
			throw new \InvalidStateException('This command wasn\'t created by ' . ProperDuels::class);
		}
		$kitManager = $this->plugin->getKitManager();
		if($kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."A kit with the name '$args[kit]' already exists");
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this')
		}
		$kitManager->add(new Kit(
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
