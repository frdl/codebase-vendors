<?php 

namespace frdlweb\Procedure;

use Aura\Payload_Interface\PayloadInterface;

interface StepInterface
{
   public function __invoke(PayloadInterface $payload) : PayloadInterface;
}
