<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\QualifiedRef;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;






































































/** A value in JSON format to be interpreted based on the type. */
final class JsonValue {
	public function __construct(
		public string $asString,
		public string $json,
	) {
	}
}