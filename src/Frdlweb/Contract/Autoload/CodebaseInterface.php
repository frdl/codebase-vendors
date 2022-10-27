<?php

namespace Frdlweb\Contract\Autoload;
 	
 interface CodebaseInterface
 { 
   const CHANNEL_LATEST = 'latest';
   const CHANNEL_STABLE = 'stable';
   const CHANNEL_FALLBACK = 'fallback';
   const CHANNELS =[
        self::CHANNEL_LATEST => self::CHANNEL_LATEST,
        self::CHANNEL_STABLE => self::CHANNEL_STABLE,
        self::CHANNEL_FALLBACK => self::CHANNEL_FALLBACK,
	];
	 
   public function setUpdateChannel(string $channel); 
   public function getUpdateChannel() : string; 
   public function getRemotePsr4UrlTemplate() : string; 
   public function getRemoteModulesBaseUrl() : string;
   public function loadUpdateChannel(mixed $StubRunner = null) : string;
 }

 
