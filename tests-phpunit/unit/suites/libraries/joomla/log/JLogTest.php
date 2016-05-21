<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/log/inspector.php';

/**
 * Test class for JLog.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Clear out the log instance.
		$log = new JLogInspector;
		JLog::setInstance($log);

		parent::tearDown();
	}

	/**
	 * Test the JLog::addLogEntry method to verify that if called directly it will route the entry to the
	 * appropriate loggers.  We use the echo logger here for easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLogEntry()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$this->expectOutputString("DEBUG: TESTING [deprecated]\n");
		$log->addLogEntry(new JLogEntry('TESTING', JLog::DEBUG, 'DePrEcAtEd'));
	}

	/**
	 * Test that if JLog::addLogger is called and no JLog instance has been instantiated yet, that one will
	 * be instantiated automatically and the logger will work accordingly.  We use the echo logger here for
	 * easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLoggerAutoInstantiation()
	{
		JLog::setInstance(null);

		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$this->expectOutputString("WARNING: TESTING [deprecated]\n");
		JLog::add(new JLogEntry('TESTING', JLog::WARNING, 'DePrEcAtEd'));
	}

	/**
	 * Test that if JLog::addLogger is called and no JLog instance has been instantiated yet, that one will
	 * be instantiated automatically and the logger will work accordingly.  We use the echo logger here for
	 * easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLoggerAutoInstantiationInvalidLogger()
	{
		// We are expecting an InvalidArgumentException to be thrown since we are trying to add a bogus logger.
		$this->setExpectedException('RuntimeException');

		JLog::setInstance(null);

		JLog::addLogger(array('logger' => 'foobar'), JLog::ALL);

		JLog::add(new JLogEntry('TESTING', JLog::WARNING, 'DePrEcAtEd'));
	}

	/**
	 * Test the JLog::findLoggers method to make sure given a category we are finding the correct loggers that
	 * have been added to JLog.  It is important to note that if a logger was added with no category, then it
	 * will be returned for all categories.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByCategory()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.

		// Note: 67d00c8f22f5859a1fd73835ee47e4d
		JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated');

		// Note: 09826310049345665887853e4688d89e
		JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::ALL, 'com_foo');

		// Note: 5099e81204381e68555c620cd8140421
		JLog::addLogger(array('text_file' => 'none.log'), JLog::ALL);

		// Note: 57604db2561c1c4492f5dfceed3d943c
		JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::ALL, array('deprecated', 'com_foo'));

		// Note: 5fbf17c78bfcd300debc791e01066128
		JLog::addLogger(array('text_file' => 'foobar-deprecated.log'), JLog::ALL, array('foobar', 'deprecated'));

		// Note: b5550c1aa36c1eaf77206565ec5f9021
		JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::ALL, array('transactions', 'paypal'));

		// Note: 916ed48d2f635431a93aee60c56b0219
		JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ALL, array('transactions'));

		// @todo remove: var_dump($log->lookup);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					'767d00c8f22f5859a1fd73835ee47e4d',
					'5099e81204381e68555c620cd8140421',
					'57604db2561c1c4492f5dfceed3d943c',
					'5fbf17c78bfcd300debc791e01066128',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'b5550c1aa36c1eaf77206565ec5f9021',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					'09826310049345665887853e4688d89e',
					'5099e81204381e68555c620cd8140421',
					'57604db2561c1c4492f5dfceed3d943c'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, 'transactions'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'b5550c1aa36c1eaf77206565ec5f9021',
					'916ed48d2f635431a93aee60c56b0219',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a category we are finding the correct loggers that
	 * have been added to JLog (using exclusion).  It is important to note that empty category can also be excluded.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testFindLoggersByNotCategory()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.

		// Note: 46c90979772c19bf707c0d8d6581cad5
		JLog::addLogger(array('text_file' => 'not_deprecated.log'), JLog::ALL, 'deprecated', true);

		// Note: 96ebc8ec99ccca7d8108232da1f35abe
		JLog::addLogger(array('text_file' => 'not_com_foo.log'), JLog::ALL, 'com_foo', true);

		// Note: 84c5af052b619356b9fdd2f5cefd90fd
		JLog::addLogger(array('text_file' => 'not_none.log'), JLog::ALL, '', true);

		// Note: 645f55d76f1d8bc00f79040d5bead8d6
		JLog::addLogger(array('text_file' => 'not_deprecated-com_foo.log'), JLog::ALL, array('deprecated', 'com_foo'), true);

		// Note: 07abacf4dc704fe78479149ad51bd044
		JLog::addLogger(array('text_file' => 'not_foobar-deprecated.log'), JLog::ALL, array('foobar', 'deprecated'), true);

		// Note: affc04af81476fbb5e19b2773a927ec6
		JLog::addLogger(array('text_file' => 'not_transactions-paypal.log'), JLog::ALL, array('transactions', 'paypal'), true);

		// Note: 1aa03749b113bc00fb030b6c5a67b6ec
		JLog::addLogger(array('text_file' => 'not_transactions.log'), JLog::ALL, array('transactions'), true);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					'96ebc8ec99ccca7d8108232da1f35abe',
					'84c5af052b619356b9fdd2f5cefd90fd',
					'affc04af81476fbb5e19b2773a927ec6',
					'1aa03749b113bc00fb030b6c5a67b6ec',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
					'46c90979772c19bf707c0d8d6581cad5',
					'96ebc8ec99ccca7d8108232da1f35abe',
					'84c5af052b619356b9fdd2f5cefd90fd',
					'645f55d76f1d8bc00f79040d5bead8d6',
					'07abacf4dc704fe78479149ad51bd044',
					'1aa03749b113bc00fb030b6c5a67b6ec'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					'46c90979772c19bf707c0d8d6581cad5',
					'84c5af052b619356b9fdd2f5cefd90fd',
					'07abacf4dc704fe78479149ad51bd044',
					'affc04af81476fbb5e19b2773a927ec6',
					'1aa03749b113bc00fb030b6c5a67b6ec'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, 'transactions'),
			$this->equalTo(
				array(
					'46c90979772c19bf707c0d8d6581cad5',
					'96ebc8ec99ccca7d8108232da1f35abe',
					'84c5af052b619356b9fdd2f5cefd90fd',
					'645f55d76f1d8bc00f79040d5bead8d6',
					'07abacf4dc704fe78479149ad51bd044'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::INFO, ''),
			$this->equalTo(
				array(
					'46c90979772c19bf707c0d8d6581cad5',
					'96ebc8ec99ccca7d8108232da1f35abe',
					'645f55d76f1d8bc00f79040d5bead8d6',
					'07abacf4dc704fe78479149ad51bd044',
					'affc04af81476fbb5e19b2773a927ec6',
					'1aa03749b113bc00fb030b6c5a67b6ec'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a priority we are finding the correct loggers that
	 * have been added to JLog.  It is important to test not only straight values but also bitwise combinations
	 * and the catch all JLog::ALL as registered loggers.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByPriority()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.

		// Note: 684e35a45ddd17c00024891e95c29046
		JLog::addLogger(array('text_file' => 'error.log'), JLog::ERROR);

		// Note: 3ab1ff5941725c3ed01e6dd1ff623415
		JLog::addLogger(array('text_file' => 'notice.log'), JLog::NOTICE);

		// Note: e16e9516d55213efd9255d8c9c13020b
		JLog::addLogger(array('text_file' => 'warning.log'), JLog::WARNING);

		// Note: d941cfc07f7641537991eaecaa8ea553
		JLog::addLogger(array('text_file' => 'error_warning.log'), JLog::ERROR | JLog::WARNING);

		// Note: a2fae4fb61ef676032361e47068deb9a
		JLog::addLogger(array('text_file' => 'all.log'), JLog::ALL);

		// Note: aaa7a0e4a4720ef7aed99ded3b764303
		JLog::addLogger(array('text_file' => 'all_except_debug.log'), JLog::ALL & ~JLog::DEBUG);

		// @todo remove: var_dump($log->lookup);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, null),
			$this->equalTo(
				array(
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, null),
			$this->equalTo(
				array(
					'3ab1ff5941725c3ed01e6dd1ff623415',
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, null),
			$this->equalTo(
				array(
					'a2fae4fb61ef676032361e47068deb9a'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, null),
			$this->equalTo(
				array(
					'e16e9516d55213efd9255d8c9c13020b',
					'd941cfc07f7641537991eaecaa8ea553',
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303'
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a priority and category we are finding the correct
	 * loggers that have been added to JLog.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByPriorityAndCategory()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.

		// Note: 767d00c8f22f5859a1fd73835ee47e4d
		JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated');

		// Note: 09826310049345665887853e4688d89e
		JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::DEBUG, 'com_foo');

		// Note: 5099e81204381e68555c620cd8140421
		JLog::addLogger(array('text_file' => 'none.log'), JLog::ERROR | JLog::CRITICAL | JLog::EMERGENCY);

		// Note: 57604db2561c1c4492f5dfceed3d943c
		JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::NOTICE | JLog::WARNING, array('deprecated', 'com_foo'));

		// Note: b5550c1aa36c1eaf77206565ec5f9021
		JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::INFO, array('transactions', 'paypal'));

		// Note: 916ed48d2f635431a93aee60c56b0219
		JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ERROR, array('transactions'));

		// @todo remove: var_dump($log->lookup);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					'767d00c8f22f5859a1fd73835ee47e4d',
					'5099e81204381e68555c620cd8140421',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array()
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					'09826310049345665887853e4688d89e',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::ERROR, 'transactions'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'916ed48d2f635431a93aee60c56b0219',
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::INFO, 'transactions'),
			$this->equalTo(
				array(
					'b5550c1aa36c1eaf77206565ec5f9021',
				)
			),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JLog::setInstance method to make sure that if we set a logger instance JLog is actually going
	 * to use it.  We accomplish this by setting an instance of JLogInspector and then performing some
	 * operations using JLog::addLogger() to alter the state of the internal instance.  We then check that the
	 * JLogInspector instance we created (and set) has the same values we would expect for lookup and configuration
	 * so we can assert that the operations we performed using JLog::addLogger() were actually performed on our
	 * instance of JLogInspector that was set.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testSetInstance()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a logger to the JLog object.
		JLog::addLogger(array('logger' => 'w3c'));

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			'55202c195e23298813df4292c827b241' => array('logger' => 'w3c')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			'55202c195e23298813df4292c827b241' => (object) array('priorities' => JLog::ALL, 'categories' => array(), 'exclude' => false)
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertEquals(
			$expectedConfigurations,
			$log->configurations
		);

		$this->assertEquals(
			$expectedLookup,
			$log->lookup
		);

		$this->assertEquals(
			$expectedLoggers,
			$log->loggers
		);

		// Start over so we test that it actually sets the instance appropriately.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a logger to the JLog object.
		JLog::addLogger(array('logger' => 'database', 'db_type' => 'mysqli', 'db_table' => '#__test_table'), JLog::ERROR);

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			'2c6b1817bcb404c50f7bbbe9e6ae1429' => array('logger' => 'database', 'db_type' => 'mysqli', 'db_table' => '#__test_table')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			'2c6b1817bcb404c50f7bbbe9e6ae1429' => (object) array('priorities' => JLog::ERROR, 'categories' => array(), 'exclude' => false)
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertEquals(
			$expectedConfigurations,
			$log->configurations
		);

		$this->assertEquals(
			$expectedLookup,
			$log->lookup
		);

		$this->assertEquals(
			$expectedLoggers,
			$log->loggers
		);
	}
}
