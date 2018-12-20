<?php

namespace Magnum\Http\Routing\Cache;

use Magnum\Http\Routing\Cache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class FileTest
	extends TestCase
{
	/**
	 * @var Cache
	 */
	protected $cache;

	/**
	 * @var vfsStreamDirectory
	 */
	protected $vfs;


	public function setUp()
	{
		$this->vfs = vfsStream::setup('root', null, [
			'_routing_has.php' => "<?php return ['good'];",
			'_routing_delete.php' => "<?php return ['delete'];"
		]);
		$this->cache = new File($this->vfs->url());
	}

	public function testHasReturnsFalse()
	{
		self::assertFalse($this->cache->has('test'));
	}

	public function testHasReturnsTrue()
	{
		self::assertTrue($this->cache->has('has'));
	}

	public function testGetReturnsEmptyArray()
	{
		self::assertEquals([], $this->cache->get('test'));
	}

	public function testGetReturnsFileContentsProperly()
	{
		self::assertEquals(['good'], $this->cache->get('has'));
	}

	public function testSetSavesToFile()
	{
		$this->cache->set('test', 'kakaw');

		$file = $this->vfs->url() . '/_routing_test.php';

		self::assertFileExists($file);
		self::assertEquals("<?php return " . var_export(['kakaw'], true) . ";", file_get_contents($file));
	}

	public function testDeleteFileIsDeletedAndReturnsTrue()
	{
		self::assertTrue($this->cache->delete('delete'));
		self::assertFileNotExists($this->vfs->url() . '/_routing_delete.php');
	}

	public function testDeleteReturnsFalseOnNoFile()
	{
		self::assertFalse($this->cache->delete('delete-not-here'));
	}
}