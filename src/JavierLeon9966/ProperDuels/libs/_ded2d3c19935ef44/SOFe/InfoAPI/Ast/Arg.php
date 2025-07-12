<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI\QualifiedRef;
use JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;























































/** An argument passed to a mapping. */
final class Arg {
	public function __construct(
		/** Name of the argument if specified, e.g. `d` in `{ a:b(c, d=e) }`. */
		public ?string $name,

		/**
		 * The value of the argument.
		 * Parses as an Expr if it starts with an identifier (except `true` and `false`),
		 * otherwise parses as one JSON expression. */
		public JsonValue|Expr $value,
	) {
	}
}