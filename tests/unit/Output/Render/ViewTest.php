<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;
use Phrender\Template\Factory;

class ViewTest
	extends TestCase
{
	protected $escaper;
	public function setUp()
	{
		$this->escaper = new class()
		{
			public function escape($str)
			{
				return "%{$str}%";
			}
		};
	}

	public function provideInvalidFileNames()
	{
		return [
			[''],
			[null]
		];
	}

	protected function buildView($file): View
	{
		return new View(TPL_PATH . "/{$file}.php", new TemplateFactory([TPL_PATH]));
	}

	public function testViewInstantiates()
	{
		self::assertInstanceOf(View::class, $this->buildView('test'));
	}

	public function testRenderEscapes()
	{
		$view          = $this->buildView('test');
		$view->escaper = $this->escaper;

		self::assertEquals('%test%', $view->render(new MutableContext(['var' => 'test'])));
	}

	public function testMagicGetEscapes()
	{
		$view          = $this->buildView('test-get');
		$view->escaper = $this->escaper;

		self::assertEquals('%test%', $view->render(new MutableContext(['var' => 'test'])));
	}

	public function testRawReturnsRealValue()
	{
		$view          = $this->buildView('test-raw');
		$view->escaper = $this->escaper;

		self::assertEquals('test', $view->render(new MutableContext(['var' => 'test'])));
	}

	/**
	 * @dataProvider provideInvalidFileNames
	 */
	public function testFileIsRequiredInConstructor($filename)
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('A file is required');
		new View($filename, new Factory());
	}
}