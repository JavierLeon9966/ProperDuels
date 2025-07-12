<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_ded2d3c19935ef44\SOFe\InfoAPI;

use pocketmine\command\CommandSender;
use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;







































/**
 * @extends Index<KindMeta>
 */
final class KindMetaIndex extends Index {
	/** @var array<string, KindMeta> */
	private array $kinds;

	public function reset() : void {
		$this->kinds = [];
	}

	public function index($help) : void {
		$this->kinds[$help->kind] = $help;
	}

	public function get(string $kind) : ?KindMeta {
		$this->sync();

		if (!isset($this->kinds[$kind])) {
			return null;
		}

		return $this->kinds[$kind];
	}
}