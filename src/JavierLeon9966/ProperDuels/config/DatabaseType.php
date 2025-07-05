<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

enum DatabaseType: string{
	case Mysql = 'mysql';
	case Sqlite3 = 'sqlite3';
}