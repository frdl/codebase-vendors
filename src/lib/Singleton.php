<?php

namespace Webfan\Traits{

trait Singleton {

    protected static $__frdl__aInstance = array();


  //  private function __construct() {}

    public static function getInstance() {

       $args = func_get_args();

       $sClassName = get_called_class(); 

		$bag = \version_compare(\PHP_VERSION, '5.3.0', '>=') ? static::$__frdl__aInstance : self::$__frdl__aInstance;

       if( !isset( $bag[ $sClassName ] ) ) {
 		
		if( 
			isset($sClassName::$WEBFANTIZED_CLASS)
		&& is_array($sClassName::$WEBFANTIZED_CLASS) && isset($sClassName::$WEBFANTIZED_CLASS['onGetInstance'])
		&& is_callable([$oInstance,  $sClassName::$WEBFANTIZED_CLASS['onGetInstance']])){
          $bag[ $sClassName ]  = call_user_func_array([$oInstance,
		                                     $sClassName::$WEBFANTIZED_CLASS['onGetInstance']], 
											 $args);
        }else{
		     if(\version_compare(PHP_VERSION, '5.6.0', '>=')){
                 $bag[ $sClassName ]  = new $sClassName(...$args);
             } else {
                $reflect  = new \ReflectionClass($sClassName);
               $bag[ $sClassName ]  = $reflect->newInstanceArgs($args);
              }
		}
		
	}	
		
		$oInstance = &$bag[ $sClassName ];
       return $oInstance;
    }

   // final private function __clone() {}
}

}