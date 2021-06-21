<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit;

use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\kit\subcommand\{CreateSubCommand, DeleteSubCommand, ListSubCommand};

use pocketmine\command\CommandSender;

class KitCommand extends BaseCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit');
		$this->registerSubCommand(new CreateSubCommand($this->plugin, 'create'));
		$this->registerSubCommand(new DeleteSubCommand($this->plugin, 'delete'));
		$this->registerSubCommand(new ListSubCommand($this->plugin, 'list'));
	}
}
