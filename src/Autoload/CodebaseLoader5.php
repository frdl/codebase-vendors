<?php

namespace Webfan\Autoload{
use Frdlweb\Contract\Autoload\LoaderInterface;
use Frdlweb\Contract\Autoload\ClassLoaderInterface;
use Frdlweb\Contract\Autoload\Psr4GeneratorInterface;
use Frdlweb\Contract\Autoload\ClassmapGeneratorInterface;
use Frdlweb\Contract\Autoload\GeneratorInterface;
use Frdlweb\Contract\Autoload\ResolverInterface;
use Nette;
use SplFileInfo;
use Webfan\Traits\WithContextDirectories as DirectoriesTrait;
use Webfan\Traits\WithTimeout;
	
class CodebaseLoader5 implements LoaderInterface, ClassLoaderInterface, ClassmapGeneratorInterface, ResolverInterface
{
	use Nette\SmartObject, DirectoriesTrait, WithTimeout;
    const VERSION = 'v2';
	const TIMEOUT = 180;
	protected const RETRY_LIMIT = 3;

	protected $ambiguous = [];
	/** @var string[] */
	public array $ignoreDirs = ['.*', '*.old', '*.bak', '*.tmp', 'temp'];

	/** @var string[] */
	public array $acceptFiles = ['*.php'];

	protected bool $autoRebuild = true;

	protected bool $reportParseErrors = true;

	/** @var string[] */
	protected array $scanPaths = [];

	/** @var string[] */
	protected array $excludeDirs = [];

	/** @var array<string, array{string, int}>  class => [file, time] */
	protected array $classes = [];
	protected array $classMaps = [];
	protected array $dubs = [];
	protected array $parseErrors = [];

	protected bool $cacheLoaded = false;

	protected bool $refreshed = false;

	/** @var array<string, int>  class => counter */
	protected array $missingClasses = [];

	/** @var array<string, int>  file => mtime */
	protected array $emptyFiles = [];

	protected ?string $tempDirectory = null;

	protected bool $needSave = false;
    protected array $options = [
		'target' =>[
			
		],
	];

	public function __construct(array $options = null)
	{
		if (!extension_loaded('tokenizer')) {
			throw new \Nette\NotSupportedException('PHP extension Tokenizer is not loaded.');
		}
		
		$this->options['target'] = [
			'php' => \PHP_VERSION,
			'extensions'=>\get_loaded_extensions(),
		];
		
		$this->options = array_merge_recursive ($this->options, $options ?? [
		
		]);
		
		sort($this->options['target']['extensions']);
		ksort($this->options['target']);
		ksort($this->options);
		
		$this->setTempDirectory($this->tempDir('classmaps-meta-cache', 'local'));
	}

 public function sign($cleartext,$private_key, $sep = 'X19oYWx0X2NvbXBpbGVyKCk7')
    {
      $msg_hash = sha1($cleartext);
      \openssl_private_encrypt($msg_hash, $sig, $private_key);
       $signed_data = $cleartext .base64_decode($sep). "----SIGNATURE:----" . $sig;
      return $signed_data;
   }

 public function verify($my_signed_data,$public_key, $sep = 'X19oYWx0X2NvbXBpbGVyKCk7')
   {
    list($plain_data,$sigdata) = explode(base64_decode($sep), $my_signed_data, 2);
    list($nullVoid,$old_sig) = explode("----SIGNATURE:----", $sigdata, 2);
    if(empty($old_sig)){
      return new \Exception("ERROR -- unsigned data");
    }
    \openssl_public_decrypt($old_sig, $decrypted_sig, $public_key);
    $data_hash = sha1($plain_data);
    if($decrypted_sig === $data_hash && strlen($data_hash)>0){
        return $plain_data;
	}else{
        return new \Exception("ERROR -- untrusted signature");
	}
  }
	
	public function __destruct()
	{
		if ($this->needSave) {
			$this->saveCache();			 
	     	$this->saveClassMaps();
		}
	}


	/**
	 * Register autoloader.
	 */
	public function register(bool $prepend = false)//: static
	{
		\spl_autoload_register([$this, 'Autoload'], true, $prepend);
		//return $this;
	}

