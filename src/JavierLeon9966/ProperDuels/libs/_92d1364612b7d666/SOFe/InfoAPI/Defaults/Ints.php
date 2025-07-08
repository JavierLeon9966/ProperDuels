<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\Defaults;

use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Standard;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\Indices;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\ReflectUtil;
use function abs;
use function ceil;
use function floor;
use function fmod;
use function intdiv;
use function is_float;
use function is_int;
use function max;
use function min;
use function pow;
use function round;
use function sprintf;

final class Ints {
	public static function register(Indices $indices) : void {
		$indices->registries->kindMetas->register(new KindMeta(Standard\IntInfo::KIND, "Integer", sprintf("A whole number between %e and %e", PHP_INT_MIN, PHP_INT_MAX), []));
		$indices->registries->displays->register(new Display(Standard\IntInfo::KIND, fn($value) => is_int($value) ? (string) $value : Display::INVALID));

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["float"], fn(int $value) : float => (float) $value, isImplicit: true,
			help: "Convert the integre to a float",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["abs", "absolute"], fn(int $v) : int => abs($v),
			help: "Take the absolute value.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["neg", "negate"], fn(int $v) : int => -$v,
			help: "Flip the positive/negative sign.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["add", "plus", "sum"], fn(int $v1, int $v2) : int => $v1 + $v2,
			help: "Add two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["sub", "subtract", "minus"], fn(int $v1, int $v2) : int => $v1 - $v2,
			help: "Subtract two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["mul", "mult", "multiply", "times", "prod", "product"], fn(int $v1, int $v2) : int => $v1 * $v2,
			help: "Multiply two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["div", "divide"], fn(int $v1, int $v2) : float => $v1 / $v2,
			help: "Divide two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["quotient"], fn(int $v1, int $v2) : int => intdiv($v1, $v2),
			help: "Divide two numbers and take the integer quotient.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["remainder", "rem", "modulus", "mod"], fn(int $v1, int $v2) : int => $v1 % $v2,
			help: "Divide two numbers and take the remainder.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["greater", "max", "maximum"], fn(int $v1, int $v2) : int => max($v1, $v2),
			help: "Take the greater of two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["less", "min", "minimum"], fn(int $v1, int $v2) : int => min($v1, $v2),
			help: "Take the less of two numbers.",
		);
	}
}