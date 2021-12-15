<?php

namespace Webfan\Traits {

trait WithTimeout {
	
  public static function withTimeoutStatic(int $time_limit = null){
	  $class = \get_called_class();
	  $timeout = (is_int($time_limit))
		  ? $time_limit
		  : (
			     (null!==($class::TIMEOUT) && is_int($class::TIMEOUT) ) ? $class::TIMEOUT : 30
			  );
	  set_time_limit($timeout);
  }
	
  public function withTimeout(int $time_limit = null){
	   $class = \get_class($this);
	  $timeout = (is_int($time_limit))
		  ? $time_limit
		  : (
			     (null!==($class::TIMEOUT) && is_int($class::TIMEOUT) ) ? $class::TIMEOUT : 30
			  );
	  set_time_limit($timeout);
	  return $this;
  }


}
	

}