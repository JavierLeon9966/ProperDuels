<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\Zleep;

use Closure;
use Generator;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\ReversePriorityQueue;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\AwaitGenerator\Await;
use SplPriorityQueue;
use function is_finite;
use function max;
use function microtime;

final class Zleep {
	/**
	 * Sleep for the specified number of ticks.
	 */
	public static function sleepTicks(Plugin $plugin, int $ticks) : Generator {
		/** @var ?TaskHandler $handler */
		$handler = null;
		try {
			yield from Await::promise(function($resolve) use ($plugin, $ticks, &$handler) {
				$handler = $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask($resolve), $ticks);
			});
			$handler = null;
		} finally {
			if ($handler !== null) {
				$handler->cancel();
			}
		}
	}

	/**
	 * Sleep until $seconds seconds have passed
	 */
	public static function sleepSeconds(Plugin $plugin, float $seconds) : Generator {
		yield from self::sleepUntilTimestamp($plugin, microtime(true) + $seconds);
	}

	/**
	 * Sleep until the given timestamp.
	 */
	public static function sleepUntilTimestamp(Plugin $plugin, float $target) : Generator {
		$heap = self::$clockLoop ?? new TimestampHeap;
		$promise = Await::promise(fn($resolve) => $heap->insert($target, $resolve));
		if (self::$clockLoop === null) {
			Await::g2c(self::runClockLoop($plugin, $heap));
		}
		yield from $promise;
	}

	private static ?TimestampHeap $clockLoop = null;
	private static function runClockLoop(Plugin $plugin, TimestampHeap $heap) : Generator {
		self::$clockLoop = $heap;

		while (is_finite($rem = $heap->getRemaining())) {
			if ($rem >= 0.05) { // more than one tick
				yield from self::sleepTicks($plugin, 1);
				continue;
			}

			$closure = $heap->shift();
			if ($closure !== null) {
				$closure();
			}
		}

		self::$clockLoop = null;
	}
}