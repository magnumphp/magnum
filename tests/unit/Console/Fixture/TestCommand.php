<?php

namespace Magnum\Console\Fixture;

use Magnum\Console\CommandConfig;
use Magnum\Console\CommandProvider;

class TestCommand
	implements CommandProvider
{
	public static $seen = false;

	public static function configure(CommandConfig $config)
	{
		$config->setName('test-command')->setHandler(__CLASS__);
		self::$seen =true;
	}
}