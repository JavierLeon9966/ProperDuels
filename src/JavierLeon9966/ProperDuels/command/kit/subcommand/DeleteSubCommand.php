<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(private readonly ProperDuels $plugin, string $name, string $description = "", array $aliases = []){
		parent::__construct($name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$kitManager = $this->plugin->getKitManager();
		if(!$kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."No kit was found by the name '$args[kit]'");
			return;
		}

		$kitManager->remove($args['kit']);
		$sender->sendMessage("Removed kit '$args[kit]' successfully");
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.delete');
		$this->registerArgument(0, new RawStringArgument('kit'));
	}
}
