<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Console
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Unit\Libraries\Cms\Console;

use Joomla\CMS\Console\ExtensionDiscoverInstallCommand;
use Joomla\Database\DatabaseInterface;

/**
 * Test class for Joomla\CMS\Console\ExtensionDiscoverInstallCommand.
 *
 * @since   __DEPLOY_VERSION__
 */
class ExtensionDiscoverInstallCommandTest extends \PHPUnit\Framework\TestCase
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
		$this->assertInstanceOf(ExtensionDiscoverInstallCommand::class, $this->createExtensionDiscoverInstallCommand());
	}

	/**
	 * Helper function to create a ExtensionDiscoverInstallCommand
	 *
	 * @return  ExtensionDiscoverInstallCommand
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function createExtensionDiscoverInstallCommand(): ExtensionDiscoverInstallCommand
	{
		$db = $this->createMock(DatabaseInterface::class);
		return new ExtensionDiscoverInstallCommand($db);
	}
}