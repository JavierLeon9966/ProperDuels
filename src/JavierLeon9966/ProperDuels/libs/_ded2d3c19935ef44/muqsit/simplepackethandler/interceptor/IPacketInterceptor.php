<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

interface IPacketInterceptor{

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptIncoming(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptOutgoing(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor;
}