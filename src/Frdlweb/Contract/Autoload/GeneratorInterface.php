<?php
namespace Frdlweb\Contract\Autoload;
 

	
	interface GeneratorInterface {	
	   public function withContext(Context $Context); 		
	   public function withPackage(string | array | \stdclass $urlPackageNameOrComposerJson ); 			
	   public function withDirectory($dir); 
	   public function withAlias(string $alias, string $rewrite); 
	   public function withClassmap(array $classMap = null); 
	   public function withNamespace($prefix, $server, $prepend = false);
	}
	
    
