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

final class Finder {
	/**
	 * Perform Dijkstra pathfinding on the shortest path with the cost function defined in the Cost class.
	 *
	 * @param QualifiedRef[] $calls
	 * @param Closure(string): bool $admitTailKind
	 * @return Path[]
	 */
	public static function find(ReadIndices $indices, array $calls, string $sourceKind, Closure $admitTailKind) {
		$heap = new Heap;
		$heap->insertPath(new Path(
			unreadCalls: $calls,
			tailKind: $sourceKind,
			mappings: [],
			implicitLoopDetector: [$sourceKind => true],
			cost: new Cost(0, 0),
		));

		/** @var Path[] $accepted */
		$accepted = [];
		while (!$heap->isEmpty()) {
			/** @var Path $path */
			$path = $heap->extract();

			/** @var Path[] $newPaths */
			$newPaths = [];

			if (count($path->unreadCalls) > 0) {
				$shiftedCalls = $path->unreadCalls;
				$call = array_shift($shiftedCalls);
				$matches = $indices->getNamedMappings()->find($path->tailKind, $call);
				foreach ($matches as $match) {
					// TODO also check parameter compatibility here?
					$newPaths[] = new Path(
						unreadCalls: $shiftedCalls,
						tailKind: $match->mapping->targetKind,
						mappings: array_merge($path->mappings , [$match->mapping]),
						implicitLoopDetector: [$match->mapping->targetKind => true],
						cost: $path->cost->addMapping($match->score),
					);
				}
			}

			$implicits = $indices->getImplicitMappings()->getImplicit($path->tailKind);
			foreach ($implicits as $implicit) {
				if (isset($path->implicitLoopDetector[$implicit->targetKind])) {
					continue;
				}

				$newPaths[] = new Path(
					unreadCalls: $path->unreadCalls, // $call was not consumed, don't shift
					tailKind: $implicit->targetKind,
					mappings: array_merge($path->mappings, [$implicit]),
					implicitLoopDetector: $path->implicitLoopDetector + [$implicit->targetKind => true],
					cost: $path->cost->addMapping(0),
				);
			}

			foreach ($newPaths as $newPath) {
				if (count($newPath->unreadCalls) === 0 && $admitTailKind($newPath->tailKind)) {
					$accepted[] = $newPath;
				} else {
					$heap->insertPath($newPath);
				}
			}
		}

		return $accepted;
	}
}