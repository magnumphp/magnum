<?php

namespace Magnum\Container\Compiler;

use Magnum\Container\Stub\ConstructorA;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolvePathsParameterTest
	extends TestCase
{
	public static function provideReservedNames()
	{
		return [
			['keys'],
			['tags'],
			['tag'],
		];
	}

	#[DataProvider('provideReservedNames')]
	public function testThrowsExceptionOnReservedNames($key)
	{
		$this->expectException(\InvalidArgumentException::class);
		(new ResolvePathsParameter())->append($key, '/test');
	}

	public function testRegistersWithContainer()
	{
		$c = new ContainerBuilder();
		$c->addCompilerPass($rpp = new ResolvePathsParameter());

		$rpp->append('migrations', '/kakaw', 'test');
		$rpp->prepend('migrations', '/test');

		$c->register(ConstructorA::class)
		  ->setPublic(true)
		  ->setArgument('$a', '%paths%');
		;

		$c->compile();

		$a = $c->get(ConstructorA::class);

		self::assertEquals(['/test', '/kakaw'], $a->a['migrations']);
		self::assertEquals(['migrations'], $a->a['keys']);
		self::assertEquals(['/kakaw'], $a->a['tag']['test']);
		self::assertEquals(['/kakaw'], $a->a['tag']['test-migrations']);
		self::assertEquals(['test', 'test-migrations'], $a->a['tags']);
	}
}