<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\kit\KitManager;
use pocketmine\plugin\PluginBase;
use JavierLeon9966\ProperDuels\libs\_932519f2234d1e3a\CortexPE\Commando\args\{RawStringArgument, Vector3Argument};
use JavierLeon9966\ProperDuels\libs\_932519f2234d1e3a\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_932519f2234d1e3a\CortexPE\Commando\constraint\InGameRequiredConstraint;

use JavierLeon9966\ProperDuels\arena\Arena;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\{AssumptionFailedError, TextFormat};

class CreateSubCommand extends BaseSubCommand{

	public function __construct(PluginBase $plugin, string $name, private readonly ArenaManager $arenaManager, private readonly KitManager $kitManager, string $description = "", array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if($this->arenaManager->has($args['arena'])){
			$sender->sendMessage(TextFormat::RED."An arena with the name '$args[arena]' already exists");
			return;
		}

		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$world = $sender->getWorld();
		foreach(['firstSpawnPos', 'secondSpawnPos'] as $spawn){
			$pos = $args[$spawn]->floor();
			if(!$world->isInWorld((int)$pos->x, (int)$pos->y, (int)$pos->z)){
				$sender->sendMessage(TextFormat::RED.'Cannot set positions outside of the world');
				return;
			}
		}

		if(isset($args['kit']) and !$this->kitManager->has($args['kit'])){
			$sender->sendMessage(TextFormat::RED."No kit was found by the name '$args[kit]'");
			return;
		}

		$this->arenaManager->add(new Arena(
			$args['arena'],
			$world->getFolderName(),
			$args['firstSpawnPos'],
			$args['secondSpawnPos'],
			$args['kit'] ?? null
		));
		$sender->sendMessage("Added new arena '$args[arena]' successfully");
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.arena.create');

		$this->registerArgument(0, new RawStringArgument('arena'));
		$this->registerArgument(1, new Vector3Argument('firstSpawnPos'));
		$this->registerArgument(2, new Vector3Argument('secondSpawnPos'));
		$this->registerArgument(3, new RawStringArgument('kit', true));
	}
}