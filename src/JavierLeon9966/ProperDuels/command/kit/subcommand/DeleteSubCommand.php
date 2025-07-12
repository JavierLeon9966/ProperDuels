<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		Await::f2c(function() use($args, $sender): Generator{
			/** @var array{'kit': string} $args */
			$changed = yield from $this->kitManager->remove($args['kit']);
			if($sender instanceof Player && !$sender->isConnected()){
				return;
			}
			if(!$changed){
				$sender->sendMessage(TextFormat::RED."No kit was found by the name '$args[kit]'");
			}else{
				$sender->sendMessage("Removed kit '$args[kit]' successfully");
			}
		});
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.delete');
		try{
			$this->registerArgument(0, new RawStringArgument('kit'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}