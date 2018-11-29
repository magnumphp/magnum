<?php

namespace Magnum\Container\Compiler;

use Magnum\Container\Builder;
use Magnum\Container\Stub\BadConstructorC;
use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\ConstructorC;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class FullAutowirePassTest
	extends TestCase
{
	public function testAutowiringWithArguments()
	{
		$builder = new Builder();
		$builder->instance(ConstructorB::class)
				->setArgument('$a', new ConstructorA());

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringWithKnownClass()
	{
		$builder = new Builder();
		$builder->singleton(ConstructorA::class);
		$builder->instance(ConstructorB::class);

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringWithVariadic()
	{
		$builder = new Builder();
		$builder->singleton(ConstructorC::class);

		$obj = $builder->container()->get(ConstructorC::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testAutowiringFails()
	{
		$builder = new Builder();
		$builder->instance(BadConstructorC::class);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			"Cannot autowire service \"Magnum\Container\Stub\BadConstructorC\"" .
			": argument \"\$x\" of method \"__construct()\" has type " .
			"\"Magnum\Container\Stub\ConstructorX\" but this class was not found."
		);
		$builder->container()->get(BadConstructorC::class);
	}
}