<?php

namespace Frdlweb\Contract\Autoload;
 

	interface RecordableClassLoaderInterface extends ClassLoaderInterface {
	    	public function record():void;
	    	public function getAutoloadCallstack():array;
	}
