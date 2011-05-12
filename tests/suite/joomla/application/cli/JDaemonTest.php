<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/cli/daemon.php';

/**
 * Test class for JDaemon.
 */
class JDaemonTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// Include the inspector.
		include_once JPATH_TESTS.'/suite/joomla/application/cli/TestStubs/JDaemon_Inspector.php';

		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);
	}

	/**
	 * Test the JDaemon::writeProcessIdFile method.
	 */
	public function testWriteProcessIdFile()
	{
		// Get a new JDaemonInspector instance.
		$daemon = new JDaemonInspector();

		// Get the current process id and set it to the daemon instance.
		$pid = (int) posix_getpid();
		$daemon->processId = $pid;

		// Execute the writeProcessIdFile method.
		$daemon->writeProcessIdFile();

		// Check the value of the file.
		$this->assertEquals($pid, (int) file_get_contents($daemon->get('application_pid_file')), 'Line: '.__LINE__);

		// Check the permissions on the file.
		$this->assertEquals('0644', substr(decoct(fileperms($daemon->get('application_pid_file'))), 1), 'Line: '.__LINE__);
	}
}
