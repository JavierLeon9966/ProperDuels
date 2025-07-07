<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;

class ListSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		Await::f2c(function() use ($args, $sender): Generator{
			/** @var array{'page'?: int} $args */
			$page = $args['page'] ?? 1;
			$kits = yield from $this->kitManager->getList($page - 1, 10);
			if($sender instanceof Player && !$sender->isConnected()){
				return;
			}
			if(count($kits) === 0){
				$sender->sendMessage(TextFormat::RED.'There are no kits');
				return;
			}
			$sender->sendMessage(TextFormat::GREEN."Kits (Page $page):");
			foreach($kits as $kit){
				$sender->sendMessage(TextFormat::YELLOW . "- {$kit->getName()}");
			}
		});
	}

	public function prepare(): void{
		$this->setPermission('properduels.command.kit.list');
		try{
			$this->registerArgument(0, new IntegerArgument('page', true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}
