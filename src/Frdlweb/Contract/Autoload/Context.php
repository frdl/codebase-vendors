<?php

namespace Frdlweb\Contract\Autoload;
use Frdlweb\Contract\Webfantized\Project as WebfantizedProject;
	
interface Context {
		public function withPhpVersion(string $version);
		public function withProject(WebfantizedProject $Project);
}
	
  
