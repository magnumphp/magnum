<?php

/**
 * @file
 * Contains Magnum\Http\Container\ActionRequestDecoratorPass
 */

namespace Magnum\Http\Container;

use Magnum\Http\Message\DecoratedRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Grabs all services that are tagged in the container as "http.action" and checks to see if they want a different
 * ServerRequestInterface object.
 *
 * Two methods are supported:
 * - implement DecoratedRequestHandler with a `run` method and `$request` parameter.
 * - Annotate the class with @RequestDecorator
 */
class ActionRequestDecoratorPass
	implements CompilerPassInterface
{
	const TAG = 'http.action';

	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		$services = $container->findTaggedServiceIds(self::TAG, true);
		foreach ($services as $name => $params) {
			if (class_exists($name) && ($request = $this->resolveRequestDecorator(new \ReflectionClass($name)))) {
				$this->decorate($container, $name, $request);
			}
		}
	}

	/**
	 * Resolves the name of the RequestDecorator
	 *
	 * @param \ReflectionClass $class
	 *
	 * @return string|null The Request decorator class, null if there is none
	 * @throws \ReflectionException
	 */
	protected function resolveRequestDecorator(\ReflectionClass $class)
	{
		$docs = $class->getDocComment();
		if (($start = strpos($docs, '@RequestDecorator')) !== false) {
			$start += 18;

			return trim(substr($docs, $start, strpos($docs, "\n", $start) - $start));
		}

		if ($class->implementsInterface(DecoratedRequestHandler::class)) {
			// run is not contracted, but we really only support one parameter
			$method = $class->getMethod('run');
			$params = $method->getParameters();
			foreach ($params as $param) {
				if ($param->getName() === 'request' && $param->hasType()) {
					$type = $param->getType();
					if ($type instanceof \ReflectionNamedType && $type->getName() !== ServerRequestInterface::class) {
						return $type->getName();
					}

					return (string)$param->getType();
				}
			}
		}

		return null;
	}

	/**
	 * Decorates the given class with the ActionDecorator
	 *
	 * @param ContainerBuilder $container
	 * @param string           $decoratedClass
	 * @param string           $requestClass
	 */
	protected function decorate(ContainerBuilder $container, string $decoratedClass, $requestClass)
	{
		$inner = $container->hasDefinition($decoratedClass)
			? $container->getDefinition($decoratedClass)
			: $container->autowire($decoratedClass)->setAutoconfigured(true);

		$container->setDefinition($innerKey = "{$decoratedClass}.inner", $inner);

		$outer = $container->autowire($decoratedClass, ActionRequestDecorator::class)->setAutoconfigured(true);

		$container->setDefinition($decoratedClass, $outer);
		$outer->setPublic(true);
		$outer->setArgument(0, new Reference($innerKey));
		$outer->setArgument(1, $container->hasDefinition($requestClass) ? new Reference($requestClass) : $requestClass);
	}
}