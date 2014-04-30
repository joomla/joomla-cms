<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRackspacePublicTempurl.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspacePublicTempurlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspace  Object under test.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->object = new JRackspace($this->options);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the createTempUrl method.
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function testCreateTempUrl()
	{
		$method = "GET";
		$url = "http://MySampleUrl/v1/SamplePath";
		$ttl = 120;
		$key = "MySampleKey";
		$expires = (int) (time() + $ttl);
		$sig = hash_hmac("sha1", "$method\n$expires\nv1/SamplePath", $key);
		$expectedResult = "http://MySampleUrl/v1/SamplePath?temp_url_sig=" . $sig . "&temp_url_expires=" . $expires;

		$this->assertThat(
			$this->object->public->tempurl->createTempUrl($method, $url, $ttl, $key),
			$this->equalTo($expectedResult)
		);
	}
}
