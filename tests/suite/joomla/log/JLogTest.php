<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Log
 */

require_once JPATH_PLATFORM.'/joomla/log/log.php';
require_once JPATH_PLATFORM.'/joomla/log/logentry.php';
require_once JPATH_PLATFORM.'/joomla/log/logger.php';

/**
 * Test class for JLog.
 */
class JLogTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @access protected
	 */
	protected $inspector;

	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		include_once 'TestStubs/JLog_Inspector.php';
	}

	/**
	 * Test the JLog::setInstance method.
	 */
	public function testSetInstance()
	{
		$this->inspector = new JLogInspector;
		JLog::setInstance($this->inspector);

		// Add a logger to the JLog object.
		JLog::addLogger(array('logger' => 'w3c'));

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			'55202c195e23298813df4292c827b241' => array('logger' => 'w3c')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			'55202c195e23298813df4292c827b241' => (object) array('priorities' => JLog::ALL, 'categories' => array())
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertThat(
			$this->inspector->configurations,
			$this->equalTo($expectedConfigurations),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->lookup,
			$this->equalTo($expectedLookup),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->loggers,
			$this->equalTo($expectedLoggers),
			'Line: '.__LINE__.'.'
		);

		// Start over so we test that it actually sets the instance appropriately.
		$this->inspector = new JLogInspector;
		JLog::setInstance($this->inspector);

		// Add a logger to the JLog object.
		JLog::addLogger(array('logger' => 'database', 'db_type' => 'mysql', 'db_table' => '#__test_table'), JLog::ERROR);

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			'b67483f5ba61450d173aae527fa4163f' => array('logger' => 'database', 'db_type' => 'mysql', 'db_table' => '#__test_table')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			'b67483f5ba61450d173aae527fa4163f' => (object) array('priorities' => JLog::ERROR, 'categories' => array())
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertThat(
			$this->inspector->configurations,
			$this->equalTo($expectedConfigurations),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->lookup,
			$this->equalTo($expectedLookup),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->loggers,
			$this->equalTo($expectedLoggers),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JLog::findLoggers method.
	 */
	public function testFindLoggersByPriority()
	{
		// First let's test a set of priorities.
		$this->inspector = new JLogInspector;
		JLog::setInstance($this->inspector);

		// Add a loggers to the JLog object.
		JLog::addLogger(array('text_file' => 'error.log'), JLog::ERROR); // 684e35a45ddd17c00024891e95c29046
		JLog::addLogger(array('text_file' => 'notice.log'), JLog::NOTICE); // 3ab1ff5941725c3ed01e6dd1ff623415
		JLog::addLogger(array('text_file' => 'warning.log'), JLog::WARNING); // e16e9516d55213efd9255d8c9c13020b
		JLog::addLogger(array('text_file' => 'error_warning.log'), JLog::ERROR | JLog::WARNING); // d941cfc07f7641537991eaecaa8ea553
		JLog::addLogger(array('text_file' => 'all.log'), JLog::ALL); // a2fae4fb61ef676032361e47068deb9a
		JLog::addLogger(array('text_file' => 'all_except_debug.log'), JLog::ALL & ~JLog::DEBUG); // aaa7a0e4a4720ef7aed99ded3b764303
		//var_dump($this->inspector->lookup);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::EMERGENCY, null),
			$this->equalTo(
				array(
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::NOTICE, null),
			$this->equalTo(
				array(
					'3ab1ff5941725c3ed01e6dd1ff623415',
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303'
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::DEBUG, null),
			$this->equalTo(
				array(
					'a2fae4fb61ef676032361e47068deb9a'
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::WARNING, null),
			$this->equalTo(
				array(
					'e16e9516d55213efd9255d8c9c13020b',
					'd941cfc07f7641537991eaecaa8ea553',
					'a2fae4fb61ef676032361e47068deb9a',
					'aaa7a0e4a4720ef7aed99ded3b764303'
				)),
			'Line: '.__LINE__.'.'
		);

	}

	/**
	 * Test the JLog::findLoggers method.
	 */
	public function testFindLoggersByCategory()
	{
		// First let's test a set of priorities.
		$this->inspector = new JLogInspector;
		JLog::setInstance($this->inspector);

		// Add a loggers to the JLog object.
		JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated'); // 767d00c8f22f5859a1fd73835ee47e4d
		JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::ALL, 'com_foo'); // 09826310049345665887853e4688d89e
		JLog::addLogger(array('text_file' => 'none.log'), JLog::ALL); // 5099e81204381e68555c620cd8140421
		JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::ALL, array('deprecated', 'com_foo')); // 57604db2561c1c4492f5dfceed3d943c
		JLog::addLogger(array('text_file' => 'foobar-deprecated.log'), JLog::ALL, array('foobar', 'deprecated')); // 5fbf17c78bfcd300debc791e01066128
		JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::ALL, array('transactions', 'paypal')); // b5550c1aa36c1eaf77206565ec5f9021
		JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ALL, array('transactions')); // 916ed48d2f635431a93aee60c56b0219
		//var_dump($this->inspector->lookup);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					'767d00c8f22f5859a1fd73835ee47e4d',
					'5099e81204381e68555c620cd8140421',
					'57604db2561c1c4492f5dfceed3d943c',
					'5fbf17c78bfcd300debc791e01066128',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'b5550c1aa36c1eaf77206565ec5f9021',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					'09826310049345665887853e4688d89e',
					'5099e81204381e68555c620cd8140421',
					'57604db2561c1c4492f5dfceed3d943c'
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::WARNING, 'transactions'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'b5550c1aa36c1eaf77206565ec5f9021',
					'916ed48d2f635431a93aee60c56b0219',
				)),
			'Line: '.__LINE__.'.'
		);

	}

	/**
	 * Test the JLog::findLoggers method.
	 */
	public function testFindLoggersByPriorityAndCategory()
	{
		// First let's test a set of priorities.
		$this->inspector = new JLogInspector;
		JLog::setInstance($this->inspector);

		// Add a loggers to the JLog object.
		JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated'); // 767d00c8f22f5859a1fd73835ee47e4d
		JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::DEBUG, 'com_foo'); // 09826310049345665887853e4688d89e
		JLog::addLogger(array('text_file' => 'none.log'), JLog::ERROR | JLog::CRITICAL | JLog::EMERGENCY); // 5099e81204381e68555c620cd8140421
		JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::NOTICE | JLog::WARNING, array('deprecated', 'com_foo')); // 57604db2561c1c4492f5dfceed3d943c
		JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::INFO, array('transactions', 'paypal')); // b5550c1aa36c1eaf77206565ec5f9021
		JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ERROR, array('transactions')); // 916ed48d2f635431a93aee60c56b0219
		//var_dump($this->inspector->lookup);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					'767d00c8f22f5859a1fd73835ee47e4d',
					'5099e81204381e68555c620cd8140421',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					'09826310049345665887853e4688d89e',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::ERROR, 'transactions'),
			$this->equalTo(
				array(
					'5099e81204381e68555c620cd8140421',
					'916ed48d2f635431a93aee60c56b0219',
				)),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$this->inspector->findLoggers(JLog::INFO, 'transactions'),
			$this->equalTo(
				array(
					'b5550c1aa36c1eaf77206565ec5f9021',
				)),
			'Line: '.__LINE__.'.'
		);

	}
}
