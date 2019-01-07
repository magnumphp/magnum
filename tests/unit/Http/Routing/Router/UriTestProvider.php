<?php

namespace Magnum\Http\Routing\Router;

/**
 * Provides the data for the generateUri tests
 *
 * This is separate because the code is Copyright Zend Technologies USA Inc. for the tests themselves. I copied them
 * here so I can ensure the tests are working.
 *
 * @package Magnum\Http\Routing
 *
 * @see       https://github.com/zendframework/zend-expressive-fastroute for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-fastroute/blob/master/LICENSE.md New BSD License
 */
class UriTestProvider
	extends \ArrayIterator
{
	// @formatter:off
	// @codingStandardsIgnoreStart
	protected $routes = [
		['post', '/foo', 'foo-create'],
		['get', '/foo','foo-list'],
		['get', '/foo/{id:\d+}', 'foo'],
		['any', '/bar/{baz}', 'bar'],
		['get', '/index[/{page:\d+}]', 'index'],
		['get', '/extra[/{page:\d+}[/optional-{extra:\w+}]]', 'extra'],
		['get', '/page[/{page:\d+}/{locale:[a-z]{2}}[/optional-{extra:\w+}]]', 'limit'],
		['get', '/api/{res:[a-z]+}[/{resId:\d+}[/{rel:[a-z]+}[/{relId:\d+}]]]', 'api'],
		['get', '/optional-regex[/{optional:prefix-[a-z]+}]','optional-regex'],
	];

	public function __construct($useEncoded = false)
	{
		parent::__construct($useEncoded ? $this->generateEncoded() : $this->generate());
	}

	public function generateEncoded()
	{
		return [
			'encoded-space'   => ['/foo/{id:.+}', '/foo/b%20ar', 'b ar'],
			'encoded-slash'   => ['/foo/{id:.+}', '/foo/b%2Fr', 'b/r'],
			'encoded-unicode' => ['/foo/{id:.+}', '/foo/bar-%E6%B8%AC%E8%A9%A6', 'bar-測試'],
			'encoded-regex'   => ['/foo/{id:bär}', '/foo/b%C3%A4r', 'bär'],
			'unencoded-regex' => ['/foo/{id:bär}', '/foo/bär', 'bär'],
		];
	}

	public function generate()
	{
		return [
			// Test case                 routes   expected URI                   generateUri arguments
			'foo-create'             => [$this->routes, '/foo',                        ['foo-create']],
			'foo-list'               => [$this->routes, '/foo',                        ['foo-list']],
			'foo'                    => [$this->routes, '/foo/42',                     ['foo', ['id' => 42]]],
			'bar'                    => [$this->routes, '/bar/BAZ',                    ['bar', ['baz' => 'BAZ']]],
			'index'                  => [$this->routes, '/index',                      ['index']],
			'index-page'             => [$this->routes, '/index/42',                   ['index', ['page' => 42]]],
			'extra-42'               => [$this->routes, '/extra/42',                   ['extra', ['page' => 42]]],
			'extra-optional-segment' => [$this->routes, '/extra/42/optional-segment',  ['extra', ['page' => 42, 'extra' => 'segment']]],
			'limit'                  => [$this->routes, '/page/2/en/optional-segment', ['limit', ['locale' => 'en', 'page' => 2, 'extra' => 'segment']]],
			'api-optional-regex'     => [$this->routes, '/api/foo',                    ['api', ['res' => 'foo']]],
			'api-resource-id'        => [$this->routes, '/api/foo/1',                  ['api', ['res' => 'foo', 'resId' => 1]]],
			'api-relation'           => [$this->routes, '/api/foo/1/bar',              ['api', ['res' => 'foo', 'resId' => 1, 'rel' => 'bar']]],
			'api-relation-id'        => [$this->routes, '/api/foo/1/bar/2',            ['api', ['res' => 'foo', 'resId' => 1, 'rel' => 'bar', 'relId' => 2]]],
			'optional-regex'         => [$this->routes, '/optional-regex',             ['optional-regex']],
		];
	}

	// @codingStandardsIgnoreEnd
	// @formatter:on
}