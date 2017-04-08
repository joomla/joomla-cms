<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Abstract test case class for unit testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
	use TestCaseTrait;

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
		JFactory::$container = $this->_stashedFactoryState['container'];
		JFactory::$dates = $this->_stashedFactoryState['dates'];
		JFactory::$session = $this->_stashedFactoryState['session'];
		JFactory::$language = $this->_stashedFactoryState['language'];
		JFactory::$document = $this->_stashedFactoryState['document'];
		JFactory::$acl = $this->_stashedFactoryState['acl'];
		JFactory::$mailer = $this->_stashedFactoryState['mailer'];
		JFactory::$database = $this->_stashedFactoryState['database'];
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
		$this->_stashedFactoryState['container'] = JFactory::$container;
		$this->_stashedFactoryState['dates'] = JFactory::$dates;
		$this->_stashedFactoryState['session'] = JFactory::$session;
		$this->_stashedFactoryState['language'] = JFactory::$language;
		$this->_stashedFactoryState['document'] = JFactory::$document;
		$this->_stashedFactoryState['acl'] = JFactory::$acl;
		$this->_stashedFactoryState['mailer'] = JFactory::$mailer;
		$this->_stashedFactoryState['database'] = JFactory::$database;
	}


	/**
	 * Overrides the parent setup method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::setUp()
	 * @since   11.1
	 */
	protected function setUp()
	{
		$this->setExpectedError();

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		if (is_array($this->expectedErrors) && !empty($this->expectedErrors))
		{
			$this->fail('An expected error was not raised.');
		}

		// Handle optional usage of JError until removed.
		if (class_exists('JError'))
		{
			JError::setErrorHandling(E_NOTICE, 'ignore');
			JError::setErrorHandling(E_WARNING, 'ignore');
			JError::setErrorHandling(E_ERROR, 'ignore');
		}

		parent::tearDown();
	}
}
