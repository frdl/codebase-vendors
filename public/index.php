<?php
use Nette\Utils\Helpers;
use Melbahja\Http2\Pusher;
/*
die('PAUSE '.basename(__FILE__).__LINE__.$_SERVER['SERVER_ADDR']);

@todo: bundle/printer...., 2oop functions
*/
function multineedle_stripos($haystack, $needles, $offset=0) {
    foreach($needles as $needle) {
        $found[$needle] = stripos($haystack, $needle, $offset);
    }
    return $found;
}
function cleanArray($didYouMeans){
		    $_i=0;
		    $didYouMeans_Clean=[];
		    foreach($didYouMeans as $suggestion){	
				if(!empty($suggestion)){
					$didYouMeans_Clean[]=$suggestion;
				}
				$_i++;
			}	
	return $didYouMeans_Clean;
}



$content='';

require __DIR__.'/../vendor/autoload.php';
  //ClassMapsLoaderBundle.php



$per_page = 25;

$someDirs=[
 'vendor',	
 'src',	
 'packages',	
 'modules',	
	
 
 
];

 $loader = new \Webfan\Autoload\CodebaseLoader;
 $loader->setTempDirectory(__DIR__ . \DIRECTORY_SEPARATOR. '..'.  \DIRECTORY_SEPARATOR.'cache'.  \DIRECTORY_SEPARATOR.'codebase-loader-caches');
 $loader->reportParseErrors(false);
 
//die($loader->getCacheFile( '_classmaps').'<br />'.$loader->getCacheFile( ) );


 foreach($someDirs as $subdir){
	 $__dir=realpath(__DIR__ . \DIRECTORY_SEPARATOR. '..'.  \DIRECTORY_SEPARATOR. $subdir);
	 if(is_dir($__dir)){
	    $loader->addDirectory($__dir);
	 }
 }


 

  $didYouMeans = [];
$cacheFile = $loader->getCacheFile( '_classmaps'); // $loader->getCacheFile(  ); //
if(empty($content)){

   
   $loader->setAutoRefresh(!file_exists($cacheFile));
  if(!file_exists($cacheFile)
	// || !\class_exists(\Wehowski\Helpers\ArrayHelper::class) 
	){
	  $loader->refresh();
	  $loader->rebuild();
  }
  $loader->register();


	
	
	  
	$FloodProtection = new \frdl\security\floodprotection\FloodProtection($_SERVER['REQUEST_URI'], 60, 60);	
  if($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR'] && $FloodProtection->check($_SERVER['REMOTE_ADDR'])){
     header("HTTP/1.1 429 Too Many Requests");
 
 	 $content='Too Many Requests, please try again later!';
  }
	
	
   $classMaps = require $cacheFile;

	
   $Helper = new \Wehowski\Helpers\ArrayHelper($classMaps);
   $classes=$Helper->keys();
  

if(isset($_GET['bundle']) && !isset($_GET['source'])){
	$_GET['source'] = $_GET['bundle'];
}elseif(isset($_GET['bundle']) && isset($_GET['source'])){
	
	
	$content.='You can ONLY SPECIFY EITHER source OR bundle!';
}

}//empty $content


