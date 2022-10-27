<?php 


namespace Webfan;


use Exception;

class CommonJavascript
{
    public const CREDITS = "/*\n * This file is part of the CommonJS for PHP library.\n *\n * (c) Olivier Philippon <https://github.com/olivierphi/CommonJSForPHP>\n *\n * For the full copyright and license information, please view the LICENSE\n * file that was distributed with this source code.\n */\n/** \n* edited by Frdlweb! \Webfan\CommonJavascript::temporaryFile\n**/";

    protected static $instances = [];

    public static function temporaryFile(string $content, string $name = null, int $expires = -1, $tmpPath = null)
    {
        if(null === $tmpPath){
          $tmpPath =\sys_get_temp_dir();
        }
			
		$hash = sha1($content);
        if(null === $name){
          //  $name = 'tmp-php-script-'.sha1($content).'.'.strlen($content).'.'.mt_rand(1,100000).'.php';
             $name = 'tmp-php-script-'.strtolower('CommonJavascript').'.'.$hash.'.'.strlen($content).'.php';
        }

        $file =rtrim($tmpPath, \DIRECTORY_SEPARATOR) .
            \DIRECTORY_SEPARATOR .
			substr($hash, 0, 4).
            \DIRECTORY_SEPARATOR .
			substr($hash, -4).
            \DIRECTORY_SEPARATOR .
			substr($hash, 5, strlen($hash)).
            \DIRECTORY_SEPARATOR .
            ltrim($name, \DIRECTORY_SEPARATOR);

        if(!file_exists($file) || ($expires>0 && filemtime($file) < time()-$expires) ){						
			if(!is_dir(dirname($file))){
					mkdir(dirname($file), 0775, true);
			}
            file_put_contents($file, $content);
        }

        if($expires===0){
           register_shutdown_function(function() use($file) {
              if(file_exists($file)){
				  unlink($file);
			  }
           });
        }

        return $file;
    }

    public static function getInstance($instanceName = 'default', array $config = [], array $plugins = [])
    {
        if (!isset(self::$instances[$instanceName])) {
            self::$instances[$instanceName] = self::getRequireJs($config, $plugins);
        }

        return self::$instances[$instanceName];
    }

