<?php

namespace Magnum\Container\Param;

/**
 * Handles parameter conversion to strings
 *
 * @package Magnum\Container\Param
 */
trait ConvertsParamsToString
{
	/**
	 * @param $params
	 * @return string The resolve parameters appropriate for echo
	 */
	protected function paramsToString($params)
	{
		$resolved = [];
		foreach ($params as $param) {
			if ($param instanceof Param) {
				$resolved[] = (string)$param;
			}
			elseif (is_array($param)) {
				// defer to the more array maker
				$resolved[] = (string)(new ArrayParam(...$param));
			}
			else {
				$resolved[] = var_export($param, true);
			}
		}

		return join(', ', $resolved);
	}
}