<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena;

use CortexPE\Commando\BaseCommand;

use JavierLeon9966\ProperDuels\command\arena\subcommand\{CreateSubCommand, ListSubCommand, DeleteSubCommand};

use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\ProperDuels;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class ArenaCommand extends BaseCommand{

	public function __construct(PluginBase $plugin, string $name, private readonly ArenaManager $arenaManager, private readonly KitManager $kitManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

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
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', $this->arenaManager, $this->kitManager));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', $this->arenaManager));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', $this->arenaManager));
	}
}
