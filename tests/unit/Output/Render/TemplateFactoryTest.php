<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;

class TemplateFactoryTest
	extends TestCase
{
	public function testHasReturnsFalse()
	{
		$tf = new TemplateFactory([TPL_PATH]);
		self::assertFalse($tf->has('not_a_file'));
	}

	public function testHasReturnsTrue()
	{
		$tf = new TemplateFactory([TPL_PATH]);
		self::assertTrue($tf->has('test'));
	}

	public function testLoadReturnsMagnumView()
	{
		$tf = new TemplateFactory([TPL_PATH]);
		self::assertInstanceOf(View::class, $tf->load('test'));
	}

	public function testLoadHonorsAbsolutePath()
	{
		$tf = new TemplateFactory();
		self::assertInstanceOf(View::class, $tf->load(TPL_PATH . '/full.phtml'));
	}
}