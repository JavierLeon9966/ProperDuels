<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit;

use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\kit\subcommand\{CreateSubCommand, DeleteSubCommand, ListSubCommand};

use JavierLeon9966\ProperDuels\ProperDuels;
use pocketmine\command\CommandSender;

class KitCommand extends BaseCommand{

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $commandLabel, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermissions([
			'properduels.command.kit.create',
			'properduels.command.kit.delete',
			'properduels.command.kit.list'
		]);
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof ProperDuels);
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create'));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete'));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list'));
	}
}
