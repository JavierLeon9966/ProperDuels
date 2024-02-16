<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class RequestAccept{
	public function __construct(public string $message, public string $playerInDuel, public string $success){
	}
}