<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\Defaults;

use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Standard;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\Indices;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI\ReflectUtil;
use function sprintf;









































final class Vectors {
	public static function register(Indices $indices) : void {
		$indices->registries->kindMetas->register(new KindMeta(Standard\VectorInfo::KIND, "Vector", "A relative vector representing a direction and magnitude in 3D", []));
		$indices->registries->displays->register(new Display(
			Standard\VectorInfo::KIND,
			fn($value) => $value instanceof Vector3 ? sprintf("(%s, %s, %s)", $value->x, $value->y, $value->z) : Display::INVALID,
		));

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["x"], fn(Position $v) : float => $v->x,
			help: "X-component of this vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["y"], fn(Position $v) : float => $v->y,
			help: "Y-component of this vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["z"], fn(Position $v) : float => $v->z,
			help: "Z-component of this vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["add", "plus"],
			fn(Vector3 $v, Vector3 $other) : Vector3 => $v->addVector($other),
			help: "Sum of two vectors",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["sub", "subtract", "minus"],
			fn(Vector3 $v, Vector3 $other) : Vector3 => $v->subtractVector($other),
			help: "Subtract two vectors",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["mul", "mult", "multiply", "times", "scale"],
			fn(Vector3 $v, float $scale) : Vector3 => $v->multiply($scale),
			help: "Multiply a vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["div", "divide"],
			fn(Vector3 $v, float $scale) : Vector3 => $v->divide($scale),
			help: "Divide a vector",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["len", "length", "mod", "modulus", "mag", "magnitude", "norm"],
			fn(Vector3 $v) : float => $v->length(),
			help: "Length of a vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["unit", "dir", "direction"], fn(Vector3 $v) : Vector3 => $v->normalize(),
			help: "A unit vector in the same direction with length 1",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["withLength"], fn(Vector3 $v, float $length) : Vector3 => $v->multiply($length / $v->length()),
			help: "A vector in the same direction with the specified length",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["dot"],
			fn(Vector3 $v, Vector3 $other) : float => $v->dot($other),
			help: "Compute the dot product of two vectors",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["cross"],
			fn(Vector3 $v, Vector3 $other) : Vector3 => $v->cross($other),
			help: "Compute the cross product of two vectors",
		);
	}
}