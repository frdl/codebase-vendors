<?php


namespace frdl{

function create($arg)
{
    trigger_error('frdl\create is deorecated!', \E_USER_DEPRECATED);
    return Proto::create($arg);
}
  
}//ns frdl;

namespace Webfan\Codebase\Server {
    
 function init(){
     
     //patches
     \frdl\implementation\psr4\RemoteAutoloaderApiClient::getInstance('https://webfan.de/install/stable/?source={{class}}&salt={{salt}}', true)       
            ->withClassmapFor('frdl/codebase', 'latest', \PHP_VERSION, 24 * 60 * 60);
     
 }   
    
    
    
    
}//ns Webfan\Codebase\Server;

