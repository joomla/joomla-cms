<?php
/**
 * JoomlaTestCase.php -- unit testing file for JUtilities
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test case class for Joomla Unit Testing
 *
 * @package	Joomla.UnitTest
 *
 */
abstract class JoomlaTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var factoryState
	 */
	protected $factoryState = array();

	/**
	 * @var errorState
	 */
	protected $savedErrorState;

	/**
	 * @var actualError
	 */
	protected static $actualError;

	/**
	 * @var errors
	 */
	protected $expectedErrors = null;

	/**
	 * Saves the current state of the JError error handlers.
	 *
	 * @return	void
	 */
	protected function saveErrorHandlers()
	{
		$this->savedErrorState = array();
		$this->savedErrorState[E_NOTICE] = JError::getErrorHandling(E_NOTICE);
		$this->savedErrorState[E_WARNING] = JError::getErrorHandling(E_WARNING);
		$this->savedErrorState[E_ERROR] = JError::getErrorHandling(E_ERROR);
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @param	array	araay of values and options to set the handlers
	 *
	 * @return	void
	 */
	protected function setErrorHandlers( $errorHandlers )
	{
		$mode = null;
		$options = null;

		foreach ($errorHandlers as $type => $params)
		{
			$mode = $params['mode'];
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

	/**
	 * Sets the JError error handlers to callback mode and points them at the test
	 * logging method.
	 *
	 * @return	void
	 */
	protected function setErrorCallback( $testName )
	{
		$callbackHandlers = array(
			E_NOTICE => array(
				'mode' => 'callback',
				'options' => array($testName, 'errorCallback')
				),
			E_WARNING => array(
				'mode' => 'callback',
				'options' => array($testName, 'errorCallback')
				),
			E_ERROR => array(
				'mode' => 'callback',
				'options' => array($testName, 'errorCallback')
				),
			);
		$this->setErrorHandlers($callbackHandlers);
	}

	protected function setUp()
	{
		parent::setUp();
		$this->setExpectedError();
	}

	protected function tearDown()
	{
		if (is_array($this->expectedErrors) && !empty($this->expectedErrors)) {
			$this->fail('An expected error was not raised.');
		}

		JError::setErrorHandling(E_NOTICE, 'ignore');
		JError::setErrorHandling(E_WARNING, 'ignore');
		JError::setErrorHandling(E_ERROR, 'ignore');

		parent::tearDown();
	}

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param	JException	The JException object from JError
	 *
	 * @return	bool	To not continue with JError processing
	 */
	static function errorCallback( $error )
	{

	}

	/**
	 * Callback receives the error from JError and deals with it appropriately
	 * If a test expects a JError to be raised, it should call this setExpectedError first
	 * If you don't call this method first, the test will fail
	 */
	public function expectedErrorCallback($error)
	{
		foreach($this->expectedErrors AS $key => $err)
		{
			$thisError = true;

			foreach ($err AS $prop => $value)
			{
				if ($error->get($prop) !== $value) {
					$thisError = false;
				}
			}

			if ($thisError) {
				unset($this->expectedErrors[$key]);
				return $error;
			}

		}
		$this->fail('An unexpected error occurred - '.$error->get('message'));
		return $error;
	}

	/**
	 * Tells the unit tests that a method or action you are about to attempt
	 * is expected to result in JError::raiseSomething being called.
	 *
	 * If you don't call this method first, the test will fail.
	 * If you call this method during your test and the error does not occur, then your test
	 * will also fail because we assume you were testing to see that an error did occur when it was
	 * supposed to.
	 *
	 * If passed without argument, the array is initialized if it hsn't been already
	 */
	public function setExpectedError($error = null) {
		if (!is_array($this->expectedErrors)) {
			$this->expectedErrors = array();
			JError::setErrorHandling(E_NOTICE, 'callback', array($this, 'expectedErrorCallback'));
			JError::setErrorHandling(E_WARNING, 'callback', array($this, 'expectedErrorCallback'));
			JError::setErrorHandling(E_ERROR, 'callback', array($this, 'expectedErrorCallback'));
		}
		if (!is_null($error)) {
			$this->expectedErrors[] = $error;
		}
	}


	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function saveFactoryState()
	{
		$this->savedFactoryState['application'] = JFactory::$application;
		$this->savedFactoryState['config'] = JFactory::$config;
		$this->savedFactoryState['session'] = JFactory::$session;
		$this->savedFactoryState['language'] = JFactory::$language;
		$this->savedFactoryState['document'] = JFactory::$document;
		$this->savedFactoryState['acl'] = JFactory::$acl;
		$this->savedFactoryState['database'] = JFactory::$database;
		$this->savedFactoryState['mailer'] = JFactory::$mailer;
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return void
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application = $this->savedFactoryState['application'];
		JFactory::$config = $this->savedFactoryState['config'];
		JFactory::$session = $this->savedFactoryState['session'];
		JFactory::$language = $this->savedFactoryState['language'];
		JFactory::$document = $this->savedFactoryState['document'];
		JFactory::$acl = $this->savedFactoryState['acl'];
		JFactory::$database = $this->savedFactoryState['database'];
		JFactory::$mailer = $this->savedFactoryState['mailer'];
	}
}
