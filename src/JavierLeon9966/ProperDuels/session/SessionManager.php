<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\GameManager;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;

final class SessionManager{

	/** @var array<string, Session> */
	private array $sessions = [];

	public function __construct(
		private readonly GameManager $gameManager,
		private readonly Config $config,
		private readonly Plugin $plugin,
		private readonly PluginManager $pluginManager
	){
	}

	public function add(Player $player): void{
		$this->sessions[$player->getUniqueId()->getBytes()] = new Session(
			$this->gameManager,
			$this->config,
			$this->plugin,
			$this->pluginManager,
			$player
		);
	}

	/** @return array<string, Session> */
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
