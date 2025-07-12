<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\kit\Kit;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;

class CreateSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		Await::f2c(function() use($sender, $args): Generator{
			try{
				/** @var array{'kit': string} $args */
				yield from $this->kitManager->add(new Kit(
					$args['kit'],
					$sender->getArmorInventory()->getContents(),
					$sender->getInventory()->getContents()
				));
			}catch(RuntimeException){
				if($sender->isConnected()){
					$sender->sendMessage(TextFormat::RED."A kit with the name '$args[kit]' already exists");
				}
				return;
			}
			if($sender->isConnected()){
				$sender->sendMessage("Added new kit '$args[kit]' successfully");
			}
		});
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.kit.create');

		try{
			$this->registerArgument(0, new RawStringArgument('kit'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}