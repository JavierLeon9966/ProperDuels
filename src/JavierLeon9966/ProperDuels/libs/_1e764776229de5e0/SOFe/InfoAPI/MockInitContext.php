<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\event\Event;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\AwaitGenerator\GeneratorUtil;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\AwaitGenerator\Traverser;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\PmEvent\Blocks;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\PmEvent\Events;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\Zleep\Zleep;















































final class MockInitContext implements InitContext {
	public function watchEvent(array $events, string $key, Closure $interpreter) : Traverser {
		return new Traverser(GeneratorUtil::empty());
	}

	public function watchBlock(Position $position) : Traverser {
		return new Traverser(GeneratorUtil::empty());
	}

	public function sleep(int $ticks) : Generator {
		return GeneratorUtil::pending();
	}
}