<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 */

require_once JPATH_PLATFORM . '/joomla/facebook/http.php';
require_once JPATH_PLATFORM . '/joomla/facebook/facebook.php';
require_once JPATH_PLATFORM . '/joomla/facebook/friends.php';

/**
 * Test class for JFacebook.
 */

class JFacebookFriendsTest extends TestCase
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
	 * @var    JFacebookFriends  Object under test.
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 */
	protected $errorString = '{"error": {"message": "Invalid OAuth access token."}}';

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

		$this->object = new JFacebookFriends($this->options, $this->client);
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
	 * Tests the getFriendList method
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 */
	public function testGetFriendList()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with('/me/friends?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFriendList('me', $access_token),
			$this->equalTo(json_decode($this->sampleString, true))
		);
	}

	/**
	 * Tests the getFriendList method - failure
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @expectedException  DomainException
	 */
	public function testGetFriendListFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with('/me/friends?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getFriendList('me', $access_token);
	}

}
