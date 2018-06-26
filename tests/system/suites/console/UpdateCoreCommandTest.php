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
	 * @testdox Output from  `joomla help extension:remove` contains usage instructions
	 *
	 * @since 4.0
	 */
	public function testIfCommandOutputContainsUsageInformation()
	{
		exec('php cli/joomla.php help core:update', $parts);
		$parts = array_flip($parts);

		$this->assertArrayHasKey("Usage:", $parts, 'Message should contain usage instructions');
	}

	/**
	 * @testdox Output from  `joomla help extension:remove` contains usage instructions
	 *
	 * @since 4.0
	 */
	public function testIfCommandOutput()
	{
		$commandTester = new CommandTester(new \Joomla\CMS\Console\UpdateCoreCommand);
		var_dump($commandTester);
	}
}
