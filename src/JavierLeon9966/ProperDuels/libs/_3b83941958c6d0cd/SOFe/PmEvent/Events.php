<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\PmEvent;

use Closure;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\AwaitGenerator\Channel;
use JavierLeon9966\ProperDuels\libs\_3b83941958c6d0cd\SOFe\AwaitGenerator\Traverser;
use function spl_object_id;

/**
 * @template E of Event
 * @internal
 */
final class Events {
	/** @var array<class-string<Event>, Events<Event>> */
	private static $muxStore = [];

	/**
	 * @template E_ of Event
	 * @param class-string<E_>[] $events
	 * @param Closure(E_): string $interpreter
	 * @return Traverser<E_>
	 */
	public static function watch(Plugin $plugin, array $events, string $key, Closure $interpreter) : Traverser {
		$channels = [];
		$finalizers = [];

		foreach($events as $event) {
			if (!isset(self::$muxStore[$event])) {
				$mux = new self($event, $interpreter);
				$mux->init($plugin);
				self::$muxStore[$event] = $mux;
			}

			/** @var Events<E_> $mux */
			$mux = self::$muxStore[$event];

			[$channel, $finalize] = $mux->subscribe($key);
			$channels[] = $channel;
			$finalizers[] = $finalize;
		}

		return Util::traverseChannels($channels, function() use($finalizers) {
			foreach($finalizers as $finalizer) {
				$finalizer();
			}
		});
	}

	/** @var array<string, Channel<E>[]> */
	private array $index = [];

	/**
	 * @param class-string<E> $event
	 * @param Closure(E): string $interpreter
	 */
	private function __construct(
		private string $event,
		private Closure $interpreter,
	) {
	}

	private function init(Plugin $plugin) : void {
		Server::getInstance()->getPluginManager()->registerEvent($this->event, $this->handle(...), EventPriority::MONITOR, $plugin);
	}

	/**
	 * @param E $event
	 */
	private function handle($event) : void {
		$key = ($this->interpreter)($event);
		foreach ($this->index[$key] ?? [] as $channel) {
			$channel->sendWithoutWait($event);
		}
	}

	/**
	 * @return array{Channel<E>, Closure(): void}
	 */
	private function subscribe(string $key) : array {
		$channel = new Channel;
		$this->index[$key][spl_object_id($channel)] = $channel;

		return [$channel, function() use ($channel, $key) {
			// Do not GC $channel until this function returns
			unset($this->index[$key][spl_object_id($channel)]);
		}];
	}
}