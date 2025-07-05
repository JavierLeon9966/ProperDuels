<?php

declare(strict_types=1);

/**
 * Constants for standard kinds shared between instances of the shaded virion.
 *
 * Each kind has its own class to allow adding new standard kinds in the future.
 */
namespace Shared\SOFe\InfoAPI\Standard;

/**
 * The base context that is the implicit mapping target of resolve contexts.
 * Define mappings from this kind to expose "global functions".
 */
final class BaseContext {
	public const KIND = "infoapi/base";
}