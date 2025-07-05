<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class ListSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$kits = array_keys($this->kitManager->all());
		$count = count($kits);
		if($count === 0){
			$sender->sendMessage(TextFormat::RED.'There are no kits');
			return;
		}

		$sender->sendMessage("There are $count kit(s):");
		$sender->sendMessage(implode(', ', $kits));
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.list');
	}
}