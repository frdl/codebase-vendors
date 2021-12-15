<?php

namespace Frdlweb\Contract\Autoload;
 

	interface ClassLoaderInterface {
		public function Autoload(string $class):bool|string;
	}	
