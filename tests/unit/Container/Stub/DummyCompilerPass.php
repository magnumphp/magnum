<?php

namespace Magnum\Container\Stub;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DummyCompilerPass
	implements CompilerPassInterface
{
	public $called = false;

	public function process(ContainerBuilder $container)
	{
		$this->called = true;
	}
}