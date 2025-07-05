<?php

declare(strict_types=1);

/**
 * Constants for standard kinds shared between instances of the shaded virion.
 *
 * Each kind has its own class to allow adding new standard kinds in the future.
 */
namespace Shared\SOFe\InfoAPI\Standard;

















































/**
 * An info of type `\pocketmine\block\Block`, representing a block type.
 *
 * In the BlockTypeInfo case, the position in the block is meaningless.
 */
final class BlockTypeInfo {
	public const KIND = "infoapi/blockType";
}