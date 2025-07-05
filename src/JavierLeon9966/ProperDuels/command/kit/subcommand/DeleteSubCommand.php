<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class DeleteSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		/** @var array{'kit': string} $args */
		if(!$this->kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."No kit was found by the name '$args[kit]'");
			return;
		}

		$this->kitManager->remove($args['kit']);
		$sender->sendMessage("Removed kit '$args[kit]' successfully");
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