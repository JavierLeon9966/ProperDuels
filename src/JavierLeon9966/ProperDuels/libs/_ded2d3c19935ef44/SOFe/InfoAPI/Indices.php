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

























































































































































final class Indices implements ReadIndices {
	/**
	 * @param Registries[] $fallbackRegistries The non-default registries that this Indices object reads from.
	 */
	public function __construct(
		public Registries $registries,
		public DisplayIndex $displays,
		public NamedMappingIndex $namedMappings,
		public ImplicitMappingIndex $implicitMappings,
		public ReflectHintIndex $hints,
		public array $fallbackRegistries = [],
	) {
	}

	public static function forTest() : Indices {
		$registries = Registries::empty();
		return new self(
			registries: $registries,
			displays: new DisplayIndex([$registries->displays]),
			namedMappings: new NamedMappingIndex([$registries->mappings]),
			implicitMappings: new ImplicitMappingIndex([$registries->mappings]),
			hints: new ReflectHintIndex([$registries->hints]),
			fallbackRegistries: [],
		);
	}

	public static function withDefaults(InitContext $initCtx, Registries $extension) : Indices {
		$defaults = Registries::empty();
		Defaults\Index::registerStandardKinds($defaults->hints);

		$indices = new Indices(
			registries: $defaults,
			displays: new DisplayIndex([$defaults->displays, $extension->displays]),
			namedMappings: new NamedMappingIndex([$defaults->mappings, $extension->mappings]),
			implicitMappings: new ImplicitMappingIndex([$defaults->mappings, $extension->mappings]),
			hints: new ReflectHintIndex([$defaults->hints, $extension->hints]),
			fallbackRegistries: [$defaults],
		);
		Defaults\Index::register($initCtx, $indices);

		$indices->registries = $extension;

		return $indices;
	}

	public function getDisplays() : DisplayIndex {
		return $this->displays;
	}
	public function getNamedMappings() : NamedMappingIndex {
		return $this->namedMappings;
	}
	public function getImplicitMappings() : ImplicitMappingIndex {
		return $this->implicitMappings;
	}
	public function getReflectHints() : ReflectHintIndex {
		return $this->hints;
	}

	public function readonly() : ReadonlyIndices {
		return new ReadonlyIndices(
			displays: $this->displays,
			namedMappings: $this->namedMappings,
			implicitMappings: $this->implicitMappings,
			hints: $this->hints,
		);
	}
}