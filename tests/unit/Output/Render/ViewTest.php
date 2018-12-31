<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;

class ViewTest
	extends TestCase
{
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
		$view = $this->buildView('test');
		$view->escaper = new class() {
			function escape($str) {
				return "%{$str}%";
			}
		};

		self::assertEquals('%test%', $view->render(new MutableContext(['var' => 'test'])));
	}

	public function testMagicGetEscapes()
	{
		$view = $this->buildView('test-get');
		$view->escaper = new class() {
			function escape($str) {
				return "%{$str}%";
			}
		};

		self::assertEquals('%test%', $view->render(new MutableContext(['var' => 'test'])));
	}

	public function testRawReturnsRealValue()
	{
		$view = $this->buildView('test-raw');
		$view->escaper = new class() {
			function escape($str) {
				return "%{$str}%";
			}
		};

		self::assertEquals('test', $view->render(new MutableContext(['var' => 'test'])));
	}

//	public function testNeedsExtractionReturnsFalse()
//	{
//		self::assertFalse($this->buildView('test')->needsExtraction());
//	}

//	public function testNeedsExtractionReturnsTrue()
//	{
//		self::assertTrue($this->buildView('test-with-extraction')->needsExtraction());
//	}
}