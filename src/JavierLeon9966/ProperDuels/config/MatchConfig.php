<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class MatchConfig{
	public function __construct(
		public bool $allowCommands,
		public MatchCountdown $countdown,
		public MatchFailure $failure,
		public string $finish,
		public string $inUse,
		public string $start
	){
	}
}