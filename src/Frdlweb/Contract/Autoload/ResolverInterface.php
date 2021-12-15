<?php

namespace Frdlweb\Contract\Autoload;

	
     interface ResolverInterface {
        public function resolve(string $class):bool|string;
        public function file(string $class):bool|string; //local or cache
        public function url(string $class):bool|string;  //remote or ...?
     }
	
	 
