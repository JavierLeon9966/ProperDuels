<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena;

use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\arena\subcommand\{CreateSubCommand, ListSubCommand, DeleteSubCommand};

use JavierLeon9966\ProperDuels\ProperDuels;
use pocketmine\command\CommandSender;

class ArenaCommand extends BaseCommand{

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermissions([
			'properduels.command.arena.create',
			'properduels.command.arena.delete',
			'properduels.command.arena.list'
		]);
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof ProperDuels);
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create'));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete'));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list'));
	}
}
