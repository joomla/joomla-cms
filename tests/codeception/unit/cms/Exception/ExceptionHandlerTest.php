<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tests\Exception;

use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Factory;

/**
 * Test class for \Joomla\CMS\Exception\ExceptionHandler.
 */
class ExceptionHandlerTest extends \TestCaseDatabase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		Factory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		\TestReflection::setValue('\\JDocument', 'instances', array());
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @covers  Joomla\CMS\Exception\ExceptionHandler::render
	 */
	public function testEnsureTheErrorPageIsCorrectlyRendered()
	{
		$documentResponse = '<title>500 - Testing Joomla\CMS\Exception\ExceptionHandler::render() with RuntimeException</title>Testing Joomla\CMS\Exception\ExceptionHandler::render() with RuntimeException';

		$key = serialize(
			array(
				'error',
				array(
					'charset'   => 'utf-8',
					'lineend'   => 'unix',
					'tab'       => "\t",
					'language'  => 'en-GB',
					'direction' => 'ltr',
				),
			)
		);

		$mockErrorDocument = $this->getMockBuilder('\\JDocumentError')
			->setMethods(array('setError', 'setTitle', 'render'))
			->getMock();

		$mockErrorDocument->expects($this->any())
			->method('render')
			->willReturn($documentResponse);

		\TestReflection::setValue('\\JDocument', 'instances', array($key => $mockErrorDocument));

		// Create an Exception to inject into the method
		$exception = new \RuntimeException('Testing Joomla\CMS\Exception\ExceptionHandler::render() with RuntimeException', 500);

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		ExceptionHandler::render($exception);
		$output = ob_get_clean();

		// Validate the mocked response from JDocument was received
		$this->assertEquals($documentResponse, $output);
	}

	/**
	 * @covers  Joomla\CMS\Exception\ExceptionHandler::render
	 *
	 * @requires  PHP 7.0
	 */
	public function testEnsureTheErrorPageIsCorrectlyRenderedWithThrowables()
	{
		$documentResponse = '<title>500 - Testing Joomla\CMS\Exception\ExceptionHandler::render() with PHP 7 Error</title>Testing Joomla\CMS\Exception\ExceptionHandler::render() with PHP 7 Error';

		$key = serialize(
			array(
				'error',
				array(
					'charset'   => 'utf-8',
					'lineend'   => 'unix',
					'tab'       => "\t",
					'language'  => 'en-GB',
					'direction' => 'ltr',
				),
			)
		);

		$mockErrorDocument = $this->getMockBuilder('\\JDocumentError')
			->setMethods(array('setError', 'setTitle', 'render'))
			->getMock();

		$mockErrorDocument->expects($this->any())
			->method('render')
			->willReturn($documentResponse);

		\TestReflection::setValue('\\JDocument', 'instances', array($key => $mockErrorDocument));

		// Create an Error to inject into the method
		$exception = new \Error('Testing Joomla\CMS\Exception\ExceptionHandler::render() with PHP 7 Error', 500);

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		ExceptionHandler::render($exception);
		$output = ob_get_clean();

		// Validate the mocked response from JDocument was received
		$this->assertEquals($documentResponse, $output);
	}

	/**
	 * @covers  Joomla\CMS\Exception\ExceptionHandler::render
	 */
	public function testEnsureTheRenderMethodCorrectlyHandlesNonExceptionClasses()
	{
		// Create an object to inject into the method
		$object = new \stdClass;

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		ExceptionHandler::render($object);
		$output = ob_get_clean();

		$this->assertEquals('Error displaying the error page', $output);
	}
}
