<?php

namespace Magnum\Output\Render;

use Interop\Output\TemplateFactory;
use Phrender\Engine;
use Phrender\Template\Template;

class View
	extends Template
{
	public function __construct($file, TemplateFactory $factory, Engine $engine)
	{
		$this->engine = $engine;
		parent::__construct($file, $factory);
	}

	public function changeLayout($layout)
	{
		$this->engine->changeLayout($layout);
	}

}