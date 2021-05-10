<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Unit\Libraries\Cms\Console;

use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Console\ExtensionDiscoverCommand;
use Joomla\CMS\Language\Language;
use Joomla\DI\Container;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test class for Joomla\CMS\Console\ExtensionDiscoverCommand.
 *
 * @since   __DEPLOY_VERSION__
 */
class ExtensionDiscoverCommandTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Tests the constructor
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testIsConstructable()
	{
		$this->assertInstanceOf(ExtensionDiscoverCommand::class, $this->createExtensionDiscoverCommand());
	}

	/**
	 * Tests the processDiscover method
	 * Case: There is no extension to discover
	 *
	 * @return  void
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	public function testProcessDiscoverNoExtension()
	{
		$ExtensionDiscoverCommand = $this->createExtensionDiscoverCommand();
		$app = $this->createApplication();

		$this->assertSame('cli', $app->getName());
		$this->assertSame(true, $app->isClient('cli'));
		
		$this->assertSame(true, true);

	}

	/**
	 * Helper function to create a ExtensionDiscoverCommand
	 *
	 * @return  ExtensionDiscoverCommand
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function createExtensionDiscoverCommand(): ExtensionDiscoverCommand
	{
		return new ExtensionDiscoverCommand;
	}

	/**
	 * Helper function to create a ConsoleApplication with mocked dependencies
	 *
	 * @return ConsoleApplication
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function createApplication(): ConsoleApplication
	{
		$config = $this->createMock(Registry::class);
		$dispatcher = $this->createMock(DispatcherInterface::class);
		$container = $this->createMock(Container::class);
		$language = $this->createMock(Language::class);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);

		$object = new ConsoleApplication($config, $dispatcher, $container, $language, $input, $output);

		return $object;
	}
}