<?php

namespace Frdlweb\Contract\Autoload{
	

if (!\interface_exists(CodebaseInterface::class, false)) {	
 interface CodebaseInterface
 { 
   const CHANNEL_LATEST = 'latest';
   const CHANNEL_STABLE = 'stable';
   const CHANNEL_FALLBACK = 'fallback';
	 
   public function getUpdateChannel() : string; 
   public function getRemotePsr4UrlTemplate() : string; 
   public function getRemoteModulesBaseUrl() : string; 
 }
} 
}
