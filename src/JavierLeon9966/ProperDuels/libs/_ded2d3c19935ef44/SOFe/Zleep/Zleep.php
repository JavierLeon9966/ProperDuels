<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\Zleep;

use Closure;
use Generator;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\ReversePriorityQueue;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;
use SplPriorityQueue;
use function is_finite;
use function max;
use function microtime;

final class Zleep {
	/**
	 * Sleep for the specified number of ticks.
	 *
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
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
	 *
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	public static function sleepSeconds(Plugin $plugin, float $seconds) : Generator {
		yield from self::sleepUntilTimestamp($plugin, microtime(true) + $seconds);
	}

	/**
	 * Sleep until the given timestamp.
	 *
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	public static function sleepUntilTimestamp(Plugin $plugin, float $target) : Generator {
		/** @var null|ResolveWrapper $resolveWrapper */
		$resolveWrapper = null;
		try{
			yield from Await::promise(static function($resolve) use($plugin, $target, &$resolveWrapper) : void {
				$resolveWrapper = new ResolveWrapper($resolve);
				$heap = self::$clockLoop ?? new TimestampHeap;
				$heap->insert($target, $resolveWrapper);
				if (self::$clockLoop === null) {
					Await::g2c(self::runClockLoop($plugin, $heap));
				}
			});
		}finally{
			if ($resolveWrapper !== null) {
				$resolveWrapper->cancel();
			}
		}
	}

	private static ?TimestampHeap $clockLoop = null;
	/**
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	private static function runClockLoop(Plugin $plugin, TimestampHeap $heap) : Generator {
		self::$clockLoop = $heap;

		while (is_finite($rem = $heap->getRemaining())) {
			if ($rem >= 0.05) { // more than one tick
				yield from self::sleepTicks($plugin, 1);
				continue;
			}

			$resolveWrapper = $heap->shift();
			if ($resolveWrapper !== null) {
				$closure = $resolveWrapper->getClosure();
				if ($closure !== null) {
					$closure();
				}
			}
		}

		self::$clockLoop = null;
	}
}