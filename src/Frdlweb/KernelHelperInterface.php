<?php
namespace Frdlweb;

use Webfan\Webfat\App\Kernel;

 
use Frdlweb\KernelHelperInterface;
use Frdlweb\AdvancedWebAppInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Frdlweb\WebAppInterface;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use League\Route\Route;

/*
Implementation example/default: https://webfan.de/install/?source=Webfan\Webfat\App\KernelHelper
*/
interface KernelHelperInterface 
{
  
	const PROTECTED_SCHEMES = [
		'tcp',
		'file',
		'http',
		'https',
		'ws',
		'php',
		'tmp',
		'phar',
		'zip',
        'ftp',
        'sftp',
        'compress.bzip2',
        'compress.zlib',

	];
	
	public function out(array | string $str, string | bool $eol = true);
	public function route(string $name, array $params = [], bool $getPath = true) : string | bool | Route;
  	public function getModuleInfoById(string $id, string $group = Kernel::MODULES_INFO_CONTAINER_ID);
	public function getModuleInfoByName(string $name, string $group = Kernel::MODULES_INFO_CONTAINER_ID);
	public function findIn(string $column, string $search_value, string $group = '');
	public function group(string $idPrefix = '', bool $resolve = false, bool $asTree = false, bool $asValues = true);
        public function end() :void;
	public function isReservedScheme(string $protocol) : bool;
	public function isMounted(string $protocol) : bool;
	public function mountDir(string $protocol, string | \callable | \Closure $dir, bool $firstUnmount = false);
	//public function getKernel(): WebAppInterface;
	public function dotty(string $str) : string;
	public function loadFromUrlForCache($url , 
										\closure | \callable $filter = null, 
										\closure | \callable $filterSaveToFile = null, 
										string $ext = 'txt',
										int $holdBreakDuration = 60,
										string $accept =null,
										string $Authorization = null,
									   string $userAgent = null);	
	public function getFormFromRequest(string $message = '', ServerRequestInterface $request = null,
									   bool $autosubmit = true, $delay = 0): string;
	public function getResponseHeader($header, $response = null) : string | bool;
}
