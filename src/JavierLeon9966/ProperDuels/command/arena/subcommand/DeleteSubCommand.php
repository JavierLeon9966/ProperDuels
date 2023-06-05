<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

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
		$arenaManager = $this->plugin->getArenaManager();
		if(!$arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}

		$arenaManager->remove($args['arena']);
		$sender->sendMessage("Removed arena '$args[arena]' successfully");
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena.delete');
		$this->registerArgument(0, new RawStringArgument('arena'));
	}
}
