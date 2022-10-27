<?php
namespace Frdlweb;

use frdlweb\AppInterface;

interface AdvancedWebAppInterface extends WebAppInterface
{
  //public function findAppDir(string $userdir = null, $create = false);
    public function getDir(string $type = null, $create = true) : string;
    public function getFile(string $path, string $type = null) : string;
    public function getStub(): ?StubHelperInterface;
    public function setStub(?StubHelperInterface $stubHelper = null) : AppInterface;
    public function getSources(string $userdir = null, $create = false) : array;
    public function getWebUriBase() : string | bool;
    public static function getInstance(string $env = null, string $dir = null): AppInterface;
    public static function mutex(): AppInterface;
    public function getAppId() : string;
    public function mount(): AppInterface;
    public function setAppId(string $appId = '1.3.6.1.4.1.37553.8.1.8.8.1958965301') : ?AppInterface;
    public function tick(
        string|\callable|\closure $script = null,
        array $contextArgs = null,
        int $flags = \EXTR_OVERWRITE,
        string $prefix = ""
    );
    public function onError($number = null, $message = null, $file = nulle, $line = null, $errcontext = null);
    public function exception_handler(\Exception $exception);
    public function main(string $alias = null);
}
