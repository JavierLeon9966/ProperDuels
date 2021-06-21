<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\Player;

final class SessionManager{

	private $sessions = [];

	public function __construct(ProperDuels $plugin){
		$plugin->getServer()->getPluginManager()->registerEvents(new SessionListener, $plugin);
	}

	public function add(Player $player): void{
		$this->sessions[$player->getRawUniqueId()] = new Session($player);
	}

	public function all(): array{
		return $this->sessions;
	}

	public function close(): void{
		$this->sessions = [];
	}

	public function get(string $rawUUID): ?Session{
		return $this->sessions[$rawUUID] ?? null;
	}

	public function remove(string $rawUUID): void{
		unset($this->sessions[$rawUUID]);
	}
}
