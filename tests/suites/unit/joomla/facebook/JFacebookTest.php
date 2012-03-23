<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 */

require_once JPATH_PLATFORM . '/joomla/facebook/facebook.php';

/**
 * Test class for JFacebook.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 */

class JFacebookTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 */
	protected $client;

	/**
	* @var    JFacebook  Object under test.
	*/
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * 
	 * @return   void
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
	 * @return   void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the magic __get method - friends
	 * 
	 * @return   void
	 */
	public function test__GetFriends()
	{
		$this->assertThat(
			$this->object->friends,
			$this->isInstanceOf('JFacebookFriends')
		);
	}

	/**
	 * Tests the setOption method
	 * 
	 * @return   void
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
	 * @return   void
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
