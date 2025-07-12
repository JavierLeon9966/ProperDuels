<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\InfoAPI;

use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\ReflectHint;
use Shared\SOFe\InfoAPI\Registry;
use function array_splice;
use function count;

/**
 * A Registry implementation to fill the GlobalRegistrySingleton.
 *
 * This implementation should be as simple as possible to minimize possible bugs.
 * For the sake of simplicity, indexing should be deferred to a separate wrapper object.
 *
 * @template T
 * @implements Registry<T>
 */
final class RegistryImpl implements Registry {
	/** @var T[] $objects */
	private array $objects = [];

	private int $generation = 0;

	/**
	 * @param ?Registry<T> $field
	 * @return Registry<T>
	 */
	public static function getInstance(?Registry &$field) : Registry {
		return $field ??= new self;
	}

	public function register($object) : void {
		$this->objects[] = $object;
		$this->generation += 1;
	}

	public function getGeneration() : int {
		return $this->generation;
	}

	public function getAll() : array {
		return $this->objects;
	}
}