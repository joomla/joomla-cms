<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractDaemonApplication;
use Joomla\Registry\Registry;
use Joomla\Test\TestHelper;

include_once __DIR__ . '/Stubs/ConcreteDaemon.php';

/**
 * Test class for Joomla\Application\Daemon.
 *
 * @since  1.0
 */
class AbstractDaemonApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of a Daemon inspector.
	 *
	 * @var    ConcreteDaemon
	 * @since  1.0
	 */
	protected $inspector;

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDownAfterClass()
	 * @since   1.0
	 */
	public static function tearDownAfterClass()
	{
		$pidPath = JPATH_ROOT . '/japplicationdaemontest.pid';

		if (file_exists($pidPath))
		{
			unlink($pidPath);
		}

		ini_restore('memory_limit');
		parent::tearDownAfterClass();
	}

	/**
	 * Tests the Joomla\Application\Daemon::changeIdentity method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testChangeIdentity()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::daemonize method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testDaemonize()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::fork method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testFork()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::gc method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGc()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::isActive method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testIsActive()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadConfiguration()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::setupSignalHandlers method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetupSignalHandlers()
	{
		$this->inspector->setClassSignals(array('SIGTERM', 'SIGHUP', 'SIGFOOBAR123'));
		$return = $this->inspector->setupSignalHandlers();

		$this->assertThat(
			count($this->inspector->setupSignalHandlers),
			$this->equalTo(2),
			'Check that only the two valid signals are setup.'
		);
		$this->assertThat(
			$return,
			$this->equalTo(true),
			'Check that only setupSignalHandlers return is true.'
		);
	}

	/**
	 * Tests the Joomla\Application\Daemon::setupSignalHandlers method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetupSignalHandlersFailure()
	{
		ConcreteDaemon::$pcntlSignal = false;
		$this->inspector->setClassSignals(array('SIGTERM', 'SIGHUP', 'SIGFOOBAR123'));
		$return = $this->inspector->setupSignalHandlers();

		$this->assertThat(
			count($this->inspector->setupSignalHandlers),
			$this->equalTo(0),
			'Check that no signals are setup.'
		);
		$this->assertThat(
			$return,
			$this->equalTo(false),
			'Check that only setupSignalHandlers return is false.'
		);
	}

	/**
	 * Tests the Joomla\Application\Daemon::shutdown method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testShutdown()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::signal method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSignal()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::execute method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testExecute()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\Daemon::writeProcessIdFile method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testWriteProcessIdFile()
	{
		$pidPath = JPATH_ROOT . '/japplicationdaemontest.pid';

		if (file_exists($pidPath))
		{
			unlink($pidPath);
		}

		// We set a custom process id file path so that we don't interfere
		// with other tests that are running on a system
		$this->inspector->set('application_pid_file', $pidPath);

		// Get the current process id and set it to the daemon instance.
		$pid = (int) posix_getpid();
		$this->inspector->setClassProperty('processId', $pid);

		// Execute the writeProcessIdFile method.
		$this->inspector->writeProcessIdFile();

		// Check the value of the file.
		$this->assertEquals(
			$pid,
			(int) file_get_contents($this->inspector->getClassProperty('config')->get('application_pid_file')),
			'Line: ' . __LINE__
		);

		// Check the permissions on the file.
		$this->assertEquals(
			'0644',
			substr(decoct(fileperms($this->inspector->getClassProperty('config')->get('application_pid_file'))), 1),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		// Skip this test suite if PCNTL extension is not available
		if (!extension_loaded('PCNTL'))
		{
			$this->markTestSkipped('The PCNTL extension is not available.');
		}

		// Get a new ConcreteDaemon instance.
		$this->inspector = new ConcreteDaemon;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   1.0
	 */
	protected function tearDown()
	{
		// Reset some daemon inspector static settings.
		ConcreteDaemon::$pcntlChildExitStatus = 0;
		ConcreteDaemon::$pcntlFork = 0;
		ConcreteDaemon::$pcntlSignal = true;
		ConcreteDaemon::$pcntlWait = 0;
	}
}
