<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\arena;

enum ArenaCreationStatus{

	case AlreadyExists;
	case InvalidKit;
	case Success;
}
