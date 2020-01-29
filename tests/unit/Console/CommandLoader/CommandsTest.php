<?php

namespace Magnum\Console\CommandLoader;

use Magnum\Console\Application;
use Magnum\Console\Fixture\TestCommand;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class CommandsTest
	extends TestCase
{
	protected $vfs;
	/**
	 * @var Commands
	 */
	protected $cmds;

	public function setUp(): void
	{
		$this->vfs  = vfsStream::setup('root');
		$this->cmds = new Commands(
			'',
			[
				TestCommand::class
			]
		);

		$this->app = new Application(new Container());
	}

	public function testRegister()
	{
		$this->cmds->register($this->app);
		self::assertTrue(TestCommand::$seen);
	}

	public function testHasReturnsTrue()
	{
		$this->cmds->register($this->app);
		self::assertTrue($this->cmds->has('test-command'));
	}

	public function testHasReturnsFalse()
	{
		$this->cmds->register($this->app);
		self::assertFalse($this->cmds->has('no-command'));
	}

	public function testGetNamesReturnsArray()
	{
		self::assertIsArray($this->cmds->getNames());
		$this->cmds->register($this->app);
		self::assertContains('test-command', $this->cmds->getNames());
	}

	public function testGetReturnsCommandProxy()
	{
		$this->cmds->register($this->app);
		self::assertInstanceOf(CommandProxy::class, $this->cmds->get('test-command'));
	}

	public function testGetThrowsExceptionWhenNoEmpty()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Command does not exist: test-command");

		$this->cmds->get('test-command');
	}

	public function testConstructorLoadsFromSavedFile()
	{
		file_put_contents(
			$this->vfs->url() . '/_commands.php',
			'<?php return ["cache-command" => "Magnum\Console\Fixture\TestCommand"];'
		);
		$cmds = new Commands($this->vfs->url(), []);
		self::assertTrue($cmds->has('cache-command'));
	}

	public function testDestructorSavesCommands()
	{
		$cmds = new Commands($this->vfs->url(), [TestCommand::class]);
		$cmds->register($this->app);
		unset($cmds);

		$file = $this->vfs->url() . '/_commands.php';
		self::assertFileExists($file);
		self::assertEquals(
			<<<EOF
<?php return array (
  'test-command' => 'Magnum\\\\Console\\\\Fixture\\\\TestCommand',
);
EOF
			,
			file_get_contents($file)
		);
	}
}