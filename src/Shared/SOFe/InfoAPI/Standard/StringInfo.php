<?php

declare(strict_types=1);

/**
 * Constants for standard kinds shared between instances of the shaded virion.
 *
 * Each kind has its own class to allow adding new standard kinds in the future.
 */
namespace Shared\SOFe\InfoAPI\Standard;









/** An info of type `string`, representing a generic string. */
final class StringInfo {
	public const KIND = "infoapi/string";
}