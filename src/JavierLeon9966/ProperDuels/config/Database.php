<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class Database{
	public function __construct(
		public DatabaseType $type,
		public SQLiteConfig $sqlite,
		public MysqlCredentials $mysql,
		public int $workerLimit
	){
	}
}