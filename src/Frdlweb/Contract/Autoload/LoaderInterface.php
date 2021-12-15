<?php
namespace Frdlweb\Contract\Autoload;
 
	interface LoaderInterface {
		public function register(bool $prepend = false);
	}
