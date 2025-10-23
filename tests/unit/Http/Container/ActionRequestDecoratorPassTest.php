<?php

namespace Magnum\Http\Container;

use Magnum\Container\Builder;
use Magnum\Http\Stub\Action\CommentedRequestDecorator;
use Magnum\Http\Stub\Action\IgnoredAction;
use Magnum\Http\Stub\Action\InterfaceRequestDecorator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class ActionRequestDecoratorPassTest
	extends TestCase
{
	public function testProcessDetectsInterface()
	{
		$builder = new Builder();
		$builder->addCompilerPass(new ActionRequestDecoratorPass(), PassConfig::TYPE_OPTIMIZE);
		$builder->instance(InterfaceRequestDecorator::class)->setPublic(true)->addTag(ActionRequestDecoratorPass::TAG);

		$obj = $builder->container()->get(InterfaceRequestDecorator::class);
		self::assertInstanceOf(ActionRequestDecorator::class, $obj);
		self::assertObjectHasProperty('decorator', $obj);
	}

	public function testProcessReadDocComment()
	{
		$builder = new Builder();
		$builder->addCompilerPass(new ActionRequestDecoratorPass(), PassConfig::TYPE_OPTIMIZE);
		$builder->instance(CommentedRequestDecorator::class)->setPublic(true)->addTag(ActionRequestDecoratorPass::TAG);

		$obj = $builder->container()->get(CommentedRequestDecorator::class);
		self::assertInstanceOf(ActionRequestDecorator::class, $obj);
		self::assertObjectHasProperty('decorator', $obj);
	}

	public function testProcessIgnoresAction()
	{
		$builder = new Builder();
		$builder->addCompilerPass(new ActionRequestDecoratorPass(), PassConfig::TYPE_OPTIMIZE);
		$builder->instance(IgnoredAction::class)->setPublic(true)->addTag(ActionRequestDecoratorPass::TAG);

		$obj = $builder->container()->get(IgnoredAction::class);
		self::assertInstanceOf(IgnoredAction::class, $obj);
	}
}