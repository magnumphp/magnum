<?php

namespace Magnum\Output\Render;

use PHPUnit\Framework\TestCase;

class TwoStageTest
	extends TestCase
{
	public function testRenderUsesLayout()
	{
		$e = new TwoStage(new TemplateFactory([TPL_PATH]));
		$o = $e->render('test', ['var' => 'output', 'other' => 'var']);

		self::assertEquals('layout:outputvar', $o);
	}

	public function testRender()
	{
		$e = new TwoStage(new TemplateFactory([TPL_PATH]));
		$e->changeLayout(null);
		$o = $e->render('test', ['var' => 'output']);

		self::assertEquals('output', $o);
	}

	public function testReset()
	{
		$e = new TwoStage(new TemplateFactory([TPL_PATH]));
		$e->changeLayout(null);
		$e->render('test', ['var' => 'output']);
		$e->reset();

		self::assertEquals('error', $e->render('test', ['var' => 'error']));
	}
}