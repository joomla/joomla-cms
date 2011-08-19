<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/MysqlXmlDataSet.php';

/**
 * Test case class for Joomla Unit Testing
 *
 * @package  Joomla.UnitTest
 */
abstract class JoomlaDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * The saved database state.
	 *
	 * @var    JDatabase
	 * @since  11.1
	 */
	public static $database;

	/**
	 * The active database used by the test.
	 *
	 * @var    JDatabase
	 * @since  11.1
	 */
	public static $dbo;

	/**
	 * The saved factory state.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $factoryState = array();

	/**
	 * @var    array
	 * @since  11.1
	 */
	protected $savedErrorState;

	/**
	 * Not used.
	 *
	 * @var    unknown
	 * @since  11.1
	 */
	protected static $actualError;

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   object  $mockObject  The mock object.
	 * @param   array   $array       An associative array of methods to mock with return values:<br />
	 *                               string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject
				->expects($this->any())
				->method($method)
				->will(
					$this->returnValue($return)
				);
		}
	}

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   object  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   array   $array       An array of methods names to mock with callbacks.
	 *                               This method assumes that the mock callback is named {mock}{method name}.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function assignMockCallbacks($mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback = $method;
			}
			else
			{
				$methodName = $method;
				$callback = array(get_called_class(), 'mock'.$method);
			}

			$mockObject
				->expects($this->any())
				->method($methodName)
				->will($this->returnCallback($callback));
		}
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 *
	 * @since   11.3
	 */
    protected function getSetUpOperation()
    {
    	// Required given the use of InnoDB contraints.
        return new PHPUnit_Extensions_Database_Operation_Composite(
        	array(
            	PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL(),
            	PHPUnit_Extensions_Database_Operation_Factory::INSERT()
        	)
        );
    }

	/**
	 * Returns the database operation executed in test cleanup.
	 *
	 * @return  PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 *
	 * @since   11.3
	 */
    protected function getTearDownOperation()
    {
    	// Required given the use of InnoDB contraints.
        return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }

	/**
	 * Saves the current state of the JError error handlers.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function saveErrorHandlers()
	{
		$this->savedErrorState = array();
		$this->savedErrorState[E_NOTICE] = JError::getErrorHandling(E_NOTICE);
		$this->savedErrorState[E_WARNING] = JError::getErrorHandling(E_WARNING);
		$this->savedErrorState[E_ERROR] = JError::getErrorHandling(E_ERROR);
	}

	/**
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function setUpBeforeClass()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		// Load the config if available.
		@include_once JPATH_TESTS . '/config.php';
		if (class_exists('JTestConfig')) {
			$config = new JTestConfig;
		}

		if (!is_object(self::$dbo)) {
			$options = array (
				'driver' => isset ($config) ? $config->dbtype : 'mysql',
				'host' => isset ($config) ? $config->host : '127.0.0.1',
				'user' => isset ($config) ? $config->user : 'utuser',
				'password' => isset ($config) ? $config->password : 'ut1234',
				'database' => isset ($config) ? $config->db : 'joomla_ut',
				'prefix' => isset ($config) ? $config->dbprefix : 'jos_'
			);

			try {
				self::$dbo = JDatabase::getInstance($options);
			}
			catch (JDatabaseException $e) {
			}

			if (JError::isError(self::$dbo)) {
				//ignore errors
				define('DB_NOT_AVAILABLE', true);
			}
		}

		self::$database = JFactory::$database;
		JFactory::$database = self::$dbo;
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		if (defined('DB_NOT_AVAILABLE')) {
			$this->markTestSkipped();
		}

		parent::setUp();
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function tearDownAfterClass()
	{
		//JFactory::$database = self::$database;
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @param   array  $errorHandlers  araay of values and options to set the handlers
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setErrorHandlers($errorHandlers)
	{
		$mode = null;
		$options = null;

		foreach ($errorHandlers as $type => $params)
		{
			$mode = $params['mode'];

			if (isset ($params['options'])) {
				JError::setErrorHandling($type, $mode, $params['options']);
			}
			else {
				JError::setErrorHandling($type, $mode);
			}
		}
	}

	/**
	 * Sets the JError error handlers to callback mode and points them at the test
	 * logging method.
	 *
	 * @return	void
	 *
	 * @since   11.1
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
	 *
	 * @since   11.1
	 */
	static function errorCallback($error)
	{
		return false;
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function saveFactoryState()
	{
		$this->savedFactoryState['application'] = JFactory::$application;
		$this->savedFactoryState['config'] = JFactory::$config;
		$this->savedFactoryState['session'] = JFactory::$session;
		$this->savedFactoryState['language'] = JFactory::$language;
		$this->savedFactoryState['document'] = JFactory::$document;
		$this->savedFactoryState['acl'] = JFactory::$acl;
		//$this->savedFactoryState['database'] = JFactory::$database;
		$this->savedFactoryState['mailer'] = JFactory::$mailer;
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application = $this->savedFactoryState['application'];
		JFactory::$config = $this->savedFactoryState['config'];
		JFactory::$session = $this->savedFactoryState['session'];
		JFactory::$language = $this->savedFactoryState['language'];
		JFactory::$document = $this->savedFactoryState['document'];
		JFactory::$acl = $this->savedFactoryState['acl'];
		//JFactory::$database = $this->savedFactoryState['database'];
		JFactory::$mailer = $this->savedFactoryState['mailer'];
	}

	/**
	 * Sets the connection to the database
	 *
	 * @return  connection
	 *
	 * @since   11.1
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
	 * @return  xml dataset
	 *
	 * @since   11.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_BASE . '/tests/unit/stubs/test.xml');
	}

	/**
	 * Gets a mock application object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	protected function getMockApplication()
	{
		require_once JPATH_TESTS.'/suite/joomla/application/JApplicationMock.php';

		return JApplicationGlobalMock::create($this);
	}

	/**
	 * Gets a mock session object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	protected function getMockSession()
	{
		require_once JPATH_TESTS.'/suite/joomla/session/JSessionMock.php';

		return JSessionGlobalMock::create($this);
	}
}
