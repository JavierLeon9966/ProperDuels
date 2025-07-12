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





































































































final class Registries {
	/**
	 * @param Registry<KindMeta> $kindMetas
	 * @param Registry<Display> $displays
	 * @param Registry<Mapping> $mappings
	 * @param Registry<ReflectHint> $hints
	 */
	public function __construct(
		public Registry $kindMetas,
		public Registry $displays,
		public Registry $mappings,
		public Registry $hints,
	) {
	}

	public static function empty() : self {
		/** @var Registry<KindMeta> $kindMetas */
		$kindMetas = new RegistryImpl;
		/** @var Registry<Display> $displays */
		$displays = new RegistryImpl;
		/** @var Registry<Mapping> $mappings */
		$mappings = new RegistryImpl;
		/** @var Registry<ReflectHint> $hints */
		$hints = new RegistryImpl;

		return new self(
			kindMetas: $kindMetas,
			displays: $displays,
			mappings: $mappings,
			hints: $hints,
		);
	}

	public static function singletons() : self {
		/** @var Registry<KindMeta> $kindMetas */
		$kindMetas = RegistryImpl::getInstance(KindMeta::$global);
		/** @var Registry<Display> $displays */
		$displays = RegistryImpl::getInstance(Display::$global);
		/** @var Registry<Mapping> $mappings */
		$mappings = RegistryImpl::getInstance(Mapping::$global);
		/** @var Registry<ReflectHint> $hints */
		$hints = RegistryImpl::getInstance(ReflectHint::$global);

		return new self(
			kindMetas: $kindMetas,
			displays: $displays,
			mappings: $mappings,
			hints: $hints,
		);
	}
}