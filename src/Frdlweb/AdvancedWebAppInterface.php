<?php
namespace Frdlweb;

use frdlweb\AppInterface;
use Eljam\CircuitBreaker\Breaker;
use Fuz\Component\SharedMemory\SharedMemory;

interface AdvancedWebAppInterface extends WebAppInterface
{
  
    public function getDir(string $type = null, $create = true) : string;
    public function getFile(string $path, string $type = null) : string;
    public function getStub(): ?StubHelperInterface;
    public function setStub(?StubHelperInterface $stubHelper = null) : AppInterface;
    public function getSources(string $userdir = null, $create = false) : array;
    public function getWebUriBase() : string | bool;
    public function getAppId() : string;
    public function setAppId(string $appId = '1.3.6.1.4.1.37553.8.1.8.8.1958965301') : ?AppInterface;

    public function onError($number = null, $message = null, $file = nulle, $line = null, $errcontext = null);
    public function exception_handler($exception);
  
    public function context(?array $context = null) : array;
    public function isSessionStarted() : bool;
    public function session_start(string $session_name = 'WEBFATSESSION');
    public function getResponseHeader($header, $response = null) : string | bool;
    public function hasRoute(string $name) : bool;
    public function getAppIntentDir(string $appOrScheme, string $intent = null, $create = false) : string;
    public function scheme(string $appOrScheme) : string;
    public function getEnvFlag() : string;
    public function &global();
    public function &getConnection(string $appOrScheme = null,  string $id, 
								  int $max_failure = 1, int $reset_timeout = 5, bool $ignore_exceptions = false,
									  array $exclude_exceptions = [],
									  array $allowed_exceptions = []);  
  public function hasConnection(string $id, string $appOrScheme = null) : bool;
  public function &getCircuitBreaker(string $appOrScheme = null, string $circuitId, 
									  bool | \callable | \Closure $onSuccess = null,
									  bool | \callable | \Closure $onFailure = null,
									  bool | \callable | \Closure $onOpen = null,
									  bool | \callable | \Closure $onClosed = null,
									  bool | \callable | \Closure $onHalfopen = null,
									   int $max_failure = 1,
									   int $reset_timeout = 5,
									   bool $ignore_exceptions = false, 
									   string $ext = 'txt',
									  array $exclude_exceptions = [],
									  array $allowed_exceptions = [],
									  bool $forceAddEventHandlers = false) : Breaker;
  public function &getShared(string $sharedId, string $ext = 'txt', string $appOrScheme = null) : SharedMemory;
  public function deleteShared(string $sharedId, string $appOrScheme);
  public function hasShared(string $sharedId, string $appOrScheme = null) : bool;
  public function hasContainer(): bool;
  public function terminate(int $timelimit = null);
  public function exec(string | array $args, bool $needResultCode = false);
  public function isCLI(): bool;
  public function devlog(string $message, $data = []);
  public function log(string $type, string $message, array $context = [], string $loggerName = 'default');
  public function isMethod(string $method, $classOrObject) : \stdclass;	
  
}
