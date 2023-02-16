<?php

namespace frdl\Lint;

class Php
{
    protected $cacheDir = null;

    public function __construct($cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

    public function setCacheDir($cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
          return $this;
    }

    public function getCacheDir()
    {
        if((null!==$this->cacheDir && !empty($this->cacheDir)) && is_dir($this->cacheDir)){
        return $this->cacheDir;
         }

           if(!isset($_ENV['FRDL_HPS_CACHE_DIR']))$_ENV['FRDL_HPS_CACHE_DIR']=getenv('FRDL_HPS_CACHE_DIR');

          $this->cacheDir =
        (  isset($_ENV['FRDL_HPS_CACHE_DIR']) && !empty($_ENV['FRDL_HPS_CACHE_DIR']))
          ? $_ENV['FRDL_HPS_CACHE_DIR']
                   : \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . \get_current_user(). \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR ;

         $this->cacheDir = rtrim($this->cacheDir, '\\/'). \DIRECTORY_SEPARATOR.'lint';

         if(!is_dir($this->cacheDir)){
        mkdir($this->cacheDir, 0755, true);
         }


          return $this->cacheDir;
    }

    public function lintString($source)
    {
        $cachedir =  $this->getCacheDir();
         if(!is_writable($cachedir)){
        mkdir($cachedir, 0755, true);
         }
         $tmpfname = tempnam($cachedir, 'frdl_lint_php');
         if(empty($tmpfname))return false;
         file_put_contents($tmpfname, $source);
         $valid = $this->checkSyntax($tmpfname, false);
         unlink($tmpfname);
         return $valid;
    }

    public function lintFile($fileName, $checkIncludes = true)
    {
        return call_user_func_array([$this, 'checkSyntax'], [$fileName, $checkIncludes]);
    }

    public static function lintStringStatic($source)
    {
        $o = new self;
         $tmpfname = tempnam($o->getCacheDir(), 'frdl_lint_php');
         file_put_contents($tmpfname, $source);
         $valid = $o->checkSyntax($tmpfname, false);
         unlink($tmpfname);
         return $valid;
    }

    public static function lintFileStatic($fileName, $checkIncludes = true)
    {
        $o = new self;
         $o->setCacheDir($o->getCacheDir());
         return call_user_func_array([$o, 'checkSyntax'], [$fileName, $checkIncludes]);
    }

    public static function __callStatic($name, $arguments)
    {
        $o = new self;
         return call_user_func_array([$o, $name], $arguments);
    }

    public function checkSyntax($fileName, $checkIncludes = false)
    {
        if(!file_exists($fileName))
            throw new \Exception("Cannot read file ".$fileName);

        // Sort out the formatting of the filename
           $fileName = realpath($fileName);

        // Get the shell output from the syntax check command
        $output = shell_exec(sprintf('%s -l "%s"',  (new \Webfan\Helper\PhpBinFinder())->find(), $fileName));

        // Try to find the parse error text and chop it off
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

        // If the error text above was matched, throw an exception containing the syntax error
        if($count > 0)
            //throw new \Exception(trim($syntaxError));
            return 'Errors parsing '.print_r([$output, $count],true);

        // If we are going to check the files includes
        if($checkIncludes)
        {
            foreach($this->getIncludes($fileName) as $include)
            {
                // Check the syntax for each include
                $tCheck = $this->checkSyntax($include, $checkIncludes);
               if(true!==$tCheck){
                 return $tCheck;
               }
            }
        }

          return true;
    }

    public function getIncludes($fileName)
    {
        $includes = array();
        // Get the directory name of the file so we can prepend it to relative paths
        $dir = dirname($fileName);

        // Split the contents of $fileName about requires and includes
        // We need to slice off the first element since that is the text up to the first include/require
        $requireSplit = array_slice(preg_split('/require|include/i', file_get_contents($fileName)), 1);

        // For each match
        foreach($requireSplit as $string)
        {
            // Substring up to the end of the first line, i.e. the line that the require is on
            $string = substr($string, 0, strpos($string, ";"));

            // If the line contains a reference to a variable, then we cannot analyse it
            // so skip this iteration
            if(strpos($string, "$") !== false)
                continue;

            // Split the string about single and double quotes
            $quoteSplit = preg_split('/[\'"]/', $string);

            // The value of the include is the second element of the array
            // Putting this in an if statement enforces the presence of '' or "" somewhere in the include
            // includes with any kind of run-time variable in have been excluded earlier
            // this just leaves includes with constants in, which we can't do much about
            if($include = $quoteSplit[1])
            {
                // If the path is not absolute, add the dir and separator
                // Then call realpath to chop out extra separators
                if(strpos($include, ':') === FALSE)
                    $include = realpath($dir.\DIRECTORY_SEPARATOR.$include);

                array_push($includes, $include);
            }
        }

        return $includes;
    }
}
