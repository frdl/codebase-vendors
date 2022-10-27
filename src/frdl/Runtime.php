<?php

namespace frdl{

 class Runtime
 {
	 protected static $mutex = null;
	 protected $obj = null;
	 protected function __construct(){
	      $this->obj = \frdl\create(function () {        // function-constructor
                 $this->container = null;  // add property
			     $this->kernel = null;
                 $this->getContainer = function (bool $load = true) { // add method
					  if(true === $load && null===$this->container){
						 $this->container = new \frdl\ContainerCollection();   
					  }                      
					 return $this->container;          
				 };		 			 
		  });
		 
	
		 $this->obj->prototype = \frdl\create([    
			 'addContainer' => function (\Psr\Container\ContainerInterface $container) {     
				 return $this->getContainer()->addContainer($container); 
			 },			 
			 'setKernel' => function (\frdlweb\AppInterface $kernel) {     
					 $this->kernel = $kernel; 
				 return $this;
			 },
			 'getKernel' => function (bool $load = true) : \frdlweb\AppInterface {     
				 if(true === $load && null === $this->kernel){				
					 $this->setKernel( \Webfan\Webfat\App\Kernel::mutex() ); 
				 }
				 return $this->kernel;
			 },
		 ]);		 
		
		//$this->obj = $this->obj();
	 }
	 
	 public function getObject(){
		 return $this->obj;
	 }
	 
	 public static function global(){		 
		 if(null === self::$mutex){
			 $class = \get_called_class();
			 self::$mutex = new $class();
		 }
		 return self::$mutex->getObject();
	 }
 }
		
	
}
