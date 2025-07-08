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






















































final class Floats {
	public static function register(Indices $indices) : void {
		$indices->registries->kindMetas->register(new KindMeta(Standard\FloatInfo::KIND, "Float", sprintf("A whole number between %e and %e", PHP_FLOAT_MIN, PHP_FLOAT_MAX), []));
		$indices->registries->displays->register(new Display(Standard\FloatInfo::KIND, fn($value) => is_int($value) || is_float($value) ? (string) $value : Display::INVALID));

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["floor"], fn(float $value) : int => (int) floor($value),
			help: "Round down the number.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["ceil", "ceiling"], fn(float $value) : int => (int) ceil($value),
			help: "Round up the number.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["round"], fn(float $value) : int => (int) round($value),
			help: "Round the number to the nearest integer.",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["gt", "greater"], fn(float $v1, float $v2) : bool => $v1 > $v2,
			help: "Check if a number is greater than another.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["ge", "greaterEqual"], fn(float $v1, float $v2) : bool => $v1 >= $v2,
			help: "Check if a number is greater than or equal to another.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["lt", "less"], fn(float $v1, float $v2) : bool => $v1 < $v2,
			help: "Check if a number is less than another.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["le", "lessEqual"], fn(float $v1, float $v2) : bool => $v1 <= $v2,
			help: "Check if a number is less than or equal to another.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["eq", "equal"], fn(float $v1, float $v2) : bool => $v1 === $v2,
			help: "Check if two numbers are equal. Note that two floats are almost never equal unless they were converted from the same integer.",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["abs", "absolute"], fn(float $v) : float => abs($v),
			help: "Take the absolute value.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["neg", "negate"], fn(float $v) : float => -$v,
			help: "Flip the positive/negative sign.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["add", "plus", "sum"], fn(float $v1, float $v2) : float => $v1 + $v2,
			help: "Add two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["sub", "subtract", "minus"], fn(float $v1, float $v2) : float => $v1 - $v2,
			help: "Subtract two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["mul", "mult", "multiply", "times", "prod", "product"], fn(float $v1, float $v2) : float => $v1 * $v2,
			help: "Multiply two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["div", "divide"], fn(float $v1, float $v2) : float => $v1 / $v2,
			help: "Divide two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["quotient"], fn(float $v1, float $v2) : int => (int) ($v1 / $v2),
			help: "Divide two numbers and take the integer quotient.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["remainder", "rem", "modulus", "mod"], fn(float $v1, float $v2) : float => fmod($v1, $v2),
			help: "Divide two numbers and take the remainder.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["greater", "max", "maximum"], fn(float $v1, float $v2) : float => max($v1, $v2),
			help: "Take the greater of two numbers.",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["less", "min", "minimum"], fn(float $v1, float $v2) : float => min($v1, $v2),
			help: "Take the less of two numbers.",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["pow", "power"], fn(float $v, float $exp) : float => pow($v, $exp),
			help: "Raise the number to the power \"exp\".",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:number", ["rec", "reciprocal", "inv", "inverse"], fn(float $value) : float => 1 / $value,
			help: "Take the reciprocal of a number, i.e. 1 divided by the number.",
		);
	}
}