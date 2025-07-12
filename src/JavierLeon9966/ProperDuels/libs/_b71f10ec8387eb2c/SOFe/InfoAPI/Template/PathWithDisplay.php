<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\InfoAPI\Template;

use pocketmine\command\CommandSender;
use Shared\SOFe\InfoAPI\Display;
use JavierLeon9966\ProperDuels\libs\_b71f10ec8387eb2c\SOFe\AwaitGenerator\Traverser;

use function count;
use function sprintf;


















































































































































final class PathWithDisplay implements CoalesceChoice {
	public function __construct(
		public ResolvedPath $path,
		public Display $display,
	) {
	}

	public function getPath() : ResolvedPath {
		return $this->path;
	}

	public function getDisplay() : ?Display {
		return $this->display;
	}
}