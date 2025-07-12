<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\command\kit\subcommand;

use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\args\RawStringArgument;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\BaseSubCommand;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\CortexPE\Commando\exception\ArgumentOrderException;
use Generator;
use JavierLeon9966\ProperDuels\kit\Kit;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\kit\KitUpdateStatus;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;

final class UpdateSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(PluginBase $plugin, string $name, private readonly KitManager $kitManager, string $description = '', array $aliases = []){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/**
	 * @inheritDoc
	 */
	protected function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission("properduels.command.kit.update");
		try{
			$this->registerArgument(0, new RawStringArgument("name"));
			$this->registerArgument(1, new RawStringArgument("newName", true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		Await::f2c(function() use($sender, $args): Generator{
			/** @var array{'name': string, 'newName': ?string} $args */
			$msg = match(yield from $this->kitManager->update($args['name'], new Kit(
				$args['newName'] ?? $args['name'],
				$sender->getArmorInventory()->getContents(),
				$sender->getInventory()->getContents()
			))){
				KitUpdateStatus::SUCCESS => "Kit '{$args['name']}' updated successfully.",
				KitUpdateStatus::NOT_FOUND => "Kit '{$args['name']}' not found.",
				KitUpdateStatus::NO_CHANGES => "Kit '{$args['name']}' has no changes to update.",
			};
			if($sender->isConnected()){
				$sender->sendMessage($msg);
			}
		});
	}
}