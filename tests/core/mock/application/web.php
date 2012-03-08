<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationWeb.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplicationWeb
{
	/**
	 * Creates and instance of the mock JApplicationWeb object.
	 *
	 * @param   object  $test     A test object.
	 * @param   array   $options  A set of options to configure the mock.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test, $options = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationWeb (work in progress).
		$methods = array(
			'get',
			'getDocument',
			'getLanguage',
			'getSession',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JApplicationWeb',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		// Mock calls to JApplicationWeb::getDocument().
		$mockObject->expects($test->any())->method('getDocument')->will($test->returnValue(JDocumentGlobalMock::create($test)));

		// Mock calls to JApplicationWeb::getLanguage().
		$mockObject->expects($test->any())->method('getLanguage')->will($test->returnValue(JLanguageGlobalMock::create($test)));

		// Mock a call to JApplicationWeb::getSession().
		if (isset($options['session']))
		{
			$mockObject->expects($test->any())->method('getSession')->will($test->returnValue($options['session']));
		}
		else
		{
			$mockObject->expects($test->any())->method('getSession')->will($test->returnValue(JSessionGlobalMock::create($test)));
		}

		return $mockObject;
	}
}
