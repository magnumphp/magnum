<?php

/**
 *
 * @file
 * Contains Magnum\Container\Resolver\ContextResolver
 */

namespace Magnum\Container\Resolver;

use Aura\Di\Resolver\Resolver;
use ReflectionParameter;

/**
 *
 * Resolves class creation specifics based on constructor params and setter
 * definitions, unified across class defaults, inheritance hierarchies,
 * configuration, and contexts
 *
 * @copyright 2018 AuraPHP
 * @license http://opensource.org/licenses/MIT MIT
 */
class ContextResolver
	extends Resolver
{
	/**
	 * The contexts that can change resolution
	 *
	 * @var array
	 */
	protected $contexts = [];

	/**
	 * The context stack
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * The context when clause
	 *
	 * @var string
	 */
	protected $ctxtWhen;

	/**
	 * The context needs clause
	 *
	 * @var string
	 */
	protected $ctxtNeeds;

	/**
	 * Set up a contexts when clause
	 *
	 * @param string $class
	 * @return self
	 */
	public function when($class)
	{
		$this->ctxtWhen = $class;

		return $this;
	}

	/**
	 * Set up a contexts needs
	 *
	 * @param string $class
	 * @return self
	 */
	public function needs($class)
	{
		if (!$this->ctxtWhen) {
			throw new \RuntimeException("Please call when first");
		}
		$this->ctxtNeeds = $class;

		return $this;
	}

	/**
	 * Set the parameters for the context
	 *
	 * @param array $params The parameters to provide
	 */
	public function provide(array $params)
	{
		if (!$this->ctxtWhen || !$this->ctxtNeeds) {
			throw new \RuntimeException("Please call when and needs first");
		}
		$this->contexts[$this->ctxtWhen][$this->ctxtNeeds] = $params;

		unset($this->ctxtWhen);
		unset($this->ctxtNeeds);
	}

	/**
	 * Overrides the resolve method to handle contexts
	 *
	 * @inheritdoc
	 */
	public function resolve(
		$class,
		array $mergeParams = [],
		array $mergeSetters = []
	) {
		array_push($this->context, $class);
		$resolved = parent::resolve($class, $mergeParams, $mergeSetters);
		array_pop($this->context);

		return $resolved;
	}

	/**
	 * Overrides the getUnified method to handle different keys for unification
	 *
	 * @inheritdoc
	 */
	public function getUnified($class)
	{
		// have values already been unified for this class?
		$key = join('.', $this->context);
		if (isset($this->unified[$key])) {
			return $this->unified[$key];
		}

		// default to an an array of two empty arrays
		// (one for params, one for setters)
		$spec = [[], []];

		// fetch the values for parents so we can inherit them
		$parent = get_parent_class($class);
		if ($parent) {
			if (!isset($this->contexts[$class])) {
				$context = $this->context;
				array_shift($context);
				array_unshift($context, $parent);
				if (isset($this->unified[$parent])) {
					return $this->unified[$parent];
				}
			}

			$spec = $this->getUnified($parent);
		}

		// stores the unified params and setters
		$this->unified[$key][0] = $this->getUnifiedParams($class, $spec[0]);
		$this->unified[$key][1] = $this->getUnifiedSetters($class, $spec[1]);

		// done, return the unified values
		return $this->unified[$key];
	}

	/**
	 * Overrides the getUnifiedParam to handle contexts
	 *
	 * @inheritdoc
	 */
	protected function getUnifiedParam(ReflectionParameter $rparam, $class, $parent)
	{
		$params = [];
		foreach ($this->context as $ctxt) {
			if (isset($this->contexts[$ctxt])) {
				$params = array_merge($params, $this->contexts[$ctxt]);
			}
		}

		if (isset($params[$class])) {
			$originalParams       = $this->params;
			$this->params[$class] = array_merge($this->params[$class], $params[$class]);
		}

		$unifiedParams = parent::getUnifiedParam($rparam, $class, $parent);

		if (isset($originalParams)) {
			$this->params = $originalParams;
		}

		return $unifiedParams;
	}
}