<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\muqsit\simplepackethandler;

use InvalidArgumentException;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\muqsit\simplepackethandler\interceptor\IPacketInterceptor;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\muqsit\simplepackethandler\interceptor\PacketInterceptor;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\muqsit\simplepackethandler\monitor\IPacketMonitor;
use JavierLeon9966\ProperDuels\libs\_92d1364612b7d666\muqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handle_cancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, PacketPool::getInstance(), $priority, $handle_cancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handle_cancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, PacketPool::getInstance(), $handle_cancelled);
	}
}