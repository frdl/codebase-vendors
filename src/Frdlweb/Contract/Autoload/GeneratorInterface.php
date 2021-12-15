<?php
namespace Frdlweb\Contract\Autoload;
 

	
	interface GeneratorInterface {				
	   public function withDirectory($dir); 
	   public function withAlias(string $alias, string $rewrite); 
	   public function withClassmap(array $classMap = null); 
	   public function withNamespace($prefix, $server, $prepend = false);
	}
	
    
