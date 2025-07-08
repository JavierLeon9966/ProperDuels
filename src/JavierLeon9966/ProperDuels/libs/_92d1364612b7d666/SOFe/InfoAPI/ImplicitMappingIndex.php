<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI;

use Shared\SOFe\InfoAPI\Mapping;

use function array_filter;
use function array_unshift;
use function count;



































































/**
 * @extends Index<Mapping>
 */
final class ImplicitMappingIndex extends Index {
	/** @var array<string, list<Mapping>> */
	private array $implicitMappings;

	public function reset() : void {
		$this->implicitMappings = [];
	}

	public function index($mapping) : void {
		$source = $mapping->sourceKind;

		if ($mapping->isImplicit) {
			if (!isset($this->implicitMappings[$source])) {
				$this->implicitMappings[$source] = [];
			}
			array_unshift($this->implicitMappings[$source], $mapping);
		}
	}

	/**
	 * @return list<Mapping>
	 */
	public function getImplicit(string $sourceKind) : array {
		$this->sync();
		return $this->implicitMappings[$sourceKind] ?? [];
	}

	public function cloned() : self {
		// this object is clone-safe
		return clone $this;
	}
}