	public function tryLoad(string $type) 
	{
		return $this->Autoload($type);
	}
	/**
	 * Handles autoloading of classes, interfaces or traits.
	 */
	public function Autoload(string $type):bool|string 
	{
		$file=$this->file($type);

		if ($file) {
			(static function ($file) { require $file; })($file);
			return $file;
		}else{
		 return false;	
		}
	}
	public function url(string $type):bool|string
	{
		return false;
	}
	public function resolve(string $type):bool|string
	{
		return $this->file($type);
	}
	public function file(string $type) :bool|string
	{
		$this->loadCache();

		$missing = $this->missingClasses[$type] ?? null;
		if ($missing >= self::RETRY_LIMIT) {
			return false;
		}

		[$file, $mtime] = $this->classes[$type] ?? null;

		if ($this->autoRebuild) {
			if (!$this->refreshed) {
				if (!$file || !is_file($file)) {
					$this->refreshClasses();
					[$file] = $this->classes[$type] ?? null;
					$this->needSave = true;

				} elseif (filemtime($file) !== $mtime) {
					$this->updateFile($file);
					[$file] = $this->classes[$type] ?? null;
					$this->needSave = true;
				}
			}

			if (!$file || !is_file($file)) {
				$this->missingClasses[$type] = ++$missing;
				$this->needSave = $this->needSave || $file || ($missing <= self::RETRY_LIMIT);
				unset($this->classes[$type]);
				$file = null;
			}
		}

		return (is_string($file)) ? $file : false;
	}

	/**
	 * Add path or paths to list.
	 */
	public function addDirectory(string $dir)
	{
		return $this->addPaths($dir);
	}

	public function addPaths()
	{
		$paths=func_get_args();
		$this->scanPaths = array_merge($this->scanPaths, $paths);
		return $this;
	}
	
					
	public function reportParseErrors(bool $on = true): self
	{
		$this->reportParseErrors = $on;
		return $this;
	}


	/**
	 * Excludes path or paths from list.
	 */
	public function excludeDirectory(): self
	{
		$paths=func_get_args();
		$this->excludeDirs = array_merge($this->excludeDirs, $paths);
		return $this;
	}


	/**
	 * @return array<string, string>  class => filename
	 */
	public function getIndexedClasses(): array
	{
		$this->loadCache();
		$res = [];
		foreach ($this->classes as $class => [$file]) {
			$res[$class] = $file;
		}
		return $res;
	}
	public function getClassMaps(): array
	{
		return require $this->getCacheFile('_classmaps');
	}

	/**
	 * Rebuilds class list cache.
	 */
	public function rebuild(): void
	{
		$this->cacheLoaded = true;
		$this->classes = $this->missingClasses = $this->emptyFiles = [];
		$this->refreshClasses();
		if ($this->tempDirectory) {
			$this->saveCache();
		    $this->saveClassMaps();
		}
	}


	/**
	 * Refreshes class list cache.
	 */
	public function refresh(): void
	{
		$this->loadCache();
		if (!$this->refreshed) {
			$this->refreshClasses();
			$this->saveCache();
		    $this->saveClassMaps();
		}
	}


