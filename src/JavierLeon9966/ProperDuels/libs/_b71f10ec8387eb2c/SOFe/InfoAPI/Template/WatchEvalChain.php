<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Await;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;


















/**
 * @implements EvalChain<RenderedWatchElement>
 */
final class WatchEvalChain implements EvalChain, RenderedWatchElement {
	private int $counter = 0;

	/** @var list<Closure(mixed): mixed> */
	private array $maps = [];
	/** @var array<int, Closure(mixed): ?Traverser<null>> */
	private array $subFuncs = [];

	/** @var array<int, true> */
	private array $breakpoints = [];

	/** @var mixed[] */
	private array $values = [];
	/** @var list<?Traverser<null>> */
	private array $traversers = [];

	public function then(Closure $map, ?Closure $subFunc) : void {
		$index = $this->counter++;

		$this->maps[$index] = $map;
		if ($subFunc !== null) {
			$this->subFuncs[$index] = $subFunc;
		}
	}

	public function breakOnNonNull() : bool {
		$this->breakpoints[$this->counter] = true;
		return false;
	}

	public function getResultAsElement() : RenderedElement {
		return $this;
	}

	public function watch() : Traverser {
		return Traverser::fromClosure(function() {
			try {
				while (true) {
					yield $this->getOnce() => Traverser::VALUE;

					$racers = [];
					foreach ($this->traversers as $k => $traverser) {
						if ($traverser !== null) {
							$racers[$k] = $traverser->next($_);
						}
					}
					if(count($racers) === 0) {
						// the entire expression is static
						break;
					}

					[$k, $running] = yield from Await::safeRace($racers);
					if ($running) {
						yield from $this->truncateTraversers($k);
					} else {
						// finalized traverser, no updates
						$this->traversers[$k] = null;
					}
				}
			} finally {
				yield from $this->truncateTraversers(0);
			}
		});
	}

	private function truncateTraversers(int $min) : Generator {
		for ($index = $min; $index < $this->counter; $index++) {
			unset($this->values[$index]);
			if (isset($this->traversers[$index])) {
				yield from $this->traversers[$index]->interrupt();
			}
			unset($this->traversers[$index]);
		}
	}

	private function getOnce() : string {
		for ($index = 0; $index < $this->counter; $index++) {
			$prev = $index > 0 ? $this->values[$index - 1] : null;

			if (isset($this->breakpoints[$index])) {
				if ($index > 0 && is_string($prev)) {
					return $this->values[$index - 1];
				}
			}

			if (!isset($this->values[$index])) {
				$this->values[$index] = ($this->maps[$index])($prev);

				$trigger = isset($this->subFuncs[$index]) ? $this->subFuncs[$index]($prev) : null;
				$this->traversers[$index] = $trigger;
			}
		}

		$last = $this->values[$this->counter - 1];
		if (!is_string($last)) {
			throw new RuntimeException("EvalChain::watch() cannot be called before a final then() to conclude errors");
		}

		return $last;
	}
}