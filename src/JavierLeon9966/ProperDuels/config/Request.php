<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\config;

final class Request{
	public function __construct(
		public RequestAccept $accept,
		public RequestDeny $deny,
		public RequestExpire $expire,
		public RequestInvite $invite
	){
	}
}