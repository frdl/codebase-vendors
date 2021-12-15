<?php
namespace Frdlweb\Contract\Webfantized;

interface Project {
		public function __construct(string $vendor, string $packagename);
		public function withVendor(string $vendor);
		public function withName(string $packagename);
	}
	
