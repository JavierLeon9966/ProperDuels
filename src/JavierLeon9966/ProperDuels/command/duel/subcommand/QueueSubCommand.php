<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\command\duel\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\exception\ArgumentOrderException;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\QueueManager;
use LogicException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{AssumptionFailedError, TextFormat};

class QueueSubCommand extends BaseSubCommand{

	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		private readonly ArenaManager $arenaManager,
		private readonly QueueManager $queueManager,
		string $description = '',
		array $aliases = []
	){
		parent::__construct($plugin, $name, $description, $aliases);
	}

	/**
	 * @param array<array-key, mixed> $args
	 *
	 * @throws \RuntimeException
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(!$sender instanceof Player){
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}
		$rawUUID = $sender->getUniqueId()->getBytes();
		/** @var array{'arena'?: string} $args */
		if(isset($args['arena'])){
			$arena = $this->arenaManager->get($args['arena']);
			if($arena === null){
				$sender->sendMessage(TextFormat::RED."No arena was found by the name '$args[arena]'");
				return;
			}

			if($this->queueManager->has($rawUUID)){
				$sender->sendMessage(TextFormat::RED.'You are already in a queue');
				return;
			}

			try{
				$this->queueManager->add($rawUUID, $arena);
			}catch(LogicException $e){
				throw new AssumptionFailedError('this should never happen', 0, $e);
			}
			$sender->sendMessage('Successfully added into the queue');
			return;
		}elseif($this->queueManager->has($rawUUID)){
			$this->queueManager->remove($rawUUID);
			$sender->sendMessage('Successfully removed from the queue');
			return;
		}

		if(count($this->arenaManager->all()) === 0){
			$sender->sendMessage(TextFormat::RED.'There are no existing arenas');
			return;
		}
		try{
			$this->queueManager->add($rawUUID);
		}catch(LogicException $e){
			throw new AssumptionFailedError('this should never happen', 0, $e);
		}
		$sender->sendMessage('Successfully added into the queue');
	}

	public function prepare(): void{
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('properduels.command.duel.queue');

		try{
			$this->registerArgument(0, new RawStringArgument('arena', true));
		}catch(ArgumentOrderException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
	}
}