if(empty($content) && isset($_GET['source']) && '*'!==$_GET['source']){

	$source = $_GET['source'];
	$source = str_replace('/', '\\', $source);
	$source = str_replace('.php', '', $source);
//	$source = trim($source, '\ ');
//	$source=addslashes($source);
	if('\\' === substr($source, 0,1)){
	  $source = substr($source,1,strlen($source	));
	}
	
	if(isset($classMaps[$source])){
	
		if(!isset($_GET['version'])){
			 $file =  $loader->file($source);
		}elseif(isset($_GET['version']) && isset($classMaps[$source][$_GET['version']])  ) {
			$file=$classMaps[$source][$_GET['version']][0];
		}elseif(isset($_GET['version']) && 'latest'===$_GET['version']){
			$lastI=false;
			foreach($classMaps[$source] as $i){
				//$content.=print_r($í,true);
				if(false===$lastI || $lastI[1] < $i[1]){
				  $lastI = $i;	
				  	$file=$i[0];
				}
			}
			
		}elseif(isset($_GET['version']) && isset($classMaps[$source][$_GET['version']])  ) {
			$file=$classMaps[$source][$_GET['version']][0];
		}
		
		
		
		
		if(!isset($file) || !file_exists($file)){
	    	  $file =  $loader->file($source);
			
	    	foreach($classMaps[$source] as $hash=> $i){	 	
 
			
				Pusher::getInstance()->set(false, 
                       'https://startdir.de/install/?source='.urlencode($source).'&version='.$hash,
						[
                             'rel'=>'alternate',
                               'as'=>false

                       ]);
		     }				
		}
		
		
		if(is_string($file) && file_exists($file) ){		
		 
		
			
			
			
		if(isset($_GET['bundle'])){	
		//	$code = file_get_contents($file);
			$FileAll = (new \Nette\PhpGenerator\Extractor(file_get_contents($file)))->extractAll();
			$code = (new \Nette\PhpGenerator\PsrPrinter)->printFile($FileAll);
		 
			header('Content-Type: text/plain');
		

	   
	   
	    $hash_check = strlen($code).'.'.sha1($code);
	    $userHash_check = sha1(((isset($_GET['salt']))?$_GET['salt']:null) .$hash_check);	
		header('X-Content-Hash: '.$hash_check);
		header('X-User-Hash: '.$userHash_check);
			
		header('Content-Md5: '.md5($code));
		header('Content-Sha1: '.sha1($code));
			
		//	die($code);
		 echo $code;
		return;
		}else{


			$File = new \Nette\PhpGenerator\PhpFile;
			$Nss=[];
 
			try{
			$FileAll = (new \Nette\PhpGenerator\Extractor(file_get_contents($file)))->extractAll();
			}catch(\Exception $e){
			  die('Error: '.$e->getMessage());	
			}
	 
			
	        $namespaces=$FileAll->getNamespaces();
			foreach($namespaces as $ns){
			   $_classes = $ns->getClasses();
              $_functions = $ns->getFunctions();
            //  $_traits = $ns->getTraits();
				$_break = false;
				
				foreach($_classes as $_class){						
					
						if($source === ltrim($ns->getName().'\\'.$_class->getName(), '\\ ') ){
									
           /* print_R($ns->getName().'\\'.$_class->getName().'<pre>');	print_R($ns->getName().'\\'.$_class->getName()); */
							if(!isset($Nss[$ns->getName().'\\'.$_class->getName()])){
							  $Nss[$ns->getName().'\\'.$_class->getName()] = $File->addNamespace($ns);
							}
						//	$File->addClass($_class);
							$_break=true;
							break;
						}					
		    	}			
				foreach($_functions as $_function){						
					
						if($source === ltrim($ns->getName().'\\'.$_function->getName(), '\\ ') ){
									
						   if(!isset($Nss[$ns->getName().'\\'.$_function->getName()])){
							  $Nss[$ns->getName().'\\'.$_function->getName()] = $File->addNamespace($ns);
							}
							
							$_break=true;
							break;
						}					
		    	}				
			    if(true===$_break || $ns->getName() === $source)break;
			}
			
				
			
	    if(true===$_break){
			$outPut = (new \Nette\PhpGenerator\PsrPrinter)->printFile($File);
			 //$outPut .= (new \Nette\PhpGenerator\PsrPrinter)->printClass($CClasscode);
		}elseif(function_exists($source)){
		            $function = \Nette\PhpGenerator\GlobalFunction::from($source);	
			       $outPut = ''. $function;
		}else{
			$outPut = (new \Nette\PhpGenerator\PsrPrinter)->printFile($File);
		}
			
			if(0===count($Nss) || empty(trim($outPut, ' <?php '))){
		    	$outPut = file_get_contents($file);
			}

			
			header('Content-Type: text/plain');
		   
			$hash_check = strlen($outPut).'.'.sha1($outPut);
	    $userHash_check = sha1(((isset($_GET['salt']))?$_GET['salt']:null) .$hash_check);	
		header('X-Content-Hash: '.$hash_check);
		header('X-User-Hash: '.$userHash_check);
			
		header('Content-Md5: '.md5($outPut));
		header('Content-Sha1: '.sha1($outPut));
			
		echo $outPut;
			
			return;
		}
	}else{
			\wResponse::status(404);
		    $content.='Not found - 404 ['.__LINE__.']<br />';
			//$variants = array_keys($classMaps[$source]);
			//$content.=print_r($variants,true);
					
		$content.='<legend>Variants of '.htmlentities($source).'</legend>';
		$content.='<ul>';	
		foreach($classMaps[$source] as $hash=> $i){	 	
			$content.='<li>';	
			$content.='<a href="?source='.urlencode($source).'&amp;version='.$hash.'">'
				.date('d.m.Y h.m:s', $i[1]).' '.htmlentities($hash)
				.'</a>';	 
			$content.='</li>';	
			/*
				Pusher::getInstance()->set(false, 
                       'https://startdir.de/install/?source='.urlencode($source).'&version='.$hash,
						[
                             'rel'=>'alternate',
                               'as'=>false

                       ]);
					   */
		}	
		$content.='</ul>';
	}
		
		
	}else{
		//$source = str_replace('\\', '\\\\', $source);
	//die(gettype($subNamespacePart).gettype($classes));
		
		
		
			$didYouMeans[]=Helpers::getSuggestion($classes, $source);
		 	
		    $length=strlen($source);
		     $delimiters='\\./_*'.\DIRECTORY_SEPARATOR;
		  //  $nsParts = preg_split("/[\s\t\r\n".preg_quote($delimiters)."]/", $source);
		      $nsParts = preg_split("/[^A-Za-z0-9\_]/", $source);

		foreach($classes as $className){
			 $needle = $nsParts;
			$haystack = $className;
			
		    foreach($nsParts as $subNamespacePart){
				
				$didYouMeans[]=Helpers::getSuggestion($classes, $subNamespacePart);
				//$haystack = "The quick brown fox jumps over the lazy dog.";
               // $needle = array("fox", "dog", ".", "duck")
               // (multineedle_stripos($haystack, $needle));
					
			}
			
			 $a = $length;
		     $b = -1;
		  	 $token = substr($source, $b, $a);
			 $needle[]=$token;
			 while($b<$length && $a > 0 && ++$b < --$a && strlen($token)>2){
				 $token = substr($source, $b, $a);
				 if(strlen($token)>3 ){
				    $needle[]=trim($token);
				 }
			 }
			
			$foundMatches = multineedle_stripos($haystack, $needle);
				
				
			$matches=[];
			foreach($foundMatches as $match => $data){
			
				if(!empty($match)){
				   $matches[]=$className;	
					break;
				}
			}
		
			
			 if(count($matches) > 0){
				 $didYouMeans[]= $className;
				  break;
			 }
		
			$didYouMeans=cleanArray($didYouMeans);
					
			if($didYouMeans > 25){
				break;
			}
		}
		
		     
		
		
		    
		    $didYouMeans_Clean=cleanArray($didYouMeans);
		
		
		//$content.='count($classMaps):'.count($classMaps).'<br />';
		//$content.='count($classes):'.count($classes).'<br />';
		//$content.='print_r($nsParts, true):'.print_r($nsParts, true).'<br />';
		////$content.='$length:'.$length.'<br />';
		
	if(0<count($didYouMeans_Clean)){
		$content.='Did you mean...?';
		//$content.=print_r($didYouMeans_Clean, true).'<br />'.$source.'<ul>';	
		foreach($didYouMeans_Clean as $suggestion){	 	
			$content.='<li>';	
			$content.='<a href="?source='.$suggestion.'">'.$suggestion.'</a>';	 
			$content.='</li>';	
		}	
		$content.='</ul>';
	}
	}//count($didYouMeans_Clean)

	

}elseif(empty($content)){
	 $page=(isset($_GET['page']))?intval($_GET['page']):1;
  $results= \Wehowski\Helpers\ArrayHelper::paginate($classes, (isset($_GET['page']))?intval($_GET['page']):1,25);
$content.='<h1>PHP Classes</h1>';	
$content.=<<<PHPCODE
<p>Legacy Webfan Install &amp; Source Code - will be updated, deprecations will be gone, new versions will come ...</p>

<p>
	To consume the PHP-Classes Endpoint in a PSR-4 Autoloading from remote strategy way you may use the 
	<a href="https://github.com/frdl/remote-psr4">frdl/remote-psr4</a> package.
</p>
	
PHPCODE;
	
$content.='<ul>';
foreach($results as $class){
	 
	$content.='<li>';
	 $content.='<a href="?source='.urlencode($class).'">'.htmlentities($class).'</a>';
	$content.='</li>';
}
$content.='</ul>';
$content.='<a href="?page='.($page-1).'">'.htmlentities('<<<').'</a> <a href="?page='.($page+1).'">'.htmlentities('>>>').'</a>';
	
}//not GET source


?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8" />
    <title>Install - Legacy Webfan Install &amp; Source Code - will be updated...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script type="text/javascript">(async(q,v,w,d,h,wcsb)=>{var s=d.createElement('script');
s.setAttribute('src', 'https://cdn.frdl.de'
   +'/!bundle/run/' +h+'-'+v+'/@webfan3/frdlweb/webfan-website.js'
//	+'/@webfan3/frdlweb/latest/webfan-website.js?DEBUG.enabled=true'
);
console.log(q,q);
s.setAttribute('data-webfan-config-query', q);													  
s.setAttribute('data-frdl-website-origin', w.origin);s.async=true;s.onload=()=>{wcsb();};d.head.append(s);	 
})(
    '', // 'DEBUG.enabled=true',
   'abc00dd05655676004',
   window, 
   document,  
   (new Date()).getYear() + '-' +  (new Date()).getMonth()+ '-' +  '00',//(new Date()).getDay(),
   ()=>{
 
      console.log('Hi', 'www');
		
});</script>	
<style>
    [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-    ng-cloak {
        display: none !important;
    }
</style>

</head>
<body>
<?php
	echo $content;
?>
</body>
</html><?php
