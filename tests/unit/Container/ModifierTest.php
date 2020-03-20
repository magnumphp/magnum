<?php

namespace Magnum\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Symfony\Component\DependencyInjection\Definition;

class ModifierTest
	extends TestCase
{
	/**
	 * @var Modifier
	 */
	protected $modifier;

	/**
	 * @var Definition
	 */
	protected $definition;

	public function setUp(): void
	{
		$this->modifier   = new Modifier('kakaw');
		$this->definition = new Definition('test');
	}

	public function provideMultiTests()
	{
		return [
			// arguments
			['setArgument', ['test', 'test'], 'getArgument', 'test', 'test'],
			['setArguments', [['test1' => 'test1', 'test2' => 'test2']], 'getArgument', 'test1', 'test1'],
			['setArguments', [['test1' => 'test1', 'test2' => 'test2']], 'getArgument', 'test2', 'test2'],
			['addArgument', ['test3'], 'getArgument', 0, 'test3'],
			// tags
			['setTags', [['tag1', 'tag2']], 'getTags', null, ['tag1', 'tag2']],
			['addTag', ['tag', ['k1' => 'v2']], 'getTag', 'tag', [['k1' => 'v2']]],
			// method call
			['setMethodCalls', [[['kakaw', ['k']]]], 'getMethodCalls', null, [['kakaw', ['k']]]],
			['addMethodCall', ['kakaw', ['k']], 'getMethodCalls', null, [['kakaw', ['k']]]],
			// properties
			['setProperties', [['p1' => 'p2']], 'getProperties', null, ['p1' => 'p2']],
			['setProperty', ['p2', 'p3'], 'getProperties', null, ['p2' => 'p3']],
			// instanceof
			['setInstanceofConditionals', [['i1' => 'i2']], 'getInstanceofConditionals', null, ['i1' => 'i2']],

			// singular methods
			['setFactory', ['test::build'], 'getFactory', null, ['test', 'build']],
			['setDecoratedService', ['test'], 'getDecoratedService', null, ['test', null, 0]],
			['setFile', ['testit'], 'getFile', null, 'testit'],

			// multi-boolean
			['setDeprecated', [], 'isDeprecated', null, true],

			// booleans
			['setAutoconfigured', [true], 'isAutoconfigured', null, true],
			['setShared', [true], 'isShared', null, true],
			['setAbstract', [true], 'isAbstract', null, true],
			['setPublic', [true], 'isPublic', null, true],
			['setLazy', [true], 'isLazy', null, true],
			['setSynthetic', [true], 'isSynthetic', null, true]
		];
	}

	/**
	 * @dataProvider provideMultiTests
	 */
	public function testMultiMethods($setter, $args, $getter, $value, $expected)
	{
		$this->modifier->$setter(...$args);
		$this->modifier->apply($this->definition, null);

		self::assertEquals($expected, $this->definition->$getter($value));
	}

	public function testReplaceArgumentsAreRecorded()
	{
		$this->modifier->setArgument('test', 'kakaw');
		$this->modifier->replaceArgument('test', 'test');
		$this->modifier->apply($this->definition, null);

		self::assertEquals('test', $this->definition->getArgument('test'));
	}

	public function testClearTag()
	{
		$this->modifier->setTags(['boom' => 'bang']);
		$this->modifier->clearTag('boom');
		$this->modifier->apply($this->definition, null);

		self::assertEquals([], $this->definition->getTag('boom'));
	}

	public function testRemoveMethodCall()
	{
		$this->modifier->addMethodCall('test');
		$this->modifier->removeMethodCall('test');
		$this->modifier->apply($this->definition, null);

		self::assertEquals([], $this->definition->getMethodCalls());
	}

	public function testBindingsAreRecorded()
	{
		$this->modifier->setBindings(['$k' => 'b1']);
		$this->modifier->apply($this->definition, null);

		$bindings = $this->definition->getBindings();
		self::assertArrayHasKey('$k', $bindings);

		// This is a bad test as it relies on the underlying implementation
		// due to the identifier being statically incremented we can't compare directly
		$expected  = (new BoundArgument('b1'))->getValues();
		$actual    = $bindings['$k']->getValues();
		$actual[1] = $expected[1];

		self::assertEquals($expected, $actual);
	}
}