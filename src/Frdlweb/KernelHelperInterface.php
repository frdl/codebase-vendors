<?php
namespace Frdlweb;


use Frdlweb\AdvancedWebAppInterface;

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
  
  
	public function isReservedScheme(string $protocol) : bool;
	public function isMounted(string $protocol) : bool;
	public function mountDir(string $protocol, string $dir, bool $firstUnmount = false);
	public function &getKernel() : AdvancedWebAppInterface;
	public function dotty(string $str) : string;
	public function loadFromUrlForCache($url , 
										\closure | \callable $filter = null, 
										\closure | \callable $filterSaveToFile = null, 
										string $ext = 'txt',
										int $holdBreakDuration = 60,
										string $accept =null,
										string $Authorization = null,
									   string $userAgent = null);
	
	
}
