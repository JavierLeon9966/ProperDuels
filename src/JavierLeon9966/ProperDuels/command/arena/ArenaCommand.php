<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena;

use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\CortexPE\Commando\BaseCommand;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\command\arena\subcommand\{CreateSubCommand, DeleteSubCommand, ListSubCommand};
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class ArenaCommand extends BaseCommand{

	public function __construct(PluginBase $plugin,
		string $name,
		private readonly ArenaManager $arenaManager,
		string $description = '',
		array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
	}

	public function prepare(): void{
		$this->setPermissions([
			'properduels.command.arena.create',
			'properduels.command.arena.delete',
			'properduels.command.arena.list'
		]);
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof PluginBase);
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', $this->arenaManager));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', $this->arenaManager));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', $this->arenaManager));
	}
}