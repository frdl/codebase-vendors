<?php
namespace Frdlweb;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;


interface WebAppInterface extends RequestHandlerInterface
{
	public function getContainer() : ContainerInterface;	
	public function handleCliRequest();	
	public function handleHttpRequest(\Psr\Http\Message\ServerRequestInterface $request = null) :?ResponseInterface;	
}
