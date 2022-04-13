<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels\session;

use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\invite\Invite;
use JavierLeon9966\ProperDuels\game\Game;
use JavierLeon9966\ProperDuels\ProperDuels;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class Session{
	/**
	 * @var Invite[]
	 * @phpstan-var array<string, Invite>
	 */
	private $invites = [];

	private $game = null;

	private $player;

	private $info;

	public function __construct(Player $player){
		$this->player = $player;
		$this->saveInfo();
	}

	public function addInvite(Session $session, ?Arena $arena): void{
		$properDuels = ProperDuels::getInstance();
		$arenaManager = $properDuels->getArenaManager();
		if($this->game !== null or $session->getGame() !== null or $arena === null and count($arenaManager->all()) === 0){
			return;
		}

		$gameManager = $properDuels->getGameManager();
		$arena = $arena ?? $arenaManager->get(array_rand(count($gameManager->all()) === 0 ? $arenaManager->all() : array_udiff(
			$arenaManager->all(),
			$gameManager->all(),
			static function(Arena $a, Game $b): int{
				return strcasecmp($a->getName(), $b->getArena()->getName());
			}
		)));

		$config = $properDuels->getConfig();

		$player = $session->getPlayer();

		if($gameManager->has($arena->getName())){
			$player->sendMessage($config->getNested('match.inUse'));
			return;
		}

		$time = $config->getNested('request.expire.time');
		$player->sendMessage(str_replace(
			['{player}', '{arena}', '{seconds}'],
			[$this->player->getDisplayName(), $arena->getName(), (string)$time],
			$config->getNested('request.invite.success')
		));
		$this->player->sendMessage(str_replace(
			['{player}', '{arena}', '{seconds}'],
			[$player->getDisplayName(), $arena->getName(), (string)$time],
			$config->getNested('request.invite.message')
		));

		$this->invites[$player->getUniqueId()->getBytes()] = new Invite($arena, $this, $session, (int)(20 * $time));
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
