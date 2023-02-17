<?php

namespace Frdlweb;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;


interface WebAppInterface extends RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface;
	public function getContainer() : ContainerInterface;	
	public function handleCliRequest();	
	public function handleHttpRequest(\Psr\Http\Message\ServerRequestInterface $request = null) :?ResponseInterface;	
}
