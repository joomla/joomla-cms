<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/facebook/facebook.php';

/**
 * Test class for JFacebook.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	* @var    JFacebook  Object under test.
	* @since  12.1
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
	 * @since   12.1
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->client = $this->getMock('JFacebookHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JFacebook($this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the magic __get method - user
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetUser()
	{
		$this->assertThat(
			$this->object->user,
			$this->isInstanceOf('JFacebookUser')
		);
	}

	/**
	 * Tests the magic __get method - status
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetStatus()
	{
		$this->assertThat(
			$this->object->status,
			$this->isInstanceOf('JFacebookStatus')
		);
	}

	/**
	 * Tests the magic __get method - checkin
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetCheckin()
	{
		$this->assertThat(
			$this->object->checkin,
			$this->isInstanceOf('JFacebookCheckin')
		);
	}

	/**
	 * Tests the magic __get method - event
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetEvent()
	{
		$this->assertThat(
			$this->object->event,
			$this->isInstanceOf('JFacebookEvent')
		);
	}

	/**
	 * Tests the magic __get method - group
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetGroup()
	{
		$this->assertThat(
			$this->object->group,
			$this->isInstanceOf('JFacebookGroup')
		);
	}

	/**
	 * Tests the magic __get method - link
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetLink()
	{
		$this->assertThat(
			$this->object->link,
			$this->isInstanceOf('JFacebookLink')
		);
	}

	/**
	 * Tests the magic __get method - note
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetNote()
	{
		$this->assertThat(
			$this->object->note,
			$this->isInstanceOf('JFacebookNote')
		);
	}

	/**
	 * Tests the magic __get method - post
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function test__GetPost()
	{
		$this->assertThat(
			$this->object->post,
			$this->isInstanceOf('JFacebookPost')
		);
	}

	/**
	 * Tests the setOption method
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testSetOption()
	{
		$this->object->setOption('api.url', 'https://example.com/settest');

		$this->assertThat(
			$this->options->get('api.url'),
			$this->equalTo('https://example.com/settest')
		);
	}

	/**
	 * Tests the getOption method
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetOption()
	{
		$this->options->set('api.url', 'https://example.com/gettest');

		$this->assertThat(
			$this->object->getOption('api.url', 'https://example.com/gettest'),
			$this->equalTo('https://example.com/gettest')
		);
	}
}
