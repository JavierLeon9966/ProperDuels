<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\command\CommandSender;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;
use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use Shared\SOFe\InfoAPI\ReflectHint;
use Shared\SOFe\InfoAPI\Registry;
use function array_shift;
use function count;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_float;
use function is_object;


























































































































































































































/**
 * @extends Index<ReflectHint>
 */
final class ReflectHintIndex extends Index {
	/** @var array<class-string, string> */
	private array $map = [];

	public function reset() : void {
		$this->map = [];
	}

	public function index($object) : void {
		$this->map[$object->class] = $object->kind;
	}

	public function lookup(string $class) : ?string {
		$this->sync();

		return $this->map[$class] ?? null;
	}
}