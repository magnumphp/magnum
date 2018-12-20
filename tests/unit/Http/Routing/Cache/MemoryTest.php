<?php

namespace Magnum\Http\Routing\Cache;

use PHPUnit\Framework\TestCase;

class MemoryTest
	extends TestCase
{
	public function setUp()
	{
		$this->cache               = new Memory();
		$this->cache->cache['yes'] = true;
	}

	public function testHasReturnsFalse()
	{
		self::assertFalse($this->cache->has('nope'));
	}

	public function testHasReturnsTrue()
	{
		self::assertTrue($this->cache->has('yes'));
	}

	public function testSetValue()
	{
		$this->cache->set('test', 'test');
		self::assertArrayHasKey('test', $this->cache->cache);
		self::assertEquals($this->cache->cache['test'], 'test');
	}

	public function testGetReturnsEmptyArrayWithBadKey()
	{
		self::assertEmpty($this->cache->get('nope'));
	}

	public function testGetReturnsValue()
	{
		$this->cache->cache['yes'] = ['yes'];
		self::assertEquals(['yes'], $this->cache->get('yes'));
	}

	public function testDeleteRemovesValue()
	{
		$this->cache->delete('yes');
		self::assertFalse($this->cache->has('yes'));
	}
}