<?php

declare(strict_types=1);

namespace Shared\SOFe\InfoAPI;

use Closure;
use Generator;














































































































/**
 * Defines a parameter required for a mapping.
 */
final class Parameter {
	public function __construct(
		/** The name of the parameter. */
		public string $name,

		/**
		 * The kind of the parameter info.
		 * Parameters of primitive types may accept literal expressions too.
		 */
		public string $kind,

		/** Whether this parameter can be required multiple times. */
		public bool $multi,

		/** Whether this parameter is optional. */
		public bool $optional,

		/**
		 * Additional non-standard metadata to describe this mapping.
		 *
		 * @var array<string, mixed>
		 */
		public array $metadata,
	) {
	}
}