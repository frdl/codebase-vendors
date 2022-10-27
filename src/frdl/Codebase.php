<?php

namespace frdl{

if (!\class_exists(Codebase::class, false)) {		
 abstract class Codebase implements \Frdlweb\Contract\Autoload\CodebaseInterface
 {
   protected $channels = null;
   protected $channel = null;
	
   abstract public function loadUpdateChannel(mixed $StubRunner = null) : string; 
	 
   public function __construct(string $channel = null){
	   $this->channels = [];
	   
	   $this->channels[self::CHANNEL_LATEST] = [
		   'RemotePsr4UrlTemplate' => 'https://webfan.de/install/latest/?source=${class}&salt=${salt}&source-encoding=b64',
		   'RemoteModulesBaseUrl' => 'https://webfan.de/install/latest',
		   
	   ];
		   
	   $this->channels[self::CHANNEL_STABLE] = [
		   'RemotePsr4UrlTemplate' => 'https://webfan.de/install/stable/?source=${class}&salt=${salt}&source-encoding=b64',
		   'RemoteModulesBaseUrl' => 'https://webfan.de/install/stable',
		   
	   ];	   
	   
	   $this->channels[self::CHANNEL_FALLBACK] = [
		   'RemotePsr4UrlTemplate' => 'https://webfan.de/install/?source=${class}&salt=${salt}&source-encoding=b64',
		   'RemoteModulesBaseUrl' => 'https://webfan.de/install/modules',		   
	   ];   
	   
	   if(null !== $channel && isset(static::CHANNELS[$channel])){
		   $this->setUpdateChannel(static::CHANNELS[$channel]);
	   }else{
		   $this->setUpdateChannel(static::CHANNELS[self::CHANNEL_LATEST]);
	   }
   }

	 
   public function setUpdateChannel(string $channel){
	   $this->channel = $channel;
	  return $this;
   }
	 
   public function getUpdateChannel() : string{
	   return $this->channel;
   }
	  
   public function getRemotePsr4UrlTemplate() : string{
	    return $this->channels[$this->getUpdateChannel()]['RemotePsr4UrlTemplate'];
   }
	  
   public function getRemoteModulesBaseUrl() : string{
	    return $this->channels[$this->getUpdateChannel()]['RemoteModulesBaseUrl'];
   }
	  	 
 }
}
}
