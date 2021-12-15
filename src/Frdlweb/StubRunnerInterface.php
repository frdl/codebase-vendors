<?php 

namespace frdlweb;

interface StubRunnerInterface
 { 
 	public function loginRootUser($username = null, $password = null) : bool;		
	public function isRootUser() : bool;
	public function getStubVM() : StubHelperInterface;	
	public function getStub() : StubItemInterface;		
	public function __invoke() :?StubHelperInterface;	
	public function getInvoker();	
	public function getShield();	
	public function autoloading() : void;
}
