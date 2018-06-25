<?php


namespace Magnum\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandHandler
{
	public function run(InputInterface $input, OutputInterface $output);
}