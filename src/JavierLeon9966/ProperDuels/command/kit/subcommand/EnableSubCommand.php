<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\kit\KitUpdateStatus;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use SOFe\AwaitGenerator\Await;

class EnableSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		Await::f2c(function() use($sender, $args): Generator{
			/** @var array{'kit': string} $args */
			$status = yield from $this->kitManager->setEnabled($args['kit'], true);

			if($sender instanceof Player and !$sender->isConnected()){
				return;
			}

			$sender->sendMessage(match($status){
				KitUpdateStatus::SUCCESS => "Enabled kit '$args[kit]' successfully",
				KitUpdateStatus::NOT_FOUND => TextFormat::RED . "A kit with the name '$args[kit]' does not exist",
				KitUpdateStatus::NO_CHANGES => TextFormat::RED . "The kit '$args[kit]' is already enabled"
			});
		});
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.kit.enable');

		try{
			$this->registerArgument(0, new RawStringArgument('kit'));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}