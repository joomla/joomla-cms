<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JErrorInspector.php';

/**
 * Test class for JError.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Error
 * @since       12.3
 */
class JErrorTest extends TestCase
{
	/**
	 * Test JError::getError
	 *
	 * @return  void
	 */
	public function testGetError()
	{
		JErrorInspector::manipulateStack(array());

		$this->assertThat(
			JError::getError(),
			$this->isFalse(),
			'There was no error on the error stack but getError did not return false'
		);

		// We normally couldn't have strings, but this is only a test
		JErrorInspector::manipulateStack(array('Error1', 'Error2'));

		$this->assertThat(
			JError::getError(),
			$this->equalTo('Error1'),
			'We did not get the proper value back from getError - it should have returned our fake error'
		);

		$this->assertThat(
			JErrorInspector::inspectStack(),
			$this->equalTo(array('Error1', 'Error2')),
			'The stack was changed by getError even though unset was false'
		);

		$this->assertThat(
			JError::getError(true),
			$this->equalTo('Error1'),
			'We did not get the proper value back from getError - it should have returned our fake error'
		);

		$this->assertThat(
			JErrorInspector::inspectStack(),
			$this->equalTo(array('Error2')),
			'The stack was either not changed or changed the wrong way by getError (with  unset true)'
		);

		// Here we remove any junk left on the error stack
		JErrorInspector::manipulateStack(array());
	}

	/**
	 * Test JError::getErrors
	 *
	 * @return  void
	 */
	public function testGetErrors()
	{
		JErrorInspector::manipulateStack(array('value1', 'value2', 'value3'));

		$this->assertThat(
			JError::getErrors(),
			$this->equalTo(array('value1', 'value2', 'value3')),
			'Somehow a basic getter did not manage to return the static value'
		);

		JErrorInspector::manipulateStack(array());
	}

	/**
	 * Test JError::addToStack
	 *
	 * @return  void
	 */
	public function testAddToStack()
	{
		// Remove the following lines when the framework is fixed.
		// $this->markTestSkipped('The framework is currently broken.  Skipping this test.');

		JErrorInspector::manipulateStack(array('value1', 'value2', 'value3'));

		$exception = new JException('This is the error message', 1056, 'error');

		JError::addToStack($exception);

		$stack = JErrorInspector::inspectStack();

		$this->assertThat(
			$stack[3],
			$this->identicalTo($exception),
			'The exception did not get properly added to the stack'
		);

		JErrorInspector::manipulateStack(array());
	}

	/**
	 * Test JError::setErrorHandling
	 *
	 * @return  void
	 */
	public function testSetErrorHandling()
	{
		JErrorInspector::manipulateLevels(
			array(
				E_NOTICE => 'Notice',
				E_WARNING => 'Warning',
				E_ERROR => 'Error'
			)
		);
		$errorHandling = JErrorInspector::inspectHandlers();

		$this->assertThat(
			JError::setErrorHandling(E_NOTICE, 'message'),
			$this->isTrue(),
			'Setting a message error handler failed'
		);

		$handlers = JErrorInspector::inspectHandlers();

		$this->assertThat(
			$handlers[E_NOTICE],
			$this->equalTo(array('mode' => 'message')),
			'The error handler did not get set to message'
		);

		$this->assertThat(
			JError::setErrorHandling(E_NOTICE, 'callback', array($this, 'callbackHandler')),
			$this->isTrue(),
			'Setting a message error handler failed'
		);

		$handlers = JErrorInspector::inspectHandlers();

		$this->assertThat(
			$handlers[E_NOTICE],
			$this->equalTo(array('mode' => 'callback', 'options' => array($this, 'callbackHandler'))),
			'The error handler did not get set to callback'
		);

		JErrorInspector::manipulateHandlers($errorHandling);
	}

	/**
	 * Test JError::setErrorHandling
	 *
	 * Callback for testSetErrorHandling
	 *
	 * @return  void
	 */
	public function callbackHandler()
	{
	}
}
