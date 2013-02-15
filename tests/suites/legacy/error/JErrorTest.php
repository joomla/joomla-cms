<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/legacy/error/error.php';
require_once JPATH_PLATFORM . '/legacy/exception/exception.php';
require_once __DIR__ . '/JErrorInspector.php';

/**
 * Test class for JError.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Error
 *
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
	 * Test JError::raise
	 *
	 * @todo    Implement testRaise().
	 *
	 * @return  void
	 */
	public function testRaise()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::throwError
	 *
	 * @todo    Implement testThrowError().
	 *
	 * @return  void
	 */
	public function testThrowError()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::raiseError
	 *
	 * @todo    Implement testRaiseError().
	 *
	 * @return  void
	 */
	public function testRaiseError()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::raiseWarning
	 *
	 * @todo    Implement testRaiseWarning().
	 *
	 * @return  void
	 */
	public function testRaiseWarning()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::raiseNotice
	 *
	 * @todo    Implement testRaiseNotice().
	 *
	 * @return  void
	 */
	public function testRaiseNotice()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::getErrorHandling
	 *
	 * @todo    Implement testGetErrorHandling().
	 *
	 * @return  void
	 */
	public function testGetErrorHandling()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
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
		return;
	}

	/**
	 * Test JError::attachHandler
	 *
	 * @todo    Implement testAttachHandler().
	 *
	 * @return  void
	 */
	public function testAttachHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::detachHandler
	 *
	 * @todo    Implement testDetachHandler().
	 *
	 * @return  void
	 */
	public function testDetachHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::registerErrorLevel
	 *
	 * @todo    Implement testRegisterErrorLevel().
	 *
	 * @return  void
	 */
	public function testRegisterErrorLevel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::translateErrorLevel
	 *
	 * @todo    Implement testTranslateErrorLevel().
	 *
	 * @return  void
	 */
	public function testTranslateErrorLevel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleIgnore
	 *
	 * @todo    Implement testHandleIgnore().
	 *
	 * @return  void
	 */
	public function testHandleIgnore()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleEcho
	 *
	 * @todo    Implement testHandleEcho().
	 *
	 * @return  void
	 */
	public function testHandleEcho()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleVerbose
	 *
	 * @todo    Implement testHandleVerbose().
	 *
	 * @return  void
	 */
	public function testHandleVerbose()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleDie
	 *
	 * @todo    Implement testHandleDie().
	 *
	 * @return  void
	 */
	public function testHandleDie()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleMessage
	 *
	 * @todo    Implement testHandleMessage().
	 *
	 * @return  void
	 */
	public function testHandleMessage()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleLog
	 *
	 * @todo    Implement testHandleLog().
	 *
	 * @return  void
	 */
	public function testHandleLog()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::handleCallback
	 *
	 * @todo    Implement testHandleCallback().
	 *
	 * @return  void
	 */
	public function testHandleCallback()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::customErrorPage
	 *
	 * @todo    Implement testCustomErrorPage().
	 *
	 * @return  void
	 */
	public function testCustomErrorPage()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::customerErrorHandler
	 *
	 * @todo    Implement testCustomErrorHandler().
	 *
	 * @return  void
	 */
	public function testCustomErrorHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test JError::renderBacktrace
	 *
	 * @todo    Implement testRenderBacktrace().
	 *
	 * @return  void
	 */
	public function testRenderBacktrace()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
