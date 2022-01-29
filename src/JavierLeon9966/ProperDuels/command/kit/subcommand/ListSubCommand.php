<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\BaseSubCommand;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListSubCommand extends BaseSubCommand{

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$this->plugin instanceof ProperDuels){
			throw new \UnexpectedValueException('This command wasn\'t created by ' . ProperDuels::class);
		}
		$kits = array_keys($this->plugin->getkitManager()->all());
		$count = count($kits);
		if($count === 0){
			$sender->sendMessage(TextFormat::RED.'There are no kits');
			return;
		}

		$sender->sendMessage("There are $count kit(s):");
		$sender->sendMessage(implode(", ", $kits));
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.list');
	}
}
