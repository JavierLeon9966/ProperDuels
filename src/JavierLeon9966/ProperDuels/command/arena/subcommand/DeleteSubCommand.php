<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly ArenaManager $arenaManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		Await::f2c(function() use($args, $sender): Generator{
			/** @var array{arena: string} $args */
			$changed = yield from $this->arenaManager->remove($args['arena']);
			if($sender instanceof Player && !$sender->isConnected()){
				return;
			}
			if(!$changed){
				$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
			}else{
				$sender->sendMessage("Removed arena '$args[arena]' successfully");
			}
		});
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.arena.delete');
		try{
			$this->registerArgument(0, new RawStringArgument('arena'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}