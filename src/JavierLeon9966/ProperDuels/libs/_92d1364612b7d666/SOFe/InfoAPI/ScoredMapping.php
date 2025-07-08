<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI;

use Shared\SOFe\InfoAPI\Mapping;

use function array_filter;
use function array_unshift;
use function count;



























































final class ScoredMapping {
	public function __construct(
		public int $score,
		public Mapping $mapping,
	) {
	}
}