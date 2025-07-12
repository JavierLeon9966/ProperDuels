<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\Pathfind;

use Closure;
use Shared\SOFe\InfoAPI\Mapping;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\QualifiedRef;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\ReadIndices;
use SplPriorityQueue;
use function array_merge;
use function array_shift;
use function count;








































































final class Path {
	/**
	 * @param QualifiedRef[] $unreadCalls
	 * @param Mapping[] $mappings
	 * @param array<string, true> $implicitLoopDetector
	 */
	public function __construct(
		public array $unreadCalls,
		public string $tailKind,
		public array $mappings,
		public array $implicitLoopDetector,
		public Cost $cost,
	) {
	}
}