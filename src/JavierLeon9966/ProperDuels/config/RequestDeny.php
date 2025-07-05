<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class RequestDeny{
	public function __construct(public string $message, public string $success){
	}
}