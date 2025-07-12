<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Await;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;

/**
 * @implements GetOrWatch<RenderedWatchElement, RenderedWatchGroup>
 */
final class Watch implements GetOrWatch {
	public function buildResult(array $elements) : RenderedGroup {
		return new RenderedWatchGroup($elements);
	}

	public function startEvalChain() : EvalChain {
		return new WatchEvalChain;
	}

	public function staticElement(string $raw) : RenderedElement {
		return new StaticRenderedElement($raw);
	}
}