	/**
	 * Refreshes $this->classes & $this->emptyFiles.
	 */
	protected function refreshClasses(): void
	{
		$this->refreshed = true; // prevents calling refreshClasses() or updateFile() in tryLoad()
		$files = $this->emptyFiles;
		$classes = [];
		foreach ($this->classes as $class => [$file, $mtime]) {
			$files[$file] = $mtime;
			$classes[$file][] = $class;
		}
		$this->classes = $this->emptyFiles = [];

		foreach ($this->scanPaths as $path) {
			$iterator = is_file($path)
				? [new SplFileInfo($path)]
				: $this->createFileIterator($path);

			foreach ($iterator as $fileInfo) {
				$mtime = $fileInfo->getMTime();
				$file = $fileInfo->getPathname();
				$foundClasses = isset($files[$file]) && $files[$file] === $mtime
					? ($classes[$file] ?? [])
					: $this->scanPhp($file);

				if (!$foundClasses) {
					$this->emptyFiles[$file] = $mtime;
				}

				$files[$file] = $mtime;
				$classes[$file] = []; // prevents the error when adding the same file twice

				$info = [$file, $mtime];

				 
			$hash=\sha1_file($file);
		//	if(!isset( $this->dubs[$hash])){
		//		$this->dubs[$hash]=[];
		//	}
			
          //    $this->dubs[$hash][$file] = $foundClasses;	
				foreach ($foundClasses as $class) {
									
					
					if(!isset($this->classMaps[$class])){
						$this->classMaps[$class] = [];
					}		

				   		             
					$this->classMaps[$class][$hash] =(
						    isset($this->classMaps[$class][$hash])
						  && filemtime($file) <  filemtime($this->classMaps[$class][$hash][0]) 
					  ) 
						  ?  $this->classMaps[$class][$hash]
						  :  $info;


					if (isset($this->classes[$class])) {
						//throw new Nette\InvalidStateException(
						/*
						trigger_error(
							sprintf(
							'Ambiguous class %s resolution; defined in %s and in %s.',
							$class,
							$this->classes[$class][0],
							$file
						)
						, \E_USER_NOTICE);
						*/
                     //);
						if(!isset($this->ambiguous[$class] )){
						  $this->ambiguous[$class]  = [];	
						}
						     $this->ambiguous[$class][$file] = $info;
						
						
							if(filemtime($file) > filemtime($this->classes[$class][0])){
								$this->classes[$class] = $info;
							}
							
					}else{
                       $this->classes[$class] = $info;
					}
					unset($this->missingClasses[$class]);
				}
			}
		}
	}


	
	/**
	 * Creates an iterator scaning directory for PHP files and subdirectories.
	 * @throws Nette\IOException if path is not found
	 */
	protected function createFileIterator(string $dir): \Nette\Utils\Finder
	{
		if (!is_dir($dir)) {
			throw new \Nette\IOException(sprintf("File or directory '%s' not found.", $dir));
		}
		$dir = realpath($dir) ?: $dir; // realpath does not work in phar

		$normalizer = fn($path) => str_replace('\\', '/', $path);
		$disallow = [];
		foreach (array_merge($this->ignoreDirs, $this->excludeDirs) as $item) {
			if ($item = realpath($item)) {
				$disallow[$normalizer($item)] = true;
			}
		}
		$filter = fn(\SplFileInfo $file) => $file->getRealPath() === false
			|| !isset($disallow[$normalizer($file->getRealPath())]);

		$iterator = \Nette\Utils\Finder::findFiles($this->acceptFiles)
			->filter($filter)
			->from($dir)
			->exclude($this->ignoreDirs)
			->filter($filter);

		$filter(new \SplFileInfo($dir));
		return $iterator;
	}


	protected function updateFile(string $file): void
	{
		
		$this->withTimeout();
		
		foreach ($this->classes as $class => [$prevFile]) {
			if ($file === $prevFile) {
				unset($this->classes[$class]);
			}
		}

		$foundClasses = is_file($file) ? $this->scanPhp($file) : [];
		
			$hash=\sha1_file($file);
			//if(!isset( $this->dubs[$hash])){
			//	$this->dubs[$hash]=[];
		//	}
			
           //   $this->dubs[$hash][$file] = $foundClasses;	
				  
		foreach ($foundClasses as $class) {
			[$prevFile, $prevMtime] = $this->classes[$class] ?? null;

			if (isset($prevFile) && @filemtime($prevFile) !== $prevMtime) { // @ file may not exists
				$this->updateFile($prevFile);
				[$prevFile] = $this->classes[$class] ?? null;
			}
			
			$info=[$file, filemtime($file)];
			
				    if(!isset($this->classMaps[$class])){
						$this->classMaps[$class] = [];
					}		
			
		              $this->classMaps[$class][$hash] =(
						    isset($this->classMaps[$class][$hash])
						  && filemtime($file) <  filemtime($this->classMaps[$class][$hash][0]) 
					  ) 
						  ?  $this->classMaps[$class][$hash]
						  :  $info;
						  
           

			if (isset($prevFile)) {
				//throw new Nette\InvalidStateException(sprintf(
				trigger_error(sprintf(
					'Ambiguous class %s resolution; defined in %s and in %s.',
					$class,
					$prevFile,
					$file
				)
					, \E_USER_NOTICE);
                //);
											
				if(filemtime($file) > filemtime($this->classes[$class][0])){							
					$this->classes[$class] = $info;						
				}
				
			   
			}else{
			   $this->classes[$class] = $info;
			}
		}
	}


