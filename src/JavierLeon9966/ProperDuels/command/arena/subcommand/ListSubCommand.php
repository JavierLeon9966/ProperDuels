<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use CortexPE\Commando\BaseSubCommand;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(private readonly ProperDuels $plugin, string $name, string $description = "", array $aliases = []){
		parent::__construct($name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$arenas = array_keys($this->plugin->getArenaManager()->all());
		$count = count($arenas);
		if($count === 0){
			$sender->sendMessage(TextFormat::RED.'There are no arenas');
			return;
		}

		$sender->sendMessage("There are $count arena(s):");
		$sender->sendMessage(implode(", ", $arenas));
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena.list');
	}
}
