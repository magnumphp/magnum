<?php
declare(strict_types=1);

namespace Magnum\Container\Fixture;

/**
 * @copyright 2018, Woohoo Labs
 * @license MIT
 */
class ConstructorA
{
	/**
	 * @param ConstructorB $b
	 * @param ConstructorC $c
	 * @param bool $d
	 * @param null $e
	 */
	public function __construct(ConstructorB $b, $c, bool $d = true, string $e = null)
	{
	}
}