	/**
	 * Searches classes, interfaces and traits in PHP file.
	 * @return string[]
	 */
	protected function scanPhp(string $file): array
	{
		/*
		if(\php_sapi_name()!=='cli'){
			set_time_limit(180);
		}


//(new \frdl\Lint\Php($cacheDirLint) ) ->lintString($codeWithStartTags)

		if(true !== \frdl\Lint\Php::lintFileStatic($file,false) ){
			trigger_error(sprintf('Parse error in %s', $file), \E_USER_WARNING);
			return [];
		}
*/	 		
		
		$this->withTimeout();
		
		$code = file_get_contents($file);
		$expected = false;
		$namespace = $name = '';
		$level = $minLevel = 0;
		$classes = [];

		try {
						$tokens = ( true ===  \class_exists(\PhpToken::class) && \method_exists(\PhpToken::class,'tokenize') )
				      ? \PhpToken::tokenize($code, \TOKEN_PARSE)
					  : $tokens = \token_get_all($code, \TOKEN_PARSE)
			;
		
		} catch (\ParseError $e) {
				$rp = new \ReflectionProperty($e, 'file');
				$rp->setAccessible(true);
				$rp->setValue($e, $file);
			if ($this->reportParseErrors) {
				throw $e;
			}else{
				$this->parseErrors[$file] = $rp;
			}
			$tokens = [];
		}

		foreach ($tokens as $token) {
				$token=(object)$token;
                $this->withTimeout();
			
			
			switch ($token->id) {
				case \T_COMMENT:
				case \T_DOC_COMMENT:
				case \T_WHITESPACE:
					continue 2;

				case \T_STRING:
				case \T_NAME_QUALIFIED:
					if ($expected) {
						$name .= $token->text;
					}
					continue 2;

					
									    
					//testing functions:
					case \T_FUNCTION: 	
					 //	die(__METHOD__.' '.__LINE__.' '.print_r($token,true));
					 //	trigger_error(__METHOD__.' '.__LINE__.' '.print_r($token,true), \E_USER_NOTICE); 
						$expected = $token->id;
						$name = '';
						continue 2;	
					
				case \T_NAMESPACE:
				case \T_CLASS:
				case \T_INTERFACE:
				case \T_TRAIT:
				case \PHP_VERSION_ID < 80100
					? \T_CLASS
					: \T_ENUM:
					$expected = $token->id;
					$name = '';
					continue 2;
				case \T_CURLY_OPEN:
				case \T_DOLLAR_OPEN_CURLY_BRACES:
					$level++;
			}

			if ($expected) {
				if ($expected === \T_NAMESPACE) {
					$namespace = $name ? $name . '\\' : '';
					$minLevel = $token->text === '{' ? 1 : 0;

				} elseif ($name && $level === $minLevel) {
					$classes[] = $namespace . $name;
				}
				$expected = null;
			}

			if ($token->text === '{') {
				$level++;
			} elseif ($token->text === '}') {
				$level--;
			}
		}
		return $classes;
	}


	/********************* caching ****************d*g**/


	/**
	 * Sets auto-refresh mode.
	 */
	public function setAutoRefresh(bool $on = true): self
	{
		$this->autoRebuild = $on;
		return $this;
	}

	

	/**
	 * Sets path to temporary directory.
	 */
	public function setTempDirectory(string $dir): self
	{
		\Nette\Utils\FileSystem::createDir($dir);
		$this->tempDirectory = $dir;
		return $this;
	}


