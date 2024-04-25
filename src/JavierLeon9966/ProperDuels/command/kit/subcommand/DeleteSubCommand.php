<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_488821ee8c1f9ac5\CortexPE\Commando\BaseSubCommand;

use JavierLeon9966\ProperDuels\kit\KitManager;

use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$this->kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."No kit was found by the name '$args[kit]'");
			return;
		}

		$this->kitManager->remove($args['kit']);
		$sender->sendMessage("Removed kit '$args[kit]' successfully");
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.delete');
		$this->registerArgument(0, new RawStringArgument('kit'));
	}
}