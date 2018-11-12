<?php
declare(strict_types=1);

namespace Magnum\Container\Fixture;

/**
 * @copyright 2018, Woohoo Labs
 * @license MIT
 */
class ConstructorC
{
	/**
	 * @param ConstructorE $d
	 */
	public function __construct(ConstructorD $d)
	{
	}
}
