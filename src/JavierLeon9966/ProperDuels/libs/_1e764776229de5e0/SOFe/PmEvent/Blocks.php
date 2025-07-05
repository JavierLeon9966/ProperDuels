<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\PmEvent;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkListenerNoOpTrait;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\AwaitGenerator\Channel;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\AwaitGenerator\Traverser;
use function count;
use function spl_object_id;

final class Blocks implements ChunkLoader, ChunkListener {
	/** @var array<int, array<int, self>> */
	private static array $store = [];

	/**
	 * @return Traverser<Block>
	 */
	public static function watch(Position $position) : Traverser {
		$world = $position->getWorld();
		$worldId = $world->getId();
		if (!isset(self::$store[$worldId])) {
			self::$store[$worldId] = [];
		}

		$chunkX = $position->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $position->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		$chunkHash = World::chunkHash($chunkX, $chunkZ);

		if (!isset(self::$store[$worldId][$chunkHash])) {
			$loader = new self($world);
			$world->registerChunkLoader($loader, $chunkX, $chunkZ);
			$world->registerChunkListener($loader, $chunkX, $chunkZ);
			self::$store[$worldId][$chunkHash] = $loader;
		}

		$loader = self::$store[$worldId][$chunkHash];

		$channel = new Channel;

		$blockHash = World::chunkBlockHash($position->getFloorX(), $position->getFloorY(), $position->getFloorZ());
		$loader->index[$blockHash][spl_object_id($channel)] = $channel;

		return Util::traverseChannels([$channel], function() use ($channel, $loader, $blockHash, $chunkHash, $worldId, $world, $chunkX, $chunkZ) {
			unset($loader->index[$blockHash][spl_object_id($channel)]);

			if (count($loader->index[$blockHash]) === 0) {
				unset($loader->index[$blockHash]);
			}

			if (count($loader->index) === 0) {
				unset(self::$store[$worldId][$chunkHash]);
				$world->unregisterChunkLoader($loader, $chunkX, $chunkZ);
				$world->unregisterChunkListener($loader, $chunkX, $chunkZ);
			}

			if (count(self::$store[$worldId]) === 0) {
				unset(self::$store[$worldId]);
			}
		});
	}

	use ChunkListenerNoOpTrait;

	/** @var array<int, Channel<Block>[]> */
	private array $index = [];

	private function __construct(private World $world) {
	}

	public function onBlockChanged(Vector3 $pos) : void {
		$channels = $this->index[World::chunkBlockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())];

		$block = $this->world->getBlock($pos, addToCache: false); // do not spam the object cache

		foreach ($channels as $channel) {
			$channel->sendWithoutWait($block);
		}
	}
}