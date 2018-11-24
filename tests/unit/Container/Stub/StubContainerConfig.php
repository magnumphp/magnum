<?php
declare(strict_types=1);

namespace Magnum\Container\Stub;

use Magnum\Container\AbstractContainerConfig;
use Magnum\Container\ProvidesContainerConfig;

class StubContainerConfig
	extends AbstractContainerConfig
{
	use ProvidesContainerConfig;

	protected $entryPoints = [];

	public function __construct($entryPoints, $definitionHints, $wildcardHints = [])
	{
		$this->entryPoints     = $entryPoints;
		$this->definitionHints = $definitionHints;
		$this->wildcardHints   = $wildcardHints;
	}


}