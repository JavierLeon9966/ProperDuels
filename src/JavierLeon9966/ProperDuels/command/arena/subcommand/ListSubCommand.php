<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;

class ListSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly ArenaManager $arenaManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		Await::f2c(function() use ($args, $sender): Generator{
			$limit = 10;
			/** @var array{'page'?: int} $args */
			$page = min(intdiv(Limits::INT64_MAX, $limit) + 1, max(1, $args['page'] ?? 1));
			$arenas = yield from $this->arenaManager->getList(($page - 1) * $limit, $limit);
			if($sender instanceof Player && !$sender->isConnected()){
				return;
			}
			$sender->sendMessage(TextFormat::GREEN."Arenas (Page $page):");
			foreach($arenas as $arena){
				$kitName = $arena->getKit() ?? 'Random';
				$sender->sendMessage(TextFormat::YELLOW . "- {$arena->getName()} (World: {$arena->getLevelName()}, Kit: $kitName, Spawns: {$arena->getFirstSpawnPos()->asVector3()}, {$arena->getSecondSpawnPos()->asVector3()})");
			}
		});
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena.list');
		try{
			$this->registerArgument(0, new IntegerArgument('page', true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}
