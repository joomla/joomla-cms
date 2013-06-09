<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/MysqlXmlDataSet.php';

/**
 * Abstract test case class for database testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCaseDatabase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * @var    JDatabaseDriver  The active database driver being used for the tests.
	 * @since  12.1
	 */
	protected static $driver;

	/**
	 * @var    JDatabaseDriver  The saved database driver to be restored after these tests.
	 * @since  12.1
	 */
	private static $_stash;

	/**
	 * @var         array  JError handler state stashed away to be restored later.
	 * @deprecated  13.1
	 * @since       12.1
	 */
	private $_stashedErrorState = array();

	/**
	 * @var    array  Various JFactory static instances stashed away to be restored later.
	 * @since  12.1
	 */
	private $_stashedFactoryState = array(
		'application' => null,
		'config' => null,
		'dates' => null,
		'session' => null,
		'language' => null,
		'document' => null,
		'acl' => null,
		'mailer' => null
	);

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param   JException  $error  The JException object from JError
	 *
	 * @return	bool	To not continue with JError processing
	 *
	 * @since   12.1
	 */
	public static function errorCallback($error)
	{
		return false;
	}

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function setUpBeforeClass()
	{
		// We always want the default database test case to use an SQLite memory database.
		$options = array(
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => 'jos_'
		);

		try
		{
			// Attempt to instantiate the driver.
			self::$driver = JDatabaseDriver::getInstance($options);

			// Create a new PDO instance for an SQLite memory database and load the test schema into it.
			$pdo = new PDO('sqlite::memory:');
			$pdo->exec(file_get_contents(JPATH_TESTS . '/schema/ddl.sql'));

			// Set the PDO instance to the driver using reflection whizbangery.
			TestReflection::setValue(self::$driver, 'connection', $pdo);
		}
		catch (RuntimeException $e)
		{
			self::$driver = null;
		}

		// If for some reason an exception object was returned set our database object to null.
		if (self::$driver instanceof Exception)
		{
			self::$driver = null;
		}

		// Setup the factory pointer for the driver and stash the old one.
		self::$_stash = JFactory::$database;
		JFactory::$database = self::$driver;
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function tearDownAfterClass()
	{
		JFactory::$database = self::$_stash;
		self::$driver = null;
	}

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   object  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   array   $array       An array of methods names to mock with callbacks.
	 * This method assumes that the mock callback is named {mock}{method name}.
	 *
	 * @return  void
	 *
	 * @since   12.1
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
				$callback = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($this->any())
			->method($methodName)
			->will($this->returnCallback($callback));
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   object  $mockObject  The mock object.
	 * @param   array   $array       An associative array of methods to mock with return values:<br />
	 * string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($this->any())
			->method($method)
			->will($this->returnValue($return));
		}
	}

	/**
	 * Gets a mock application object.
	 *
	 * @return  JApplication
	 *
	 * @since   12.1
	 */
	public function getMockApplication()
	{
		// Attempt to load the real class first.
		class_exists('JApplication');

		return TestMockApplication::create($this);
	}

	/**
	 * Gets a mock configuration object.
	 *
	 * @return  JConfig
	 *
	 * @since   12.1
	 */
	public function getMockConfig()
	{
		return TestMockConfig::create($this);
	}

	/**
	 * Gets a mock database object.
	 *
	 * @return  JDatabase
	 *
	 * @since   12.1
	 */
	public function getMockDatabase()
	{
		// Attempt to load the real class first.
		class_exists('JDatabaseDriver');

		return TestMockDatabaseDriver::create($this);
	}

	/**
	 * Gets a mock dispatcher object.
	 *
	 * @param   boolean  $defaults  Add default register and trigger methods for testing.
	 *
	 * @return  JEventDispatcher
	 *
	 * @since   12.1
	 */
	public function getMockDispatcher($defaults = true)
	{
		// Attempt to load the real class first.
		class_exists('JEventDispatcher');

		return TestMockDispatcher::create($this, $defaults);
	}

	/**
	 * Gets a mock document object.
	 *
	 * @return  JDocument
	 *
	 * @since   12.1
	 */
	public function getMockDocument()
	{
		// Attempt to load the real class first.
		class_exists('JDocument');

		return TestMockDocument::create($this);
	}

	/**
	 * Gets a mock language object.
	 *
	 * @return  JLanguage
	 *
	 * @since   12.1
	 */
	public function getMockLanguage()
	{
		// Attempt to load the real class first.
		class_exists('JLanguage');

		return TestMockLanguage::create($this);
	}

	/**
	 * Gets a mock session object.
	 *
	 * @param   array  $options  An array of key-value options for the JSession mock.
	 * getId : the value to be returned by the mock getId method
	 * get.user.id : the value to assign to the user object id returned by get('user')
	 * get.user.name : the value to assign to the user object name returned by get('user')
	 * get.user.username : the value to assign to the user object username returned by get('user')
	 *
	 * @return  JSession
	 *
	 * @since   12.1
	 */
	public function getMockSession($options = array())
	{
		// Attempt to load the real class first.
		class_exists('JSession');

		return TestMockSession::create($this, $options);
	}

	/**
	 * Gets a mock web object.
	 *
	 * @param   array  $options  A set of options to configure the mock.
	 *
	 * @return  JApplicationWeb
	 *
	 * @since   12.1
	 */
	public function getMockWeb($options = array())
	{
		// Attempt to load the real class first.
		class_exists('JApplicationWeb');

		return TestMockApplicationWeb::create($this, $options);
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 *
	 * @since   12.1
	 */
	protected function getConnection()
	{
		if (!is_null(self::$driver))
		{
			return $this->createDefaultDBConnection(self::$driver->getConnection(), ':memory:');
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 *
	 * @since   11.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/stubs/database.xml');
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 *
	 * @since   12.1
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
	 * @since   12.1
	 */
	protected function getTearDownOperation()
	{
		// Required given the use of InnoDB contraints.
		return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application = $this->_stashedFactoryState['application'];
		JFactory::$config = $this->_stashedFactoryState['config'];
		JFactory::$dates = $this->_stashedFactoryState['dates'];
		JFactory::$session = $this->_stashedFactoryState['session'];
		JFactory::$language = $this->_stashedFactoryState['language'];
		JFactory::$document = $this->_stashedFactoryState['document'];
		JFactory::$acl = $this->_stashedFactoryState['acl'];
		JFactory::$mailer = $this->_stashedFactoryState['mailer'];
	}

	/**
	 * Saves the current state of the JError error handlers.
	 *
	 * @return  void
	 *
	 * @deprecated  13.1
	 * @since       12.1
	 */
	protected function saveErrorHandlers()
	{
		$this->_stashedErrorState = array();

		// Handle optional usage of JError until removed.
		if (class_exists('JError'))
		{
			$this->_stashedErrorState[E_NOTICE] = JError::getErrorHandling(E_NOTICE);
			$this->_stashedErrorState[E_WARNING] = JError::getErrorHandling(E_WARNING);
			$this->_stashedErrorState[E_ERROR] = JError::getErrorHandling(E_ERROR);
		}
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function saveFactoryState()
	{
		$this->_stashedFactoryState['application'] = JFactory::$application;
		$this->_stashedFactoryState['config'] = JFactory::$config;
		$this->_stashedFactoryState['dates'] = JFactory::$dates;
		$this->_stashedFactoryState['session'] = JFactory::$session;
		$this->_stashedFactoryState['language'] = JFactory::$language;
		$this->_stashedFactoryState['document'] = JFactory::$document;
		$this->_stashedFactoryState['acl'] = JFactory::$acl;
		$this->_stashedFactoryState['mailer'] = JFactory::$mailer;
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @param   array  $errorHandlers  araay of values and options to set the handlers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setErrorHandlers($errorHandlers)
	{
		$mode = null;
		$options = null;

		foreach ($errorHandlers as $type => $params)
		{
			$mode = $params['mode'];

			// Handle optional usage of JError until removed.
			if (class_exists('JError'))
			{
				if (isset($params['options']))
				{
					JError::setErrorHandling($type, $mode, $params['options']);
				}
				else
				{
					JError::setErrorHandling($type, $mode);
				}
			}
		}
	}

	/**
	 * Sets the JError error handlers to callback mode and points them at the test logging method.
	 *
	 * @param   string  $testName  The name of the test class for which to set the error callback method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setErrorCallback($testName)
	{
		$callbackHandlers = array(
			E_NOTICE => array('mode' => 'callback', 'options' => array($testName, 'errorCallback')),
			E_WARNING => array('mode' => 'callback', 'options' => array($testName, 'errorCallback')),
			E_ERROR => array('mode' => 'callback', 'options' => array($testName, 'errorCallback'))
		);

		$this->setErrorHandlers($callbackHandlers);
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		if (empty(static::$driver))
		{
			$this->markTestSkipped('There is no database driver.');
		}

		parent::setUp();
	}
}
