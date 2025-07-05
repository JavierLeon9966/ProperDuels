<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class Config{
	public function __construct(
		public Database $database,
		public MatchConfig $match,
		public Request $request
	){
	}
}