<?php
namespace Frdlweb\Contract\Webfantized;
use Exception;
	
	interface TypeAssertion {
               public function __construct(mixed $payload);
               public function validate():bool;
               public function normalize();
               public function assert(bool $throw=true):bool|Exception;
               public function type():string;
	}
	
	
