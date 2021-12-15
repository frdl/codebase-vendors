<?php

namespace Frdlweb\Contract\Webfantized;

	interface Condition {
		public function __construct(bool|callable $if);
		public function __invoke(mixed $payload):bool;
	}	

