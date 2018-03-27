<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Trait defining common properties for Joomla test cases
 */
trait TestCaseTrait
{
	use TestCaseDeprecated;

	/**
	 * @var    array  Various JFactory static instances stashed away to be restored later.
	 * @since  12.1
	 */
	private $_stashedFactoryState = [
		'application' => null,
		'config' => null,
		'container' => null,
		'dates' => null,
		'database' => null,
		'session' => null,
		'language' => null,
		'document' => null,
		'acl' => null,
		'mailer' => null
	];

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   array                                    $array       An array of methods names to mock with callbacks.
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
				$callback   = $method;
			}
			else
			{
				$methodName = $method;
				$callback   = [get_called_class(), 'mock' . $method];
			}

			$mockObject->expects($this->any())
				->method($methodName)
				->willReturnCallback($callback);
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   array                                    $array       An associative array of methods to mock with return values:<br>
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
				->willReturn($return);
		}
	}

	/**
	 * Gets a mock CMS application object.
	 *
	 * @param   array  $options      A set of options to configure the mock.
	 * @param   array  $constructor  An array containing constructor arguments to inject into the mock.
	 *
	 * @return  JApplicationCms
	 *
	 * @since   3.2
	 */
	public function getMockCmsApp($options = array(), $constructor = array())
	{
		// Attempt to load the real class first.
		class_exists('JApplicationCms');

		return TestMockApplicationCms::create($this, $options, $constructor);
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
	 * @param   string  $driver        Optional driver to create a sub-class of JDatabaseDriver
	 * @param   array   $extraMethods  An array of additional methods to add to the mock
	 * @param   string  $nullDate      A null date string for the driver.
	 * @param   string  $dateFormat    A date format for the driver.
	 *
	 * @return  JDatabaseDriver
	 *
	 * @since   12.1
	 */
	public function getMockDatabase($driver = '', array $extraMethods = array(), $nullDate = '0000-00-00 00:00:00', $dateFormat = 'Y-m-d H:i:s')
	{
		// Attempt to load the real class first.
		class_exists('JDatabaseDriver');

		return TestMockDatabaseDriver::create($this, $driver, $extraMethods, $nullDate, $dateFormat);
	}

	/**
	 * Gets a mock dispatcher object.
	 *
	 * @param   boolean  $defaults  Add default register and trigger methods for testing.
	 *
	 * @return  \Joomla\Event\DispatcherInterface
	 *
	 * @since   12.1
	 */
	public function getMockDispatcher($defaults = true)
	{
		// Attempt to load the interface first.
		class_exists('Joomla\\Event\\DispatcherInterface');

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
	 * Gets a mock input object.
	 *
	 * @param   array  $options  A associative array of options to configure the mock.
	 *                           * methods => an array of additional methods to mock
	 *
	 * @return  JInput
	 *
	 * @since   3.4
	 */
	public function getMockInput(array $options = null)
	{
		// Attempt to load the real class first.
		class_exists('JInput');

		$mocker = new TestMockInput($this);

		return $mocker->createInput($options);
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
}
