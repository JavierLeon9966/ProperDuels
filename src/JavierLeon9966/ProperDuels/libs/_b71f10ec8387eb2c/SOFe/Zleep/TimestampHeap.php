<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\Zleep;

use Closure;
use Generator;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\ReversePriorityQueue;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Await;
use SplPriorityQueue;
use function is_finite;
use function max;
use function microtime;


































































































/**
 * @internal
 */
final class TimestampHeap {
	/** @var ReversePriorityQueue<float, ResolveWrapper> */
	private ReversePriorityQueue $queue;

	public function __construct() {
		$this->queue = new ReversePriorityQueue;
	}

	/**
	 * @param ResolveWrapper $resolveWrapper
	 */
	public function insert(float $target, ResolveWrapper $resolveWrapper) : void {
		$this->queue->insert($resolveWrapper, $target);
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
	 * @return null|ResolveWrapper
	 */
	public function shift() : ?ResolveWrapper {
		if ($this->queue->isEmpty()) {
			return null;
		}

		$this->queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
		/** @var ResolveWrapper $extract */
		$extract = $this->queue->extract();
		return $extract;
	}
}