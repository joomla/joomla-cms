<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFacebookOauth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @since       13.1
 */
class JFacebookOauthTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data..
	 * @since  13.1
	 */
	protected $input;

	/**
	* @var    JFacebookOauth  Object under test.
	* @since  13.1
	*/
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->input = new JInput;

		$this->object = new JFacebookOauth($this->options, $this->client, $this->input);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the setScope method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testSetScope()
	{
		$this->object->setScope('read_stream');

		$this->assertThat(
			$this->options->get('scope'),
			$this->equalTo('read_stream')
		);
	}

	/**
	 * Tests the getScope method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetScope()
	{
		$this->options->set('scope', 'read_stream');

		$this->assertThat(
			$this->object->getScope(),
			$this->equalTo('read_stream')
		);
	}
}
