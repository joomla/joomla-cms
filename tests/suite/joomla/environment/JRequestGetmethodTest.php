<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters, Inc.
 * @version $Id: JRequestGetmethodTest.php 20196 2011-01-09 02:40:25Z ian $
 *
 */

require_once JPATH_PLATFORM.'/joomla/environment/request.php';

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

	function testGetMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this -> assertEquals('POST', JRequest::getMethod());
		$_SERVER['REQUEST_METHOD'] = 'get';
		$this -> assertEquals('GET', JRequest::getMethod());
	}

}


