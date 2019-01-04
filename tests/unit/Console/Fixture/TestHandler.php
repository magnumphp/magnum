<?php

namespace Magnum\Console\Fixture;

use Magnum\Console\CommandHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestHandler
	implements CommandHandler
{
	public $ran = false;

	public function run(InputInterface $input, OutputInterface $output)
	{
		$this->ran = true;
	}
}