<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;

use JavierLeon9966\ProperDuels\arena\ArenaManager;

use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly ArenaManager $arenaManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$this->arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			return;
		}

		$this->arenaManager->remove($args['arena']);
		$sender->sendMessage("Removed arena '$args[arena]' successfully");
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena.delete');
		$this->registerArgument(0, new RawStringArgument('arena'));
	}
}
