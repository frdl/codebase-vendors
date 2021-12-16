<?php

namespace Webfan\Autoload;
use Webfan\Autoload\ContextInterface;


trait ConditionalAutoloadNegotiationTrait {
  protected $Context = null;
   public function withContext(ContextInterface $Context){
	   $this->Context=$Context;
   }  
	
	public function getContext() : ContextInterface {
	  return $this->Context;
   }
}