    public static function getRequireJs(array $config = [], array $plugins = [])
    {
        return call_user_func_array(function(array $conf = [], array $plugs = []){

        $_definitionsRegistry = array();
        $_modulesRegistry = array();

        if(!isset($conf['basePath']) ){
          $conf['basePath'] = [getcwd()];
        }elseif(isset($conf['basePath']) && !is_array($conf['basePath'])){
           $conf['basePath'] = [$conf['basePath']];
        }

        $config = array_merge(array(
        // Default config
        'tmpPath' => \sys_get_temp_dir(),
           // 'basePath' => __DIR__,
        'basePath' =>array_merge($conf['basePath'], [
            getcwd().\DIRECTORY_SEPARATOR.'modules',
            getenv('FRDL_WORKSPACE').\DIRECTORY_SEPARATOR.'modules',
            'https://webfan.de/install/modules',
            'https://webfan.de/install/stable',
            'https://webfan.de/install/latest',
            getcwd(),
        ]),
        'modulesExt' => '.php',
        'folderAsModuleFileName' => 'index.php',
        'packageInfoFileName' => 'package.php',
           //   'autoNamespacing' => false,
        'autoNamespacing' => true,
        'autoNamespacingCacheExpires' => 24*60*60,
		'validators' => [],
        ), $conf);
			
		$config['basePath']	= array_merge($config['basePath'], [
            getcwd().\DIRECTORY_SEPARATOR.'modules',
            'https://webfan.de/install/modules',
            getenv('FRDL_WORKSPACE').\DIRECTORY_SEPARATOR.'modules',
            getcwd(),
        ]);
			
		$config['basePath'] = array_unique($config['basePath']);	
			
        $plugins =  array_merge(array(
        // Default plugins
        'json' => __DIR__ . '/plugins/commonsjs-plugin.json.php',
        'yaml' => __DIR__ . '/plugins/commonsjs-plugin.yaml.php',
        ), $plugs);

        if(!file_exists($plugins['json'])){
         if(!is_dir(dirname($plugins['json']))){
           mkdir(dirname($plugins['json']), 0755, true);
         }
          file_put_contents($plugins['json'], '<?php return json_decode(file_get_contents($resourcePath), true);');
        }

        if(!file_exists($plugins['yaml'])){
         if(!is_dir(dirname($plugins['yaml']))){
           mkdir(dirname($plugins['yaml']), 0755, true);
         }
          file_put_contents($plugins['yaml'],
                        '<?php $yamlParser = new \Symfony\Component\Yaml\Parser(); '
                        .' return $yamlParser->parse(file_get_contents($resourcePath));');
        }


        $_currentResolvedModuleDir = null;

        $_getResourceFullPath = function ($modulePath, $fileExtToAdd = '') use (&$config, &$_searchResource, &$_currentResolvedModuleDir)
        {
        static $absolutePathsResolutionsCache = array();

        $isRelativePath = ('./' === substr($modulePath, 0, 2) || '../' === substr($modulePath, 0, 3));

        $pathCacheId = $modulePath . $fileExtToAdd;
        if (!$isRelativePath && isset($absolutePathsResolutionsCache[$pathCacheId])) {

            return $absolutePathsResolutionsCache[$pathCacheId];
        }

        $basePaths = is_array($config['basePath']) ? $config['basePath'] : array($config['basePath']);

        if (null === $_currentResolvedModuleDir) {
            //defaults to $config['basePath'][0] if we are not already in a Module context
            $_currentResolvedModuleDir = $basePaths[0];
        }

        // Relative or absolute path?
        if ($isRelativePath) {
            // Relative path
            $fullModulePath = $_currentResolvedModuleDir . \DIRECTORY_SEPARATOR . ltrim($modulePath,  \DIRECTORY_SEPARATOR);
            $resolvedModulePath = $_searchResource($fullModulePath, $fileExtToAdd);
        } else {
            // Absolute path search from $basePaths
            foreach ($basePaths as $currentBasePath) {
                $fullModulePath = $currentBasePath . \DIRECTORY_SEPARATOR . ltrim($modulePath,  \DIRECTORY_SEPARATOR);
                $resolvedModulePath = $_searchResource($fullModulePath, $fileExtToAdd);
                 if (is_string($resolvedModulePath) && !empty($resolvedModulePath)) {
                    break;
                }
            }
        }

        $absolutePathsResolutionsCache[$pathCacheId] = $resolvedModulePath;
         //  $resolvedModulePath = str_replace('//', '/', $resolvedModulePath);
        return $resolvedModulePath;//can be null if no matching module path has been found
        };
			
			
    $_resource_exists = function($source)
    {
        if('http://'!==substr($source, 0, strlen('http://'))
           && 'https://'!==substr($source, 0, strlen('https://'))
           && is_file($source) && file_exists($source) && is_readable($source)){
        return true;
        }

        $options = [
        'https' => [
           'method'  => 'HEAD',
            'ignore_errors' => true,

           ]
        ];
        $context  = stream_context_create($options);
        $res = @file_get_contents($source, false, $context);
        return false !== $res;
    };
			
			
        $_moduleExists = function ($modulePath) use (&$_getResourceFullPath, &$config)
        {
        return (boolean) $_getResourceFullPath($modulePath, $config['modulesExt']);
        };

        $_searchResource = function ($searchedResourceFullPath, $fileExtToAdd = '') use (&$config, $_resource_exists)
        {
        static $resolutionsCache = array();

        $moduleCacheId = $searchedResourceFullPath . '|' . $fileExtToAdd;
        if (isset($resolutionsCache[$moduleCacheId])) {

            return $resolutionsCache[$moduleCacheId];
        }

        $resolvedModulePath = null;
        if (is_file($searchedResourceFullPath . $fileExtToAdd)) {
            // This is a regular "file Module" ; we just add the file extension
            $resolvedModulePath = $searchedResourceFullPath . $fileExtToAdd;
        } elseif (is_dir($searchedResourceFullPath)) {
            $relativeFile = $config['folderAsModuleFileName'];
            $packageInfoFile = $searchedResourceFullPath . \DIRECTORY_SEPARATOR . $config['packageInfoFileName'];
            if(file_exists($packageInfoFile)){
                $package = require $packageInfoFile;

            }
            if(isset($package['main'])){
              $relativeFile = $package['main'];
            }
              $relativeFile = $relativeFile.('.php' !== substr(strlen($relativeFile) - strlen('.php'), strlen($relativeFile))
                                        ? '.php'
                                        : '');
            $directoryModulePath = $searchedResourceFullPath . \DIRECTORY_SEPARATOR . $relativeFile;
            if (file_exists($directoryModulePath)) {
                // Yeah! This is a "folder as Module"
                $resolvedModulePath = $directoryModulePath;
            }
        }elseif($_resource_exists($searchedResourceFullPath . $fileExtToAdd)){
            // This is a regular "file Module" ; we just add the file extension
            $resolvedModulePath = $searchedResourceFullPath . $fileExtToAdd;
        }

        if (null !== $resolvedModulePath
            && 'https:'!=substr($resolvedModulePath,0,strlen('https:'))
            && 'http:'!=substr($resolvedModulePath,0,strlen('http:'))) {
            $resolvedModulePath = str_replace('/', DIRECTORY_SEPARATOR, $resolvedModulePath);
            $resolvedModulePath = realpath($resolvedModulePath);
        }

        $resolutionsCache[$moduleCacheId] = $resolvedModulePath;

        return $resolvedModulePath;
        };

        $_triggerModule = function ($moduleFilePath) use (&$config, &$require, &$define, &$_currentResolvedModuleDir, &$_getResourceFullPath, &$_moduleExists)
        {
        // Env setup...
        $module = array();
        $module['id'] = str_replace($config['basePath'], '', $moduleFilePath);//can handle string or array "$config['basePath']" :-)
        $module['id'] = str_replace(
            array(\DIRECTORY_SEPARATOR, $config['modulesExt']),
            array('/', ''),
            $module['id']
        );
        $module['uri'] = $moduleFilePath;
        $module['resolve'] = function ($modulePath) use ($_getResourceFullPath, $config)
        {
            return $_getResourceFullPath($modulePath, $config['modulesExt']);
        };
        $module['moduleExists'] = function ($modulePath) use ($_moduleExists)
        {
            return $_moduleExists($modulePath);
        };
        $exports = array();

        if ($config['autoNamespacing']
            || 'https:'===substr($moduleFilePath,0,strlen('https:'))
            || 'http:'===substr($moduleFilePath,0,strlen('http:'))
           ) {
            $autoNamespacingCacheExpires = $config['autoNamespacingCacheExpires'];
            $tmpPath = $config['tmpPath'];
			$validators = $config['validators'];
            $moduleTrigger = function () use ($moduleFilePath, &$require, &$define, &$module, &$exports,
                                              $autoNamespacingCacheExpires, $tmpPath, $validators)
            {
				
				if(empty($moduleFilePath)){
				  return null;	
				}
          // Yes, you're right : I probably deserve death for this "eval()" usage...
          // But I have not been able to find another way of using properly isolated classes in this CommonJS Modules PHP implementation :-)
          // 1) Let's create a unique namespace, based on the Module ID and prefixed with "CommonJS\Module"
				$hash = sha1($moduleFilePath);
				
                $cachePathFile = $tmpPath.\DIRECTORY_SEPARATOR.'common-js-php-madness-uricache'     
					.\DIRECTORY_SEPARATOR .		
					substr($hash, 0, 2).     
                    \DIRECTORY_SEPARATOR.						
					strlen($moduleFilePath).     
					\DIRECTORY_SEPARATOR .		
					substr($hash, 0, 4).          
					\DIRECTORY_SEPARATOR .			
					substr($hash, -4).               
					\DIRECTORY_SEPARATOR .		
					substr($hash, 5, strlen($hash)).	
					\DIRECTORY_SEPARATOR.'path-'.strlen($moduleFilePath).'-'.sha1($moduleFilePath).'.php';
					 //  .'-'.'path-'.strlen($moduleFilePath).'-'.sha1($moduleFilePath).'.php';

				
				
				
                if(file_exists($cachePathFile)){
                  $cacheInfo = require $cachePathFile;
                  if(($autoNamespacingCacheExpires < 0 ||  $cacheInfo['time'] > time()-$autoNamespacingCacheExpires)
                     && isset($cacheInfo['file']) && file_exists($cacheInfo['file']) ){
                      return include $cacheInfo['file'];
                  }
                }
                $moduleDynamicNamespace = 'Webfan\CommonJS\Module' . str_replace('/', '\\', $module['id']);
                $moduleDynamicNamespace = preg_replace('|[\s-]|i', '_', $moduleDynamicNamespace);
			    $moduleDynamicNamespace = preg_replace("/[^A-Za-z0-9\_\\\]/", '', $moduleDynamicNamespace);
                // 2) The PHP Module file content is read...
                $moduleFileContent = file_get_contents($moduleFilePath);
	
				foreach($validators as $middleware){	
					//if(is_callable($middleware[0]) && true !== call_user_func_array($middleware[0], [$moduleFilePath]) ){		
						
					if(( \is_callable($middleware[0])
						|| ('object' === gettype($middleware[0]) && $middleware[0] instanceof \Closure) ) 
					   		   && true !== \call_user_func_array($middleware[0], [$moduleFilePath]) ){								
						continue;			
					}elseif(is_string($middleware[0]) && !preg_match($middleware[0], $moduleFilePath)){		
						continue;			
					}
					
					$moduleFileContent = call_user_func_array($middleware[1], [$moduleFileContent]);		
					if(!is_string($moduleFileContent)){		
						 throw new Exception('ERROR: Validation failed (from path: '.$moduleFilePath.')!');
					}	
				}				
				
                // 3) ...and we add a dynamic namespace before it
            /* $moduleFileContent = 'namespace '.$moduleDynamicNamespace.'; ?>'.$moduleFileContent; */
                   $moduleFileContent = '<?php namespace '.$moduleDynamicNamespace.'; '.trim($moduleFileContent, '<>?php ');

                  // 4) Now we can actually trigger this PHP Module code, properly isolated in a unique namespace!
               // eval($moduleFileContent);
                $tempFilename = \Webfan\CommonJavascript::temporaryFile($moduleFileContent, null, $autoNamespacingCacheExpires, $tmpPath);
                $exp = var_export([
                    'file'=>$tempFilename,
                    'time'=>time(),
                    ],true);
                $phpCode = "<?php\n return $exp;";

				if(!is_dir(dirname($cachePathFile))){
					mkdir(dirname($cachePathFile), 0775, true);
				}
                file_put_contents($cachePathFile, $phpCode);
                return include $tempFilename;
            };

        } else {

            $moduleTrigger = function () use ($moduleFilePath, &$require, &$define, &$module, &$exports)
            {
                return include $moduleFilePath;
            };

        }


        // Go!
        $previousResolvedModuleDir = $_currentResolvedModuleDir;//current dir backup...
        $_currentResolvedModuleDir = dirname($moduleFilePath);
		//	print_r($previousResolvedModuleDir);die();
        call_user_func($moduleTrigger);
        $_currentResolvedModuleDir = $previousResolvedModuleDir;//..and restore!

        // Result analysis
        if (isset($module['exports'])) {

            return $module['exports'];
        } else {

            return $exports;
        }
        };

        $_triggerDefine = function ($definitionPath) use (&$_definitionsRegistry, &$require)
        {
        // Env setup...
        $module = array();
        $exports = array();
        $defineArgs = array(&$require, &$exports, &$module);

        // Go!
        $definitionResult = call_user_func_array($_definitionsRegistry[$definitionPath], $defineArgs);

        // Result analysis
        if (null === $definitionResult) {
            if (isset($module['exports'])) {
                $definitionResult = $module['exports'];
            } else if (sizeof($exports) > 0) {
                $definitionResult = $exports;
            }
        }

        return $definitionResult;
        };

        $_triggerPlugin = function ($extensionName, $resourcePath) use (&$plugins, &$require, &$_getResourceFullPath)
        {
        static $pluginsResolutionsCache = array();

        $extensionFilePath = $plugins[$extensionName];
        $resourcePath = $_getResourceFullPath($resourcePath);

        $pluginCacheId = $extensionFilePath . '|' . $resourcePath;
        if (isset($pluginsResolutionsCache[$pluginCacheId])) {

            return $pluginsResolutionsCache[$pluginCacheId];
        }

        $extensionTrigger = function () use ($extensionFilePath, &$require, $resourcePath)
        {
            return require $extensionFilePath;
        };
        $extensionResult = call_user_func($extensionTrigger);

        $pluginsResolutionsCache[$pluginCacheId] = $extensionResult;

        return $extensionResult;
        };

        /**
         * @param string $definitionPath
         * @param callable $moduleDefinition
         * @public
         */
        $define = function ($definitionPath, \Closure $moduleDefinition) use (&$_definitionsRegistry, &$_modulesRegistry)
        {
        if (isset($_modulesRegistry[$definitionPath])) {
            unset($_modulesRegistry[$definitionPath]);//clear previous defined module result cache
        }

        $_definitionsRegistry[$definitionPath] = $moduleDefinition;
        };

        /**
         * @param string $modulePath
         * @public
         * @return mixed
         * @throw \Exception
         */
        $require = function ($modulePath) use (&$config, &$plugins, &$_definitionsRegistry, &$_modulesRegistry,
        &$_triggerModule, &$_getResourceFullPath, &$_triggerDefine, &$_triggerPlugin, &$_currentResolvedModuleDir)
        {
        // "define()"-ed Module
        if (isset($_definitionsRegistry[$modulePath])) {

            if (isset($_modulesRegistry[$modulePath])) {

                return $_modulesRegistry[$modulePath];
            } else {
                // First "define()" module definition resolution: after that, it will be resolved with the modules registry
                $moduleDefinitionResult = $_triggerDefine($modulePath);
                $_modulesRegistry[$modulePath] = $moduleDefinitionResult;

                return $moduleDefinitionResult;
            }

        }

        // Do we use a plugin on this Module path (i.e. do we have a [prefix]![resource path] pattern) ?
        if (preg_match('|^(\w+)!([a-z0-9/_.-]+)$|i', $modulePath, $matches)) {
            $pluginName = $matches[1];
            $resourcePath = $matches[2];
            if (!isset($plugins[$pluginName])) {
                throw new Exception('Unregistered plugin  "'.$pluginName.'" (resource path: "'.$resourcePath.'")!');
            }
            $moduleDefinitionResult = $_triggerPlugin($pluginName, $resourcePath);

            return $moduleDefinitionResult;
        }

        // Regular Module resolution
        $fullModulePath = $_getResourceFullPath($modulePath, $config['modulesExt']);
        if (null === $fullModulePath) {
            throw new Exception('Unresolvable module "'.$modulePath.'" (from path: '.$_currentResolvedModuleDir.')!');
        }

        if (isset($_modulesRegistry[$fullModulePath])) {
            return $_modulesRegistry[$fullModulePath];//previously resolved Module
        }

        // Okay, let's trigger this Module!
        $moduleResolution = $_triggerModule($fullModulePath);
        $_modulesRegistry[$fullModulePath] = $moduleResolution;//this Module won't have to be resolved again

        return $moduleResolution;
        };

        return array(
        'define' => $define,
        'require' => $require,
        'config' => &$config,
        'plugins' => &$plugins,
        );
        }, [$config, $plugins]);
    }
}
