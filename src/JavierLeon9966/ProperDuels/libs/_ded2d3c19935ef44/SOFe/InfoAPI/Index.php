<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI;

use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\ReflectHint;
use Shared\SOFe\InfoAPI\Registry;
use function array_splice;
use function count;






































/**
 * Maintains search indices for objects from multiple registries.
 *
 * @template T
 */
abstract class Index {
	/** @var ?list<int> */
	private ?array $lastSyncGenerations = null;

	/**
	 * @param Registry<T>[] $registries
	 */
	public function __construct(
		private array $registries,
	) {
	}

	/**
	 * @param Registry<T> $newRegistry
	 */
	public function addLocalRegistry(int $position, Registry $newRegistry) : void {
		array_splice($this->registries, $position, 0, [$newRegistry]);
	}

	private function isSynced() : bool {
		if ($this->lastSyncGenerations === null || count($this->lastSyncGenerations) !== count($this->registries)) {
			return false;
		}

		foreach ($this->registries as $i => $registry) {
			if ($registry->getGeneration() !== $this->lastSyncGenerations[$i]) {
				return false;
			}
		}

		return true;
	}

	public function sync() : void {
		if ($this->isSynced()) {
			return;
		}

		$this->reset();
		$this->lastSyncGenerations = [];

		foreach ($this->registries as $i => $registry) {
			$this->lastSyncGenerations[$i] = $registry->getGeneration();

			foreach ($registry->getAll() as $object) {
				$this->index($object);
			}
		}
	}

	public abstract function reset() : void;

	/**
	 * @param T $object
	 */
	public abstract function index($object) : void;
}