<?php

namespace Magnum\Container\Compiler;

use Magnum\Container\Builder;
use Magnum\Container\Stub\ConstructorA;
use PHPUnit\Framework\TestCase;

class StaticProxyPassTest
	extends TestCase
{
	public function testContainerParametersAreUsed()
	{
		$builder = new Builder();
		$builder->proxy('ConA', ConstructorA::class);
		$builder->setParameter('a', 'kakaw');

		$builder->register(ConstructorA::class)
		  ->setPublic(true)
		  ->setArgument('$a', '%a%');
		;

		$builder->container();
		self::assertInstanceOf(ConstructorA::class, \ConA::test());
	}
}