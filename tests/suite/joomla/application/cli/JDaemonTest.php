<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/cli/daemon.php';
include_once __DIR__.'/TestStubs/JDaemon_Inspector.php';

/**
 * Test class for JDaemon.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.1
 */
class JDaemonTest extends JoomlaTestCase
{
	/**
	 * An instance of a JDaemon inspector.
	 *
	 * @var    JDaemonInspector
	 * @since  11.3
	 */
	protected $inspector;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		// Skip this test suite if PCNTL  extension is not available
		if(!extension_loaded("PCNTL")){
		   $this->markTestSkipped('The PCNTL extension is not available.');
		}

		// Get a new JDaemonInspector instance.
		$this->inspector = new JDaemonInspector;

		//$this->config->set('max_memory_limit', '2048M');

		// We are only coupled to Document and Language in JFactory.
		$this->saveFactoryState();

		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.3
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		parent::tearDown();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDownAfterClass()
	 * @since   11.3
	 */
	 public static function tearDownAfterClass()
	 {
		 ini_restore('memory_limit');
		 parent::tearDownAfterClass();
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
