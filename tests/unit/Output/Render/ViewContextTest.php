<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;
use Phrender\Context\Contains;

class ViewContextTest
	extends TestCase
{

	public function testObjectInstantiates()
	{
		$vc = new ViewContext();

		self::assertFalse($vc->accepts('test'));
	}

	public function testAddCombines()
	{
		$vc = new ViewContext();

		$vc->add(new MutableContext());

		self::assertTrue($vc->accepts('test'));
		self::assertEmpty($vc->provide('test'));
	}

	public function testRemoveDeletes()
	{
		$vc = new ViewContext();

		$vc->add($mc = new MutableContext());
		$vc->remove($mc);

		self::assertFalse($vc->accepts('test'));
	}

	public function testPropertyAccessToDepth()
	{
		$vc = new ViewContext();
		$vc->add(new MutableContext(['l1' => 'l1']));
		$vc->add($mc2 = new MutableContext(['l2' => 'l2']));

		self::assertEquals(['l1' => 'l1', 'l2' => 'l2'], $vc->provide('test'));
		self::assertEquals('l2', $vc->l2);

		$vc->remove($mc2);
		self::assertEquals(['l1' => 'l1'], $vc->provide('test'));
		self::assertEquals(null, $vc->l2);
	}

	public function testKeyAtDepth()
	{
		$vc = new ViewContext();
		$vc->add(new MutableContext(['l1' => 'l1']));
		$vc->add($mc2 = new MutableContext(['l2' => 'l2']));

		$vc->provide('test');
		self::assertArrayHasKey('l2', $vc);

		$vc->remove($mc2);
		self::assertArrayNotHasKey('l2', $vc);
	}

	public function testConditionalKeyAtDepth()
	{
		$vc = new ViewContext();
		$vc->add($c1 = new MutableContext(['l1' => 'l1']));

		// l2
		$vc->add($c2 = new Contains('testing', ['c3' => 'c3']));
		self::assertEquals(['l1' => 'l1', 'c3' => 'c3'], $vc->provide('testing'));

		// depth = 3 (this should fall through to the prior version when accessing the context as an array or object
		$vc->add($c3 = new MutableContext(['l2' => 'l2']));
		self::assertEquals(['l1' => 'l1', 'l2' => 'l2'], $vc->provide('test'));
		self::assertArrayHasKey('c3', $vc);
	}

	public function testOffsetSetDoesNothing()
	{
		$vc = new ViewContext();
		$vc->add($c1 = new MutableContext());

		$vc['test'] = 'nothing';

		self::assertEmpty($vc->provide('test'));
	}

	public function testOffsetUnsetDoesNothing()
	{
		$vc = new ViewContext();
		$vc->add($c1 = new MutableContext(['test' => 'exists']));
		$vc->provide('test');

		unset($vc['test']);

		self::assertEquals('exists', $vc->test);
	}
}