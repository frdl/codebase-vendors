<?php 

namespace frdlweb;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

interface AppInterface
{
	public function __construct(string $env = null, string $dir = null);	
	public static function getInstance(string $env = null, string $dir = null) : AppInterface;	
	public function getContainer() : ContainerInterface;	
	public function boot(): AppInterface;
	public function handle(\Psr\Http\Message\ServerRequestInterface $request = null);	
	public function handleCliRequest();	
	public function handleHttpRequest(\Psr\Http\Message\ServerRequestInterface $request = null) :?ResponseInterface;	
}
