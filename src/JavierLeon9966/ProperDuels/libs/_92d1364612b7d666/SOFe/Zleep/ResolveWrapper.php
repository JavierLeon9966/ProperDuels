<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\Zleep;

use Closure;
use Generator;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\ReversePriorityQueue;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\AwaitGenerator\Await;
use SplPriorityQueue;
use function is_finite;
use function max;
use function microtime;

















































































/** @internal */
final class ResolveWrapper{

	/** @param (Closure(): void)|null $closure */
	public function __construct(private ?Closure $closure) {
	}

	/** @return (Closure(): void)|null */
	public function getClosure() : ?Closure {
		return $this->closure;
	}

	public function cancel() : void {
		$this->closure = null;
	}
}