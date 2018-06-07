<?php
/**
 * @package    Joomla.SystemTest
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class CheckJoomlaUpdatesCommandTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @testdox Output from  `joomla help check-updates` contains usage instructions
	 *
	 * @since 4.0
	 */
	public function testIfCommandOutputContainsUsageInformation()
	{
		exec('php cli/joomla.php help check-updates', $parts);
		$parts = array_flip($parts);
		$this->assertArrayHasKey("Usage:", $parts, 'Message should contain usage instructions');
	}

	/**
	 * @testdox 'check-updates' tells whether there is update or not
	 *
	 * @since 4.0
	 */
	public function testIfThereIsJoomlaUpdate()
	{
		exec('php cli/joomla.php check-updates', $result, $code);
		$possible_results = ['[NOTE] New Joomla Version', '[OK] You already have the latest Joomla version'];
		$result = implode("\n", $result);
		$bool = (strpos($result, $possible_results[0]) || strpos($result, $possible_results[1]));
		$this->assertEquals(true, $bool, 'Checking of Update not successful');
	}
}