	/**
	 * Loads class list from cache.
	 */
	protected function loadCache(): void
	{
		if ($this->cacheLoaded) {
			return;
		}
		$this->cacheLoaded = true;

		$file = $this->getCacheFile('_classes');

		// Solving atomicity to work everywhere is really pain in the ass.
		// 1) We want to do as little as possible IO calls on production and also directory and file can be not writable (#19)
		// so on Linux we include the file directly without shared lock, therefore, the file must be created atomically by renaming.
		// 2) On Windows file cannot be renamed-to while is open (ie by include() #11), so we have to acquire a lock.
		$lock = defined('PHP_WINDOWS_VERSION_BUILD')
			? $this->acquireLock("$file.lock", \LOCK_SH)
			: null;

		$data = @include $file; // @ file may not exist
		if (is_array($data)) {
			[$this->classes, $this->missingClasses, $this->emptyFiles] = $data;
			return;
		}

		if ($lock) {
			flock($lock, \LOCK_UN); // release shared lock so we can get exclusive
		}
		$lock = $this->acquireLock("$file.lock", \LOCK_EX);

		// while waiting for exclusive lock, someone might have already created the cache
		$data = @include $file; // @ file may not exist
		if (is_array($data)) {
			[$this->classes, $this->missingClasses, $this->emptyFiles] = $data;
			return;
		}

		$this->classes = $this->missingClasses = $this->emptyFiles = [];
		$this->refreshClasses();
		$this->saveCache($lock);
		$this->saveClassMaps($lock);
		// On Windows concurrent creation and deletion of a file can cause a 'permission denied' error,
		// therefore, we will not delete the lock file. Windows is really annoying.
	}


	/**
	 * Writes class list to cache.
	 * @param  resource  $lock
	 */
	protected function saveCache($lock = null): void
	{
		// we have to acquire a lock to be able safely rename file
		// on Linux: that another thread does not rename the same named file earlier
		// on Windows: that the file is not read by another thread
		$file = $this->getCacheFile('_classes');
		$lock = $lock ?: $this->acquireLock("$file.lock", LOCK_EX);
		ksort($this->classes);
		ksort($this->missingClasses);
		$code = "<?php\nreturn " . var_export([$this->classes, $this->missingClasses, $this->emptyFiles], true) . ";\n";

		if (file_put_contents("$file.tmp", $code) !== strlen($code) || !rename("$file.tmp", $file)) {
			@unlink("$file.tmp"); // @ file may not exist
			throw new \RuntimeException(sprintf("Unable to create '%s'.", $file));
		}
		if (\function_exists('opcache_invalidate')) {
			@opcache_invalidate($file, true); // @ can be restricted
		}
	}
	protected function saveClassMaps($lock = null): void
	{
		// we have to acquire a lock to be able safely rename file
		// on Linux: that another thread does not rename the same named file earlier
		// on Windows: that the file is not read by another thread
		$file = $this->getCacheFile('_classmaps');
		$lock = $lock ?: $this->acquireLock("$file.lock", \LOCK_EX);
		ksort($this->classMaps);
		$code = "<?php\nreturn " . var_export($this->classMaps, true) . ";\n";

		if (file_put_contents("$file.tmp", $code) !== strlen($code) || !rename("$file.tmp", $file)) {
			@unlink("$file.tmp"); // @ file may not exist
			throw new \RuntimeException(sprintf("Unable to create '%s'.", $file));
		}
		if (function_exists('opcache_invalidate')) {
			@opcache_invalidate($file, true); // @ can be restricted
		}
	}

	/** @return resource */
	protected function acquireLock(string $file, int $mode)
	{
		$handle = @fopen($file, 'w'); // @ is escalated to exception
		if (!$handle) {
			throw new \RuntimeException(sprintf("Unable to create file '%s'. %s", $file, \error_get_last()['message']));
		} elseif (!@flock($handle, $mode)) { // @ is escalated to exception
			throw new \RuntimeException(sprintf(
				"Unable to acquire %s lock on file '%s'. %s",
				$mode & \LOCK_EX ? 'exclusive' : 'shared',
				$file,
				\error_get_last()['message'],
			));
		}
		return $handle;
	}


	public function getCacheFile($name = '_classes'): string
	{
		if (!$this->tempDirectory) {
			throw new \LogicException('Set path to temporary directory using setTempDirectory().');
		}
		$file= $this->tempDirectory . '/' . $name .'.'. \sha1(serialize($this->getCacheKey())) . '.php';
		if(!is_dir(dirname($file))){
		 mkdir(dirname($file), 0777,true);	
		}
		   
		return $file;
	}


	protected function getCacheKey(): array
	{
		return [
			    $this->options,
			    \sha1_file(__FILE__), 
				__FILE__,
				\get_class($this), 
				$this->ignoreDirs,
				$this->acceptFiles, 
				$this->scanPaths, 
				$this->excludeDirs, 
				self::VERSION
			   ];
	}
}

}



