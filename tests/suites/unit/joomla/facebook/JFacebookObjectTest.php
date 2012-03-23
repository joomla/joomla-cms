<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 */

require_once JPATH_PLATFORM . '/joomla/facebook/http.php';
require_once JPATH_PLATFORM . '/joomla/facebook/object.php';
require_once __DIR__ . '/stubs/JFacebookObjectMock.php';

/**
 * Test class for JFacebook.
 */

class JFacebookObjectTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  Mock client object.
	 */
	protected $client;

	/**
	 * @var    JFacebookObjectMock  Object under test.
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

		$this->object = new JFacebookObjectMock($this->options, $this->client);
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
	 * Test the fetchUrl method.
	 * 
	 * @todo Implement testFetchUrl().
	 * 
	 * @return  void
	 */
	public function testFetchUrl()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the sendRequest method.
	 *
	 * @return  void
	 */
	public function testSendRequest()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

}
