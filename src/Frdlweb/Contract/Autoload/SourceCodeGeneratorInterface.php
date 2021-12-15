<?php

interface SourceCodeGeneratorInterface
{
     public function file(string $className):string;
     public function source(string $className):string;
     public function bundle(array $classes):string;   
}
