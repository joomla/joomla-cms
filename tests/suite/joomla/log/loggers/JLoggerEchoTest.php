<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/log/loggers/echo.php';

/**
 * Test class for JLoggerEcho.
 */
class JLoggerEchoTest extends PHPUnit_Extensions_OutputTestCase
{
	/**
	 * Test the JLoggerEcho::addEntry method.
	 */
	public function testAddEntry01()
	{
		// Create bogus config.
		$config = array();

		// Get an instance of the logger.
		$logger = new JLoggerEcho($config);

		$this->expectOutputString("DEBUG: TESTING [deprecated]\n");
		$logger->addEntry(new JLogEntry('TESTING', JLog::DEBUG, 'DePrEcAtEd'));
	}

	/**
	 * Test the JLoggerEcho::addEntry method.
	 */
	public function testAddEntry02()
	{
		// Create bogus config.
		$config = array();

		// Get an instance of the logger.
		$logger = new JLoggerEcho($config);

		$this->expectOutputString("CRITICAL: TESTING2 [bam]\n");
		$logger->addEntry(new JLogEntry('TESTING2', JLog::CRITICAL, 'BAM'));
	}

	/**
	 * Test the JLoggerEcho::addEntry method.
	 */
	public function testAddEntry03()
	{
		// Create bogus config.
		$config = array();

		// Get an instance of the logger.
		$logger = new JLoggerEcho($config);

		$this->expectOutputString("ERROR: Testing3\n");
		$logger->addEntry(new JLogEntry('Testing3', JLog::ERROR));
	}

	/**
	 * Test the JLoggerEcho::addEntry method.
	 */
	public function testAddEntry04()
	{
		// Create bogus config.
		$config = array();

		// Get an instance of the logger.
		$logger = new JLoggerEcho($config);

		$this->expectOutputString("INFO: Testing 4\n");
		$logger->addEntry(new JLogEntry('Testing 4'));
	}
}
