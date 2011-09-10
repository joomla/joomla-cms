<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/client/http.php';
require_once __DIR__.'/JHttpInspector.php';

/**
 * Tests for the JHttp class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Client
 * @since       11.3
 */
class JHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Gets the data for testConnect.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getTestConnectData()
	{
		return array(
			'Basic test' => array(
				// String url.
				'http://github.com',
				// Expected hash key for the connection.
				md5('github.com'.'80')
			),
			'Basic test with port' => array(
				// String url.
				'http://build.joomla.org:8080',
				// Expected hash key for the connection.
				md5('build.joomla.org'.'8080')
			),
		);
	}

	/**
	 * Tests the JHttp::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$http = new JHttpInspector;

		$this->assertThat(
			$http->timeout,
			$this->equalTo(5),
			'Tests the constructor.'
		);

		$http = new JHttpInspector(array('timeout' => 42));

		$this->assertThat(
			$http->timeout,
			$this->equalTo(42),
			'Tests the consuctor option for timeout is set.'
		);
	}

	/**
	 * Tests the JHttp::connect method.
	 *
	 * @param   string  $url  The url to test.
	 * @param   string  $key  The hash of the internally stored connection.
	 *
	 * @return  void
	 *
	 * @dataProvider  getTestConnectData
	 * @since   11.3
	 */
	public function testConnect($url, $key)
	{
		$http = new JHttpInspector;

		$res = $http->connect(JUri::getInstance($url));

		$this->assertThat(
			$res,
			$this->equalTo($http->connections[$key]),
			'Tests that the internally set connection is what was returned.'
		);

		// We can't test a bad address because the connector throws a PHP error.
		$this->assertThat(
			is_resource($res),
			$this->isTrue(),
			'Tests that the return value for a case that should connect is a resource.'
		);
	}

	/**
	 * Tests the JHttp::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JHttp::getResponseObject method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetResponseObject()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JHttp::head method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testHead()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JHttp::post method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testPost()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JHttp::sendRequest method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSendRequest()
	{
		$this->markTestIncomplete();
	}
}