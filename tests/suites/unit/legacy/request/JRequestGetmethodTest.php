<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A unit test class for JRequest
 */
class JRequestTest_GetMethod extends PHPUnit_Framework_TestCase
{

	/**
	 * Clear the cache
	 */
	function setUp() {
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
	}

	/**
	 * @covers JRequest::getMethod
	 */
	function testGetMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this -> assertEquals('POST', JRequest::getMethod());
		$_SERVER['REQUEST_METHOD'] = 'get';
		$this -> assertEquals('GET', JRequest::getMethod());
	}

}
