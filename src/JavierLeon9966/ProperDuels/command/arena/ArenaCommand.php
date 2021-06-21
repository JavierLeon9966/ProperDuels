<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena;

use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\arena\subcommand\{CreateSubCommand, ListSubCommand, DeleteSubCommand};

use pocketmine\command\CommandSender;

class ArenaCommand extends BaseCommand{

	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena');
		$this->registerSubCommand(new CreateSubCommand($this->plugin, 'create'));
		$this->registerSubCommand(new DeleteSubCommand($this->plugin, 'delete'));
		$this->registerSubCommand(new ListSubCommand($this->plugin, 'list'));
	}
}
