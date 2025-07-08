<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\arena\subcommand;

use Generator;
use JavierLeon9966\ProperDuels\arena\ArenaCreationStatus;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\CortexPE\Commando\args\{RawStringArgument, Vector3Argument};
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\AwaitGenerator\Await;

class CreateSubCommand extends BaseSubCommand{

	public function __construct(PluginBase $plugin,
		string $name,
		private readonly ArenaManager $arenaManager,
		string $description = '',
		array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}

		$world = $sender->getWorld();
		foreach(['firstSpawnPos', 'secondSpawnPos'] as $spawn){
			/** @var array{arena: string, firstSpawnPos: Vector3, secondSpawnPos: Vector3, kit?: string} $args */
			$pos = $args[$spawn]->floor();
			if(!$world->isInWorld((int)$pos->x, (int)$pos->y, (int)$pos->z)){
				$sender->sendMessage(TextFormat::RED.'Cannot set positions outside of the world');
				return;
			}
		}
		Await::f2c(function() use($args, $sender, $world): Generator{
			$msg = match(yield from $this->arenaManager->add(new Arena(
				$args['arena'],
				$world->getFolderName(),
				$args['firstSpawnPos'],
				$args['secondSpawnPos'],
				$kitName = $args['kit'] ?? null
			))){
				ArenaCreationStatus::Success => "Added new arena '$args[arena]' successfully",
				ArenaCreationStatus::AlreadyExists => TextFormat::RED."An arena with the name '$args[arena]' already exists",
				ArenaCreationStatus::InvalidKit => TextFormat::RED."No kit was found by the name '$kitName'",
			};
			if($sender->isConnected()){
				$sender->sendMessage($msg);
			}
		});
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.arena.create');

		try{
			$this->registerArgument(0, new RawStringArgument('arena'));
			$this->registerArgument(1, new Vector3Argument('firstSpawnPos'));
			$this->registerArgument(2, new Vector3Argument('secondSpawnPos'));
			$this->registerArgument(3, new RawStringArgument('kit', true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}