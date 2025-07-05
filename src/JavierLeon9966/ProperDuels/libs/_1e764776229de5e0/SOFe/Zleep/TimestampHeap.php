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



























































/**
 * @internal
 */
final class TimestampHeap {
	/** @var ReversePriorityQueue<float, Closure(): void> */
	private ReversePriorityQueue $queue;

	public function __construct() {
		$this->queue = new ReversePriorityQueue;
	}

	/**
	 * @param Closure(): void $callback
	 */
	public function insert(float $target, Closure $callback) : void {
		$this->queue->insert($callback, $target);
	}

	public function getRemaining() : float {
		if ($this->queue->isEmpty()) {
			return INF;
		}

		$this->queue->setExtractFlags(SplPriorityQueue::EXTR_PRIORITY);
		/** @var float $ts */
		$ts = $this->queue->top();
		return max(0.0, $ts - microtime(true));
	}

	/**
	 * @return Closure(): void
	 */
	public function shift() : ?Closure {
		if ($this->queue->isEmpty()) {
			return null;
		}

		$this->queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
		/** @var Closure(): void $extract */
		$extract = $this->queue->extract();
		return $extract;
	}
}