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
	 'getFormFromRequestHelper' =>	function(string $message = '',
											 bool $autosubmit = true, 
											 $delay = 0,
											 \Psr\Http\Message\ServerRequestInterface $request = null){
	 if(null === $request){
		 $request = (null === $this->getContainer(false) || !$this->getContainer(false)->has('request'))
			 ? null : $this->container->get('request');
	 }
	 $vars = (null===$request)
		 ? $_POST 
		 : $request->getParsedBody();
	
	 $target =  (null===$request)
		 ? $_SERVER['REQUEST_URI']
		 : $request->getParsedBody();	 
		 
	 $method =  (null===$request)
		 ? $_SERVER['REQUEST_METHOD']
		 : $request->getMethod();	 
		 
	 $vars = (array)$vars;
	
	 $id = 'idr'.str_pad(time(), 32, "0", \STR_PAD_LEFT).str_pad(mt_rand(1,99999999), 8, "0", \STR_PAD_LEFT); 
	
	 $html = $message;
	 $html.='<form id="'.$id.'" action="'.$target.'" method="'.$method.'">';
	 foreach($vars as $n => $v){
		$html.='<input type="hidden" name="'.$n.'" value="'.strip_tags($v).'" />';
	 }
	 $html.='</form>';	
	
	 if(true === $autosubmit){
		$html.='<script>';
		$html.='(()=>{';
		 $html.='setTimeout(()=>{document.getElementById(\''.$id.'\').submit();}, '.$delay.')';
		$html.='})();';
		$html.='</script>';
	 }
	
	  return $html;
    },	 
			 
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
			 'isLocalhost' => function () :bool {   
				 return $_SERVER['REMOTE_ADDR'] === '127.0.0.1';
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
