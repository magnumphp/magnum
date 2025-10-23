<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phrender\Template\Factory;

class ViewTest
	extends TestCase
{
	protected $escaper;

	public function setUp(): void
	{
		$this->escaper = new class() {
			public function escape($str)
			{
				return "%{$str}%";
			}
		};
	}

	public static function provideInvalidFileNames()
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

	public function testRawReturnsValue()
	{
		$view = new class($sut = new \stdClass())
			extends View {
			public function __construct($sut)
			{
				$this->data = [
					'var' => $sut
				];
			}
		};

		self::assertEquals($sut, $view->raw('var'));
	}

	public function testRawRendersRealValue()
	{
		$view          = $this->buildView('test-raw');
		$view->escaper = $this->escaper;

		self::assertEquals('test', $view->render(new MutableContext(['var' => 'test'])));
	}

	#[DataProvider('provideInvalidFileNames')]
	public function testFileIsRequiredInConstructor($filename)
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('A file is required');
		new View($filename, new Factory());
	}
}