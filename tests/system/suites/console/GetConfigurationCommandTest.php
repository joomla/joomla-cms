<?php
/**
 * @package    Joomla.SystemTest
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class GetConfigurationCommandTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @testdox Output from  `php cli/joomla.php help config:get` contains usage instructions
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandOutputContainsUsageInformation()
	{
		exec('php cli/joomla.php help config:get', $parts);
		$parts = array_flip($parts);
		$this->assertArrayHasKey("Usage:", $parts, 'Message should contain usage instructions');
	}

	/**
	 * Tests if command did not return a value for the option provided when option doesn't exist
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandDidNotFoundTheConfigOption()
	{
		exec('php cli/joomla.php config:get qwerty', $parts);
		$output = implode("\n", $parts);
		$this->assertContains("Can't find option *qwerty* in configuration list", $output);
	}

	/**
	 * Test if command returns the value of the option provided
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandFoundTheConfigOption()
	{
		exec('php cli/joomla.php config:get sitename', $parts);
		$output = implode("\n", $parts);
		$this->assertContains("Option", $output);
		$this->assertContains("Value", $output);
		$this->assertContains("sitename", $output);
	}
}
