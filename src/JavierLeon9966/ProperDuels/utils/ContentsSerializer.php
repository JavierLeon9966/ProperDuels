<?php

namespace JavierLeon9966\ProperDuels\utils;

use JavierLeon9966\ProperDuels\kit\Kit;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class ContentsSerializer{

	/** @return array<int, Item> */
	public static function deserializeItemContents(string $serializedContents): array{
		/** @var array<int, Item> $contents */
		$contents = [];
		/** @var ListTag $contentsTag */
		$contentsTag = (new LittleEndianNbtSerializer())->read($serializedContents)->getTag();
		/** @var CompoundTag $itemTag */
		foreach($contentsTag as $itemTag){
			$itemStack = GlobalItemDataHandlers::getUpgrader()->upgradeItemStackNbt($itemTag) ??
				throw new AssumptionFailedError('This should never happen');
			$slot = $itemStack->getSlot() ?? throw new AssumptionFailedError('This should never happen');
			$contents[$slot] = GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStack);
		}
		return $contents;
	}

	/** @param array<int, Item> $contents */
	public static function serializeItemContents(array $contents): string{
		$contentsTag = new ListTag();
		foreach($contents as $slot => $item){
			$contentsTag->push($item->nbtSerialize($slot));
		}
		return (new LittleEndianNbtSerializer())->write(new TreeRoot($contentsTag));
	}
}
