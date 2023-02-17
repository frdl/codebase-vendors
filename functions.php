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
         ->withClassmap([
           \Webfan\Codebase\Server\BundleExportHelper::class => 'https://webfan.de/install/?source=Webfan\Codebase\Server\BundleExportHelper&salt=${salt}',
           \frdl\Proto::class => 'https://webfan.de/install/?source=frdl\Proto&salt=${salt}',
       ]); 
     
     
 }   
    
    
    
    
}//ns Webfan\Codebase\Server;

