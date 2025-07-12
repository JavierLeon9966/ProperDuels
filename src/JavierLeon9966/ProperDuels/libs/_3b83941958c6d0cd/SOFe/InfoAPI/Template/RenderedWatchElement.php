<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\AwaitGenerator\Await;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;



























































































































interface RenderedWatchElement extends RenderedElement {
	/**
	 * @return Traverser<string>
	 */
	public function watch() : Traverser;
}