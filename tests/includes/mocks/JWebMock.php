<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

// Load dependencies.
require_once __DIR__ . '/JDocumentMock.php';
require_once __DIR__ . '/JLanguageMock.php';
require_once __DIR__ . '/JSessionMock.php';

/**
 * Mock class for JWeb.
 *
 * @package  Joomla.UnitTest
 * @since    12.1
 */
class JWebGlobalMock
{
	/**
	 * Creates and instance of the mock JWeb object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JWeb (work in progress).
		$methods = array(
			'get',
			'getDocument',
			'getLanguage',
			'getSession',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JWeb',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		// Mock calls to JWeb::getDocument().
		$mockObject->expects($test->any())->method('getDocument')->will($test->returnValue(JDocumentGlobalMock::create($test)));
		
		// Mock calls to JWeb::getDocument().
		$mockObject->expects($test->any())->method('getLanguage')->will($test->returnValue(JLanguageGlobalMock::create($test)));
		
		// Mock calls to JWeb::getSession().
		$mockObject->expects($test->any())->method('getSession')->will($test->returnValue(JSessionGlobalMock::create($test)));

		return $mockObject;
	}
}