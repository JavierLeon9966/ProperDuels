<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\kit\Kit;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class CreateSubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$kitManager = $this->plugin->getKitManager();
		if($kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."A kit with the name '$args[kit]' already exists");
			return;
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
