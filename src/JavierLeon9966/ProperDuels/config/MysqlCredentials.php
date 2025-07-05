<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class MysqlCredentials{
	public function __construct(
		public string $host,
		public string $username,
		public string $password,
		public string $schema,
		public int $port
	){
	}
}