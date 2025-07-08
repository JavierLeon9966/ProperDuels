<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\Defaults;

use Generator;
use pocketmine\block\Block;
use pocketmine\event\world\WorldEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\Server;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Standard;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\Indices;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\InitContext;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\SOFe\InfoAPI\ReflectUtil;
use function count;



















































































final class Blocks {
	public static function register(Indices $indices) : void {
		$indices->registries->kindMetas->register(new KindMeta(Standard\BlockTypeInfo::KIND, "Block type", "A type of block", []));
		$indices->registries->displays->register(new Display(
			Standard\BlockTypeInfo::KIND,
			fn($value) => $value instanceof Block ? $value->getName() : Display::INVALID,
		));

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:world", ["name"], fn(Block $v) : string => $v->getName(),
			help: "Block name",
		);
	}
}