<?php
namespace Frdlweb\Type\Is;
use Frdlweb\Contract\Webfantized\TypeAssertion;

class CallableDefinition implements TypeAssertion
{
	 protected $payload;
	 protected $info = null;
     public function __construct(mixed $payload){
		 $this->payload=$payload;
	 }
		   
	public function assert(bool $throw=true):bool|Exception{
		 try{
			$result = $this->validate();
		 }catch(Exception $e){
			 $result = $e;
		 }
		
		$res = true === $result ? $result : new Exception('Invalid type of '.basename(get_class($this)).' in '.__METHOD__.' '.__LINE__);
		 if(true!==$res && true === $throw){
			 throw $res;
		 }
		return $res;
	 }		
     public function validate():bool{
		 return $this->_typeInfo()->valid;
	 }		
     public function normalize(){
		 return $this->_typeInfo()->normalized;
	 }				
     public function type():string{
		 return $this->_typeInfo()->type;
	 }	
     protected function _typeInfo(){
       if(null===$this->info){
		 $result=new \stdclass;
		 
		 $result->normalized = null;
		 $result->type = $this->callableType($this->payload, true,$result->normalized);
		 $result->valid = (is_string($result->type ) && is_callable($result->normalized) )?true:false;
		 
		 $this->info = $result;
	   }
		 return $this->info;
	 }		
 protected function callableType($callable, $strict = true, \callable &$norm = null):bool|string {
  if (!is_callable($callable)) {
    switch (true) {
      case is_object($callable):
        $norm = $callable;
        return 'Closure' === get_class($callable) ? 'closure' : 'invocable';
      case is_string($callable):
        $m    = null;
        if (preg_match('~^(?<class>[a-z_][a-z0-9_]*)::(?<method>[a-z_][a-z0-9_]*)$~i', $callable, $m)) {
          list($left, $right) = [$m['class'], $m['method']];
          if (!$strict || (new \ReflectionMethod($left, $right))->isStatic()) {
            $norm = [$left, $right];
            return 'static';
          }
        } else {
          $norm = $callable;
          return 'function';
        }
        break;
      case is_array($callable):
        $m = null;
        if (preg_match('~^(:?(?<reference>self|parent)::)?(?<method>[a-z_][a-z0-9_]*)$~i', $callable[1], $m)) {
          if (is_string($callable[0])) {
            if ('parent' === strtolower($m['reference'])) {
              list($left, $right) = [get_parent_class($callable[0]), $m['method']];
            } else {
              list($left, $right) = [$callable[0], $m['method']];
            }
            if (!$strict || (new \ReflectionMethod($left, $right))->isStatic()) {
              $norm = [$left, $right];
              return 'static';
            }
          } else {
            if ('self' === strtolower($m['reference'])) {
              list($left, $right) = [$callable[0], $m['method']];
            } else {
              list($left, $right) = $callable;
            }
            if (!$strict || !(new \ReflectionMethod($left, $right))->isStatic()) {
              $norm = [$left, $right];
              return 'object';
            }
          }
        }
        break;
       }
        $norm = $callable;
        return 'unknown';
     }
      $norm = null;
      return false;
     }
}
