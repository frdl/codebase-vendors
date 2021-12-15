<?php

namespace Frdlweb\Contract\Autoload;

interface ConditionalResolverInterface extends ResolverInterface {
   public function withContext(Context $Context); 
}
