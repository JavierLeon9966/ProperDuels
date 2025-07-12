<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit;

use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\BaseCommand;
use JavierLeon9966\ProperDuels\command\kit\subcommand\{CreateSubCommand,
	DeleteSubCommand,
	DisableSubCommand,
	EnableSubCommand,
	ListSubCommand,
	UpdateSubCommand};
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class KitCommand extends BaseCommand{

	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermissions([
			'properduels.command.kit.create',
			'properduels.command.kit.delete',
			'properduels.command.kit.list',
			'properduels.command.kit.enable',
			'properduels.command.kit.disable'
		]);
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof PluginBase);
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', $this->kitManager));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', $this->kitManager));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', $this->kitManager));
		$this->registerSubCommand(new UpdateSubCommand($plugin, 'update', $this->kitManager));
		$this->registerSubCommand(new EnableSubCommand($plugin, 'enable', $this->kitManager));
		$this->registerSubCommand(new DisableSubCommand($plugin, 'disable', $this->kitManager));
	}
}