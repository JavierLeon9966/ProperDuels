<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class MatchFailure{
	public function __construct(public string $levelNotFound, public string $kitNotFound){
	}
}