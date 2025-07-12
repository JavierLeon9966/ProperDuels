<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\event\Event;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\GeneratorUtil;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Traverser;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\PmEvent\Blocks;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\PmEvent\Events;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\Zleep\Zleep;





















final class PluginInitContext implements InitContext {
	public function __construct(private Plugin $plugin) {
	}

	public function watchEvent(array $events, string $key, Closure $interpreter) : Traverser {
		return Events::watch($this->plugin, $events, $key, $interpreter);
	}

	public function watchBlock(Position $position) : Traverser {
		return Traverser::fromClosure(function() use ($position) {
			$traverser = Blocks::watch($position);
			try {
				while ($traverser->next($_block)) {
					yield null => Traverser::VALUE;
				}
			} finally {
				yield from $traverser->interrupt();
			}
		});
	}

	public function sleep(int $ticks) : Generator {
		return Zleep::sleepTicks($this->plugin, $ticks);
	}
}