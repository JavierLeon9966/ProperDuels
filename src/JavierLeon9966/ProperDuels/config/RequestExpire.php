<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class RequestExpire{
	public function __construct(public string $from, public string $to, public int $time){
	}
}