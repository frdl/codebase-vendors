<?php

namespace Webfan\Traits {

trait WithContextDirectories {

	
protected $cacheRootDir=null;
protected $cacheDir=null;	

	
	
public function toSlug($string, $force_lowercase = true, $anal = true, $replaceBy = '_'){
  return $this->normalizeToSlug($this->normalizeToWords($string), $force_lowercase, $anal, $replaceBy);
}	
	
public function normalizeToWords($string){
    $string = htmlentities($string, \ENT_QUOTES, 'UTF-8');
    $string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
    $string = html_entity_decode($string, \ENT_QUOTES, 'UTF-8');
    $string = preg_replace(array('~[^0-9a-z]~i', '~[ -]+~'), ' ', $string);

    return trim($string, ' -');
}	
	
public function normalizeToSlug($string, $force_lowercase = true, $anal = true, $replaceBy = '') {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", $replaceBy, $clean) : $clean ;
    return ($force_lowercase) ?
        (\function_exists('mb_strtolower')) ?
            \mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}	
	
	
public function findIn($Dirs, $search = 'composer.json'){
 $filesFound = [];	
	
 if(!is_array($Dirs)){
	$Dirs=[$Dirs]; 
 } 
	
 foreach($Dirs as $dir){		
	foreach(\Nette\Utils\Finder::findDirectories($search)->in($dir) as $DirectoryIterators){
		foreach($DirectoryIterators as $dirIterator){
			  $filesFound = array_merge($filesFound, $this->findIn($dirIterator->getRealPath(), $search) );
		}
		 
   foreach(\Nette\Utils\Finder::findFiles($search)->in($dir) as $iterator){
      foreach($iterator as $file){
    	  $path =  $file->getRealPath() ;
	      $filesFound[]=$path;
   }
  } 
	
  }
 }	
	return $filesFound;
}	
	
	
	

public function cacheRoot(string $name = null){
	   if(is_string($name)){
		  $this->cacheRootDir=$this->tempDir($name, 'local');
	   }
   if(null === $this->cacheRootDir){
       $this->cacheRootDir= $this->tempDir(null, 'local');
   }
 return $this->cacheRootDir;
}

public function homedir(){
	
	$user = \posix_getpwuid(\posix_getuid()); 
/*
Array
(
    [name] => username
    [passwd] => ********
    [uid] => 501
    [gid] => 20
    [gecos] => Full Name
    [dir] => /home/username
    [shell] => /bin/bash
)
*/
  return $user['dir'];
}
public function tmpdir($context = 'global'){
	$method=[$this, $context.'_'.__FUNCTION__];
	if(is_callable($method)){
		return call_user_func($method);
	}
 return $this->sys_tmpdir();
}
public function usr_tmpdir(){
  $dir = $this->homedir().\DIRECTORY_SEPARATOR.'tmp';
  if(!is_dir($dir)){
    @mkdir($dir, 0777, true);
  }
  if(!is_writable($dir)){
    throw new \Exception(sprintf('The directory "%s" is not writable', $dir));
  }	
 return $dir;
}
public function local_tmpdir(){
 return $this->usr_tmpdir();
}	
public function global_tmpdir(){
 return $this->sys_tmpdir();
}		
public function sys_tmpdir(){
 return \sys_get_temp_dir();
}	
public function tempDir( $name = null, $context = 'global'){
 
	   $tmpdir =  $this->tmpdir($context);  
	   
       if(is_string($name)){
		return $tmpdir
                                    . \DIRECTORY_SEPARATOR
                                     .$name
                                    . \DIRECTORY_SEPARATOR ;   
	   }elseif(false === $name){
		return $tmpdir . \DIRECTORY_SEPARATOR;   
	   }elseif(true === $name){
		return $tmpdir
                                    . \DIRECTORY_SEPARATOR
                                     .get_current_user() 
                                    . \DIRECTORY_SEPARATOR ;   
	   }elseif(null === $name){
           return  $tmpdir. \DIRECTORY_SEPARATOR;   
       }


}
public function getCacheDir(string $name = null){
 
          $base = $this->cacheRoot($name);

	   if(null === $name){
		$name = '';   
	   }
	
	  $name = \preg_replace("/[^A-Za-z0-9\.\-\_\:\@]/", "", $name);

	 
	 return (empty($name)) ? $base  : $base. \DIRECTORY_SEPARATOR.$name. \DIRECTORY_SEPARATOR ;
}
	
	
	



}

}
