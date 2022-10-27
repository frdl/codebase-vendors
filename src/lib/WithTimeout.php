<?php

namespace Webfan\Traits {

trait WithTimeout {
	
  public static function withTimeoutStatic(int $time_limit = null){
	  $class = \get_called_class();
	  $timeout = (is_int($time_limit))
		  ? $time_limit
		  : (
			     (null!==($class::TIMEOUT) && is_int($class::TIMEOUT) ) ? $class::TIMEOUT : 45
			  );
	  //set_time_limit($timeout);
	  set_time_limit(max($timeout, max(intval(ini_get('max_execution_time')), 45)));
  }
	
  public function withTimeout(int $time_limit = null){
	   $class = \get_class($this);
	  $timeout = (is_int($time_limit))
		  ? $time_limit
		  : (
			     (null!==($class::TIMEOUT) && is_int($class::TIMEOUT) ) ? $class::TIMEOUT : 45
			  );
	//  set_time_limit($timeout);
	   set_time_limit(max($timeout, max(intval(ini_get('max_execution_time')), 45)));
	  return $this;
  }


}
	

}
