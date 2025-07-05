<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\game\Game;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\invite\Invite;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use JavierLeon9966\ProperDuels\libs\_1e764776229de5e0\SOFe\InfoAPI\InfoAPI;

final class Session{
	/** @phpstan-var array<string, Invite> */
	private array $invites = [];

	private ?Game $game = null;

	private SessionInfo $info;

	public function __construct(
		private readonly ArenaManager $arenaManager,
		private readonly GameManager $gameManager,
		private readonly Config $config,
		private readonly Plugin $plugin,
		private readonly PluginManager $pluginManager,
		private readonly Player $player
	){
		$this->saveInfo();
	}

	public function addInvite(Session $session, ?Arena $arena): void{
		if($this->game !== null or $session->getGame() !== null or $arena === null and count($this->arenaManager->all()) === 0){
			return;
		}

		$arena = $arena ?? $this->arenaManager->get(array_rand(count($this->gameManager->all()) === 0 ? $this->arenaManager->all() : array_udiff(
			$this->arenaManager->all(),
			$this->gameManager->all(),
			static function(Arena|Game $a, Arena|Game $b): int{
				return strcasecmp(($a instanceof Arena ? $a : $a->getArena())->getName(), ($b instanceof Arena ? $b : $b->getArena())->getName());
			}
		))) ?? throw new AssumptionFailedError('This should never happen');

		$player = $session->getPlayer();

		if($this->gameManager->has($arena->getName())){
			$player->sendMessage(InfoAPI::render($this->plugin, $this->config->match->inUse, [], $player));
			return;
		}

		$time = $this->config->request->expire->time;
		$player->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->success, [
			'player' => $this->player,
			'arena' => $arena,
			'seconds' => $time
		], $player));
		$this->player->sendMessage(InfoAPI::render($this->plugin, $this->config->request->invite->message, [
			'player' => $player,
			'arena' => $arena,
			'seconds' => $time,
		], $this->player));

		$this->invites[$player->getUniqueId()->getBytes()] = new Invite(
			$this->config,
			$this->plugin,
			$arena,
			$this,
			$session,
			$this->pluginManager,
			Server::TARGET_TICKS_PER_SECOND * $time
		);
	}

	public function getInfo(): SessionInfo{
		return $this->info;
	}

	public function getInvite(string $rawUUID): ?Arena{
		return ($this->invites[$rawUUID] ?? null)?->getArena();
	}

	public function getGame(): ?Game{
		return $this->game;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function hasInvite(string $rawUUID): bool{
		return isset($this->invites[$rawUUID]);
	}

	public function removeInvite(string $rawUUID): void{
		if(isset($this->invites[$rawUUID])){
			$this->invites[$rawUUID]->close();
			unset($this->invites[$rawUUID]);
		}
	}

	public function saveInfo(): void{
		$this->info = new SessionInfo(
			$this->player->getArmorInventory()->getContents(),
			$this->player->getInventory()->getContents(),
			$this->player->getXpManager()->getCurrentTotalXp()
		);
	}

	public function setGame(?Game $game): void{
		$this->game = $game;
	}

	public function close(): void{
		foreach($this->invites as $invite){
			$invite->expire();
		}
	}
}