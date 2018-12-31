<?php

namespace Magnum\Container\Compiler;

use Magnum\Container\Stub\ConstructorA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveDefaultParametersTest
	extends TestCase
{
	public function testContainerParametersAreUsed()
	{
		$rdp = new ResolveDefaultParameters();
		$c = new ContainerBuilder();
		$c->addCompilerPass(
			$rdp,
			PassConfig::TYPE_BEFORE_OPTIMIZATION,
			0
		);

		$rdp->param('a', 'test');

		$c->setParameter('a', 'test');

		$c->register(ConstructorA::class)
		  ->setPublic(true)
			->setArgument('$a', '%a%');
		;

		$c->compile();

		$a = $c->get(ConstructorA::class);

		self::assertEquals('test', $a->a);
	}
}