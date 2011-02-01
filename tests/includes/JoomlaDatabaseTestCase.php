<?php
/**
 * JoomlaTestCase.php -- unit testing file for JUtilities
 *
 * @version		$Id: JoomlaDatabaseTestCase.php 20196 2011-01-09 02:40:25Z ian $
 * @package	Joomla.UnitTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';

/**
 * Test case class for Joomla Unit Testing
 *
 * @package	Joomla.UnitTest
 *
 */
abstract class JoomlaDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{

	public static $database;

	public static $dbo;

	/**
	 * @var factoryState
	 */
	protected $factoryState = array ();

	/**
	 * @var errorState
	 */
	protected $savedErrorState;

	/**
	 * @var actualError
	 */
	protected static $actualError;

	/**
	 * Saves the current state of the JError error handlers.
	 *
	 * @return	void
	 */
	protected function saveErrorHandlers()
	{
		$this->savedErrorState = array ();
		$this->savedErrorState[E_NOTICE] = JError :: getErrorHandling(E_NOTICE);
		$this->savedErrorState[E_WARNING] = JError :: getErrorHandling(E_WARNING);
		$this->savedErrorState[E_ERROR] = JError :: getErrorHandling(E_ERROR);
	}

	public static function setUpBeforeClass()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		// Load the config if available.
		@ include_once JPATH_TESTS . '/config.php';
		if (class_exists('JTestConfig')) {
			$config = new JTestConfig;
		}

		if (!is_object(self :: $dbo)) {
			$options = array (
				'driver' => isset ($config) ? $config->dbtype : 'mysql',
				'host' => isset ($config) ? $config->host : '127.0.0.1',
				'user' => isset ($config) ? $config->user : 'utuser',
				'password' => isset ($config) ? $config->password : 'ut1234',
				'database' => isset ($config) ? $config->db : 'joomla_ut',
				'prefix' => isset ($config) ? $config->dbprefix : 'jos_'
			);

			self :: $dbo = JDatabase :: getInstance($options);

			if (JError :: isError(self :: $dbo)) {
				//ignore errors
				define('DB_NOT_AVAILABLE', true);
			}
		}
		self :: $database = JFactory :: $database;
		JFactory :: $database = self :: $dbo;
	}

	public static function tearDownAfterClass()
	{
		//JFactory::$database = self::$database;
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @param	array	araay of values and options to set the handlers
	 *
	 * @return	void
	 */
	protected function setErrorHandlers($errorHandlers)
	{
		$mode = null;
		$options = null;

		foreach ($errorHandlers as $type => $params) {
			$mode = $params['mode'];
			if (isset ($params['options'])) {
				JError :: setErrorHandling($type, $mode, $params['options']);
			} else {
				JError :: setErrorHandling($type, $mode);
			}
		}
	}

	/**
	 * Sets the JError error handlers to callback mode and points them at the test
	 * logging method.
	 *
	 * @return	void
	 */
	protected function setErrorCallback($testName)
	{
		$callbackHandlers = array (
			E_NOTICE => array (
				'mode' => 'callback',
				'options' => array (
					$testName,
					'errorCallback'
				)
			),
			E_WARNING => array (
				'mode' => 'callback',
				'options' => array (
					$testName,
					'errorCallback'
				)
			),
			E_ERROR => array (
				'mode' => 'callback',
				'options' => array (
					$testName,
					'errorCallback'
				)
			),

		);
		$this->setErrorHandlers($callbackHandlers);
	}

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param	JException	The JException object from JError
	 *
	 * @return	bool	To not continue with JError processing
	 */
	static function errorCallback($error)
	{
		return false;
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function saveFactoryState()
	{
		$this->savedFactoryState['application'] = JFactory :: $application;
		$this->savedFactoryState['config'] = JFactory :: $config;
		$this->savedFactoryState['session'] = JFactory :: $session;
		$this->savedFactoryState['language'] = JFactory :: $language;
		$this->savedFactoryState['document'] = JFactory :: $document;
		$this->savedFactoryState['acl'] = JFactory :: $acl;
		//$this->savedFactoryState['database'] = JFactory::$database;
		$this->savedFactoryState['mailer'] = JFactory :: $mailer;
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return void
	 */
	protected function restoreFactoryState()
	{
		JFactory :: $application = $this->savedFactoryState['application'];
		JFactory :: $config = $this->savedFactoryState['config'];
		JFactory :: $session = $this->savedFactoryState['session'];
		JFactory :: $language = $this->savedFactoryState['language'];
		JFactory :: $document = $this->savedFactoryState['document'];
		JFactory :: $acl = $this->savedFactoryState['acl'];
		//JFactory::$database = $this->savedFactoryState['database'];
		JFactory :: $mailer = $this->savedFactoryState['mailer'];
	}

	/**
	 * Sets the connection to the database
	 *
	 * @return connection
	 */
	protected function getConnection()
	{
		// Load the config if available.
		@ include_once JPATH_TESTS . '/config.php';
		if (class_exists('JTestConfig')) {
			$config = new JTestConfig;
		}

		$options = array (
			'driver' => ((isset ($config)) && ($config->dbtype != 'mysqli')) ? $config->dbtype : 'mysql',
			'host' => isset ($config) ? $config->host : '127.0.0.1',
			'user' => isset ($config) ? $config->user : 'utuser',
			'password' => isset ($config) ? $config->password : 'ut1234',
			'database' => isset ($config) ? $config->db : 'joomla_ut',
			'prefix' => isset ($config) ? $config->dbprefix : 'jos_'
		);

		$pdo = new PDO($options['driver'].':host='.$options['host'].';dbname='.$options['database'], $options['user'], $options['password']);
		return $this->createDefaultDBConnection($pdo, $options['database']);
	}
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_BASE . '/tests/unit/stubs/test.xml');
	}
}
