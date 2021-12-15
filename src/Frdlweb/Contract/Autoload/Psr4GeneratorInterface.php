<?php

namespace Frdlweb\Contract\Autoload;
 

	interface Psr4GeneratorInterface {		
		public function addNamespace($prefix, $resourceOrLocation, $prepend = false);
	}
