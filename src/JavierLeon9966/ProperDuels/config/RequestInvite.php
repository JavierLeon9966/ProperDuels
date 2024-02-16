<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class RequestInvite{
	public function __construct(
		public string $failure,
		public string $message,
		public string $playerInDuel,
		public string $playerNotFound,
		public string $sameTarget,
		public string $success
	){
	}
}