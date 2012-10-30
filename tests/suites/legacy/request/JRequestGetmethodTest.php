<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRequest.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JRequestTest_GetMethod extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	public function setUp()
	{
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
		parent::setUp();
	}

	/**
	 * Test JRequest::getMethod
	 *
	 * @return  void
	 */
	public function testGetMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this -> assertEquals('POST', JRequest::getMethod());
		$_SERVER['REQUEST_METHOD'] = 'get';
		$this -> assertEquals('GET', JRequest::getMethod());
	}
}
