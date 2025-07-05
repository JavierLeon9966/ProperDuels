<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class MatchCountdown{
	public function __construct(public string $message, public int $time){
	}
}