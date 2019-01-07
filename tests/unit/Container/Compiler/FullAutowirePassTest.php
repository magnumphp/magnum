<?php

namespace Magnum\Container\Compiler;

use Magnum\Container\Builder;
use Magnum\Container\Stub\BadConstructorC;
use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\ConstructorC;
use Magnum\Container\Stub\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class FullAutowirePassTest
	extends TestCase
{
	public function testAutowiringWithArguments()
	{
		$builder = new Builder();
		$builder->instance(ConstructorB::class)
				->setPublic(true)
				->setArgument('$a', new ConstructorA());

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringWithKnownClass()
	{
		$builder = new Builder();
		$builder->singleton(ConstructorA::class)->setPublic(true);
		$builder->instance(ConstructorB::class)->setPublic(true);

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringWithVariadic()
	{
		$builder = new Builder();
		$builder->singleton(ConstructorC::class)->setPublic(true);

		$obj = $builder->container()->get(ConstructorC::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringFails()
	{
		$builder = new Builder();
		$builder->instance(BadConstructorC::class)->setPublic(true);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			"Cannot autowire service \"Magnum\Container\Stub\BadConstructorC\"" .
			": argument \"\$x\" of method \"__construct()\" has type " .
			"\"Magnum\Container\Stub\ConstructorX\" but this class was not found."
		);
		$builder->container()->get(BadConstructorC::class);
	}

	public function testResolveFactoryReferences()
	{
		$builder = new Builder();

		$builder->factory('test', TestFactory::class, 'build')->setPublic(true);

		self::assertInstanceOf(ConstructorA::class, $builder->container()->get('test'));
	}
}