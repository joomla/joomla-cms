<?php
/**
 * @package    Joomla.SystemTest
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class CoreInstallCommandTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @testdox Output from  `joomla help core:install` contains usage instructions
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandOutputContainsUsageInformation()
	{
		exec('php cli/joomla.php help core:install', $parts);
		$parts = array_flip($parts);

		$this->assertArrayHasKey("Usage:", $parts, 'Message should contain usage instructions');
	}

	/**
	 * @testdox Tests if command fails because configuration file already exists
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfInstallFailsWhenConfigurationExists()
	{
		exec('php cli/joomla.php core:install', $parts);
		$result = implode("\n", $parts);

		$this->assertContains("Joomla is already installed and set up", $result);
	}
}
