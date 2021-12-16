<?php

namespace Frdlweb\Contract\Autoload;
use Frdlweb\Contract\Webfantized\Project as WebfantizedProject;
	
interface ContextInterface {
		public function withPhpVersion(string $version);
		public function withProject(WebfantizedProject $Project);
		public function withExtension(string $extension);
	    public function getPhpVersion() : string;
	    public function getExtensions() : array;
	    public function getProjects() : array;
}
	
