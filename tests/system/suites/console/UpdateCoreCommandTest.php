<?php
/**
 * @package    Joomla.SystemTest
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Symfony\Component\Console\Tester\CommandTester;

class UpdateCoreCommandTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @testdox Output from  `php cli/joomla.php help core:update` contains usage instructions
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandOutputContainsUsageInformation()
	{
		exec('php cli/joomla.php help core:update', $parts);
		$parts = array_flip($parts);

		$this->assertArrayHasKey("Usage:", $parts, 'Message should contain usage instructions');
	}


	/**
	 * @testdox Output from  `php cli/joomla.php core:update` outputs expected information
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function testIfCommandOutputExpectedInformation()
	{
		exec('php cli/joomla.php core:update', $parts);
		$result = implode("\n", $parts);
		$bool = strpos($result, 'Update cannot be performed') || strpos($result, 'Joomla core updated successfully');

		$this->assertTrue($bool, 'Update core command did exit with expected information.');
	}
}
