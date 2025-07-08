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
final class NamedMappingIndex extends Index {
	/** @var array<string, array<string, Mapping[]>> */
	private array $namedMappings;

	public function reset() : void {
		$this->namedMappings = [];
	}

	public function index($mapping) : void {
		$source = $mapping->sourceKind;
		$this->namedMappings[$source] ??= [];

		$shortName = $mapping->qualifiedName[count($mapping->qualifiedName) - 1];
		$this->namedMappings[$source][$shortName] ??= [];

		$this->namedMappings[$source][$shortName][] = $mapping;
	}

	/**
	 * @return ScoredMapping[]
	 */
	public function find(string $sourceKind, QualifiedRef $ref) : array {
		$this->sync();

		if (!isset($this->namedMappings[$sourceKind])) {
			return [];
		}

		$shortName = $ref->tokens[count($ref->tokens) - 1];
		if (!isset($this->namedMappings[$sourceKind][$shortName])) {
			return [];
		}

		$mappings = array_filter(
			$this->namedMappings[$sourceKind][$shortName],
			fn(Mapping $mapping) => (new FullyQualifiedName($mapping->qualifiedName))->match($ref) !== null,
		);

		$results = [];
		foreach ($mappings as $mapping) {
			$score = (new FullyQualifiedName($mapping->qualifiedName))->match($ref);
			if ($score !== null) {
				$results[] = new ScoredMapping($score, $mapping);
			}
		}

		return $results;
	}

	public function cloned() : self {
		// this object is clone-safe
		return clone $this;
	}
}