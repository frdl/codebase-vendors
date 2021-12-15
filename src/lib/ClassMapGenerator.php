<?php
namespace Webfan\App {
	use Webfan\FameFork\Nette\Loaders\RobotLoader;

if (false) {
	 
	class ClassMapGenerator extends RobotLoader
	{
	}
} elseif (!class_exists(ClassMapGenerator::class)) {
  \class_alias(RobotLoader::class, ClassMapGenerator::class);
}
}




namespace Webfan\Autoload {
	use Webfan\Autoload\CodebaseLoader04;

if (false) {
	 
	class ClassMapGenerator extends CodebaseLoader04
	{
	}
} elseif (!class_exists(ClassMapGenerator::class)) {
  \class_alias(CodebaseLoader04::class, ClassMapGenerator::class);
}
}