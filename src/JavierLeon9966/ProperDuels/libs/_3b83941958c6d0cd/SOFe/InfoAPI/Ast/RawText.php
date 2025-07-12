<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\QualifiedRef;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;










/** Raw text that can be printed as-is. */
final class RawText {
	public function __construct(
		/** The parsed string to be printed, e.g. `a` in `{{ a | b | c }}`. */
		public string $parsed,
		/** The original string that contains escape sequences, e.g. `b | c` in `{{ a | b | c }}`. */
		public string $original,
	) {
	}
}