<?php

/**
 * @file
 * Contains Magnum\Http\Message\DecoratedRequestHandler
 */

namespace Magnum\Http\Message;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Indicates whether or not the ActionRequestDecoratorPass shoufasld look for a `run` method and `$request` parameter to
 * decorate the request passed in.
 *
 * Alternative usage is to use a class level annotation of @RequestDecorator
 *
 * @package Magnum\Http\Message
 */
interface DecoratedRequestHandler
	extends RequestHandlerInterface
{

}