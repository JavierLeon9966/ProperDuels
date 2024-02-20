<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit;

use JavierLeon9966\ProperDuels\libs\_db4403b1a7f63e34\CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\kit\subcommand\{CreateSubCommand, DeleteSubCommand, ListSubCommand};

use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\ProperDuels;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class KitCommand extends BaseCommand{

	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

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
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', $this->kitManager));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', $this->kitManager));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', $this->kitManager));
	}
}