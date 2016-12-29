<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Test class for JFacebookUser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebookUserTest extends TestCase
{
	/**
	 * @var    Registry  Options for the Facebook object.
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
	 * @var    JFacebookUser  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    JFacebookOauth  Authentication object for the Facebook object.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample URL string.
	 * @since  13.1
	 */
	protected $sampleUrl = '{"url": "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash2/372662_10575676585_830678637_q.jpg"}';

	/**
	 * @var    string  Sample JSON string.
	 * @since  13.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  13.1
	 */
	protected $errorString = '{"error": {"message": "Generic Error."}}';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$app_id = "app_id";
		$app_secret = "app_secret";
		$my_url = "http://localhost/gsoc/joomla-platform/facebook_test.php";
		$access_token = array(
			'access_token' => 'token',
			'expires' => '51837673', 'created' => '2443672521');

		$this->options = new Registry;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->input = new JInput;
		$this->oauth = new JFacebookOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JFacebookUser($this->options, $this->client, $this->oauth);

		$this->options->set('clientid', $app_id);
		$this->options->set('clientsecret', $app_secret);
		$this->options->set('redirecturi', $my_url);
		$this->options->set('sendheaders', true);
		$this->options->set('authmethod', 'get');

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->client, $this->input, $this->oauth, $this->object);
		parent::tearDown();
	}

	/**
	* Provides test data.
	*
	* @return  array
	*
	* @since   13.1
	*/
	public function seedOauth()
	{
		// Use oauth.
		return array(
			array(true),
			array(false)
		);
	}

	/**
	 * Tests the getUser method
	 *
	 * @param   boolean  $oauth  True if the JFacebookOauth object is used.
	 *
	 * @dataProvider  seedOauth
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetUser($oauth)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		if ($oauth)
		{
			$token = $this->oauth->getToken();
			$this->client->expects($this->once())
			->method('get')
			->with('me?access_token=' . $token['access_token'])
			->will($this->returnValue($returnData));

			$this->assertThat(
				$this->object->getUser('me'),
				$this->equalTo(json_decode($this->sampleString))
			);
		}
		else
		{
			// User is not authenticated.
			$token = $this->object->getOAuth();
			$this->object->setOAuth(null);

			$this->client->expects($this->once())
			->method('get')
			->with('me')
			->will($this->returnValue($returnData));

			$this->assertThat(
				$this->object->getUser('me'),
				$this->equalTo(json_decode($this->sampleString))
			);

			// Authenticated.
			$this->object->setOAuth($token);
		}
	}

	/**
	 * Tests the getUser method - failure
	 *
	 * @param   boolean  $oauth  True if the JFacebookOauth object is used.
	 *
	 * @dataProvider  seedOauth
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetUserFailure($oauth)
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		if ($oauth != null)
		{
			$this->oauth->setToken(null);

			$this->assertThat(
				$this->object->getUser('me'),
				$this->equalTo(false)
			);
		}
		else
		{
			// User is not authenticated.
			$token = $this->object->getOAuth();
			$this->object->setOAuth(null);

			$this->client->expects($this->once())
			->method('get')
			->with('me')
			->will($this->returnValue($returnData));

			$this->setExpectedException('RuntimeException');
			$this->object->getUser('me');

			// Authenticated.
			$this->object->setOAuth($token);
		}
	}

	/**
	 * Tests the getFriends method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFriends()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friends?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFriends('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFriends method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFriendsFailure()
	{
		$access_token = $this->oauth->getToken();

		$this->oauth->setToken(null);

		$this->assertThat(
			$this->object->getFriends('me'),
			$this->equalTo(false)
		);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->oauth->setToken($access_token);
		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friends?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->setExpectedException('RuntimeException');
		$this->object->getFriends('me');
	}

	/**
	 * Tests the getFriendRequests method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFriendRequests()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friendrequests?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFriendRequests('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFriendRequests method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetFriendRequestsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friendrequests?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFriendRequests('me');
	}

	/**
	 * Tests the getFriendLists method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFriendLists()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friendlists?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFriendLists('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFriendLists method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetFriendListsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friendlists?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFriendLists('me');
	}

	/**
	 * Tests the getFeed method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFeed()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFeed('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFeed method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetFeedFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFeed('me');
	}

	/**
	 * Tests the getHome method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetHome()
	{
		$filter = 'app_2305272732';
		$location = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$extra_fields = '?filter=' . $filter . '&with=location';

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/home' . $extra_fields . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getHome('me', $filter, $location),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getHome method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetHomeFailure()
	{
		$filter = 'app_2305272732';
		$location = true;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$extra_fields = '?filter=' . $filter . '&with=location';

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/home' . $extra_fields . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getHome('me', $filter, $location);
	}

	/**
	 * Tests the hasFriend method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testHasFriend()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friends/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->hasFriend('me', 2341245353),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the hasFriend method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testHasFriendFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/friends/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->hasFriend('me', 2341245353);
	}

	/**
	 * Tests the getMutualFriends method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetMutualFriends()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/mutualfriends/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMutualFriends('me', 2341245353),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMutualFriends method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetMutualFriendsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/mutualfriends/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getMutualFriends('me', 2341245353);
	}

	/**
	 * Tests the getPicture method.
	 *
	 * @param   boolean  $oauth  True if the JFacebookOauth object is used.
	 *
	 * @dataProvider  seedOauth
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPicture($oauth)
	{
		$type = 'large';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleUrl;

		if ($oauth)
		{
			$token = $this->oauth->getToken();

			$this->client->expects($this->once())
			->method('get')
			->with('me/picture?redirect=false&type=' . $type . '&access_token=' . $token['access_token'])
			->will($this->returnValue($returnData));

			$this->assertThat(
				$this->object->getPicture('me', false, $type),
				$this->equalTo(json_decode($this->sampleUrl))
			);
		}
		else
		{
			// User is not authenticated.
			$token = $this->object->getOAuth();
			$this->object->setOAuth(null);

			$this->client->expects($this->once())
			->method('get')
			->with('me/picture?redirect=false&type=' . $type)
			->will($this->returnValue($returnData));

			$this->assertThat(
				$this->object->getPicture('me', false, $type),
				$this->equalTo(json_decode($this->sampleUrl))
			);

			// Authenticated.
			$this->object->setOAuth($token);
		}
	}

	/**
	 * Tests the getPicture method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPictureFailure()
	{
		$type = 'large';

		$returnData = new JHttpResponse;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/picture?redirect=false&type=' . $type . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture('me', false, $type),
			$this->equalTo($this->sampleUrl)
		);
	}

	/**
	 * Tests the getFamily method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFamily()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/family?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFamily('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFamily method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetFamilyFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/family?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFamily('me');
	}

	/**
	 * Tests the getNotifications method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetNotifications()
	{
		$read = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/notifications?include_read=1&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getNotifications('me', $read),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getNotifications method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetNotificationsFailure()
	{
		$read = true;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/notifications?include_read=1&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getNotifications('me', $read);
	}

	/**
	 * Tests the updateNotification method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUpdateNotification()
	{
		$notification = 'notif_343543656';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		// Set POST request parameters.
		$data['unread'] = 0;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($notification . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateNotification($notification),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the updateNotification method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUpdateNotificationFailure()
	{
		$notification = 'notif_343543656';
		$access_token = $this->oauth->getToken();

		$this->oauth->setToken(null);

		$this->assertThat(
			$this->object->updateNotification($notification),
			$this->equalTo(false)
		);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['unread'] = 0;

		$this->oauth->setToken($access_token);
		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($notification . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->setExpectedException('RuntimeException');
		$this->object->updateNotification($notification);
	}

	/**
	 * Tests the getPermissions method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPermissions()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/permissions?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPermissions('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPermissions method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPermissionsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/permissions?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPermissions('me');
	}

	/**
	 * Tests the deletePermission method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeletePermission()
	{
		$permission = 'some_permission';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with('me/permissions?permission=' . $permission . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deletePermission('me', $permission),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deletePermission method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeletePermissionFailure()
	{
		$access_token = $this->oauth->getToken();
		$permission = 'some_permission';

		$this->oauth->setToken(null);

		$this->assertThat(
			$this->object->deletePermission('me', $permission),
			$this->equalTo(false)
		);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->oauth->setToken($access_token);
		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with('me/permissions?permission=' . $permission . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->setExpectedException('RuntimeException');
		$this->object->deletePermission('me', $permission);
	}

	/**
	 * Tests the getAlbums method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetAlbums()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/albums?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAlbums('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAlbums method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetAlbumsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/albums?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getAlbums('me');
	}

	/**
	 * Tests the createAlbum method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateAlbum()
	{
		$name = 'test';
		$description = 'This is a test';
		$privacy = '{"value": "SELF"}';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['name'] = $name;
		$data['description'] = $description;
		$data['privacy'] = $privacy;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/albums?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createAlbum('me', $name, $description, $privacy),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createAlbum method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateAlbumFailure()
	{
		$name = 'test';
		$description = 'This is a test';
		$privacy = '{"value": "SELF"}';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['name'] = $name;
		$data['description'] = $description;
		$data['privacy'] = $privacy;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/albums?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createAlbum('me', $name, $description, $privacy);

	}

	/**
	 * Tests the getCheckins method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetCheckins()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/checkins?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCheckins('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCheckins method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetCheckinsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/checkins?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getCheckins('me');
	}

	/**
	 * Tests the createCheckin method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateCheckin()
	{
		$place = '241967239209655';
		$coordinates = '{"latitude":"44.42863444299","longitude":"26.133339107061"}';
		$tags = 'me';
		$message = 'message';
		$link = 'www.test.com';
		$picture = 'some_picture_url';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['place'] = $place;
		$data['coordinates'] = $coordinates;
		$data['tags'] = $tags;
		$data['message'] = $message;
		$data['link'] = $link;
		$data['picture'] = $picture;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/checkins' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createCheckin('me', $place, $coordinates, $tags, $message, $link, $picture),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createCheckin method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateCheckinFailure()
	{
		$place = '241967239209655';
		$coordinates = '{"latitude":"44.42863444299","longitude":"26.133339107061"}';
		$tags = 'me';
		$message = 'message';
		$link = 'www.test.com';
		$picture = 'some_picture_url';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['place'] = $place;
		$data['coordinates'] = $coordinates;
		$data['tags'] = $tags;
		$data['message'] = $message;
		$data['link'] = $link;
		$data['picture'] = $picture;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/checkins' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createCheckin('me', $place, $coordinates, $tags, $message, $link, $picture);
	}

	/**
	 * Tests the getLikes method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetLikes()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLikes('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLikes method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetLikesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getLikes('me');
	}

	/**
	 * Tests the likesPage method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testLikesPage()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/likes/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->likesPage('me', 2341245353),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the likesPage method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testLikesPageFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/likes/2341245353?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->likesPage('me', 2341245353);
	}

	/**
	 * Tests the getEvents method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetEvents()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/events?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getEvents('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getEvents method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetEventsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/events?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getEvents('me');
	}

	/**
	 * Tests the createEvent method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateEvent()
	{
		$name = 'test';
		$start_time = 1590962400;
		$end_time = 1590966000;
		$description = 'description';
		$location = 'location';
		$location_id = '23132156';
		$privacy_type = 'SECRET';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/events' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createEvent('me', $name, $start_time, $end_time, $description, $location, $location_id, $privacy_type),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createEvent method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateEventFailure()
	{
		$name = 'test';
		$start_time = 1590962400;
		$end_time = 1590966000;
		$description = 'description';
		$location = 'location';
		$location_id = '23132156';
		$privacy_type = 'SECRET';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/events' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createEvent('me', $name, $start_time, $end_time, $description, $location, $location_id, $privacy_type);
	}

	/**
	 * Tests the editEvent method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testEditEvent()
	{
		$event = '345345345435';
		$name = 'test';
		$start_time = 1590962400;
		$end_time = 1590966000;
		$description = 'description';
		$location = 'location';
		$location_id = '23132156';
		$privacy_type = 'SECRET';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		// Set POST request parameters.
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($event . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->editEvent($event, $name, $start_time, $end_time, $description, $location, $location_id, $privacy_type),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the editEvent method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testEditEventFailure()
	{
		$event = '345345345435';
		$name = 'test';
		$start_time = 1590962400;
		$end_time = 1590966000;
		$description = 'description';
		$location = 'location';
		$location_id = '23132156';
		$privacy_type = 'SECRET';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($event . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->editEvent($event, $name, $start_time, $end_time, $description, $location, $location_id, $privacy_type);
	}

	/**
	 * Tests the deleteEvent method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteEvent()
	{
		$event = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteEvent($event),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteEvent method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testDeleteEventFailure()
	{
		$event = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteEvent($event);
	}

	/**
	 * Tests the getGroups method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetGroups()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/groups?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGroups('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGroups method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetGroupsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/groups?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getGroups('me');
	}

	/**
	 * Tests the getLinks method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/links?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLinks('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLinks method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetLinksFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/links?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getLinks('me');
	}

	/**
	 * Tests the createLink method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateLink()
	{
		$link = 'www.example.com';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['link'] = $link;
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLink('me', $link, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLink method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateLinkFailure()
	{
		$link = 'www.example.com';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['link'] = $link;
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createLink('me', $link, $message);
	}

	/**
	 * Tests the deleteLink method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteLink()
	{
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLink($link),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteLink method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testDeleteLinkFailure()
	{
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteLink($link);
	}

	/**
	 * Tests the getNotes method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetNotes()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/notes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getNotes('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getNotes method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetNotesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/notes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getNotes('me');
	}

	/**
	 * Tests the createNote method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateNote()
	{
		$subject = 'subject';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['subject'] = $subject;
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/notes' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createNote('me', $subject, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createNote method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateNoteFailure()
	{
		$subject = 'subject';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['subject'] = $subject;
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/notes' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createNote('me', $subject, $message);
	}

	/**
	 * Tests the getPhotos method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPhotos()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/photos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPhotos('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPhotos method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPhotosFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/photos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPhotos('me');
	}

	/**
	 * Tests the createPhoto method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreatePhoto()
	{
		$source = 'path/to/source';
		$message = 'message';
		$place = '23432421234';
		$no_story = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['message'] = $message;
		$data['place'] = $place;
		$data['no_story'] = $no_story;
		$data[basename($source)] = '@' . realpath($source);

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/photos?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPhoto('me', $source, $message, $place, $no_story),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPhoto method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreatePhotoFailure()
	{
		$source = '/path/to/source';
		$message = 'message';
		$place = '23432421234';
		$no_story = true;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['message'] = $message;
		$data['place'] = $place;
		$data['no_story'] = $no_story;
		$data[basename($source)] = '@' . realpath($source);

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/photos?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->object->createPhoto('me', $source, $message, $place, $no_story);
	}

	/**
	 * Tests the getPosts method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPosts()
	{
		$location = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/posts?with=location&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPosts('me', $location),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPosts method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPostsFailure()
	{
		$location = true;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/posts?with=location&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPosts('me', $location);
	}

	/**
	 * Tests the createPost method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreatePost()
	{
		$user = '134534252';
		$message = 'message';
		$link = 'www.example.com';
		$picture = 'thumbnail.example.com';
		$name = 'name';
		$caption = 'caption';
		$description = 'description';
		$place = '1244576532';
		$tags = '1207059,701732';
		$privacy = 'SELF';
		$object_attachment = '32413534634345';
		$actions = array('{"name":"Share","link":"http://networkedblogs.com/hGWk3?a=share"}');

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['place'] = $place;
		$data['tags'] = $tags;
		$data['privacy'] = $privacy;
		$data['object_attachment'] = $object_attachment;
		$data['picture'] = $picture;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($user . '/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost(
				$user, $message, $link, $picture, $name,
				$caption, $description, $actions, $place, $tags, $privacy, $object_attachment
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPost method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreatePostFailure()
	{
		$user = '134534252';
		$message = 'message';
		$link = 'www.example.com';
		$picture = 'thumbnail.example.com';
		$name = 'name';
		$caption = 'caption';
		$description = 'description';
		$place = '1244576532';
		$tags = '1207059,701732';
		$privacy = 'SELF';
		$object_attachment = '32413534634345';
		$actions = array('{"name":"Share","link":"http://networkedblogs.com/hGWk3?a=share"}');

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['place'] = $place;
		$data['tags'] = $tags;
		$data['privacy'] = $privacy;
		$data['object_attachment'] = $object_attachment;
		$data['picture'] = $picture;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($user . '/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createPost(
			$user, $message, $link, $picture, $name,
			$caption, $description, $actions, $place, $tags, $privacy, $object_attachment
			);
	}

	/**
	 * Tests the deletePost method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeletePost()
	{
		$post = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deletePost($post),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deletePost method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testDeletePostFailure()
	{
		$post = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deletePost($post);
	}

	/**
	 * Tests the getStatuses method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetStatuses()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/statuses?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getStatuses('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getStatuses method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetStatusesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/statuses?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getStatuses('me');
	}

	/**
	 * Tests the createStatus method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateStatus()
	{
		$user = '134534252';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($user . '/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createStatus($user, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createStatus method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateStatusFailure()
	{
		$user = '134534252';
		$message = 'message';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['message'] = $message;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($user . '/feed?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createStatus($user, $message);
	}

	/**
	 * Tests the deleteStatus method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteStatus()
	{
		$status = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteStatus($status),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteStatus method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testDeleteStatusFailure()
	{
		$status = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteStatus($status);
	}

	/**
	 * Tests the getVideos method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetVideos()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/videos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getVideos('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getVideos method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetVideosFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/videos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getVideos('me');
	}

	/**
	 * Tests the createVideo method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateVideo()
	{
		$source = '/path/to/source';
		$title = 'title';
		$description = 'Description example';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['title'] = $title;
		$data['description'] = $description;
		$data[basename($source)] = '@' . realpath($source);

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/videos?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createVideo('me', $source, $title, $description),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createVideo method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException RuntimeException
	 */
	public function testCreateVideoFailure()
	{
		$source = '/path/to/source';
		$title = 'title';
		$description = 'Description example';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['title'] = $title;
		$data['description'] = $description;
		$data[basename($source)] = '@' . realpath($source);

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with('me/videos?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->object->createVideo('me', $source, $title, $description);
	}

	/**
	 * Tests the getTagged method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetTagged()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/tagged?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getTagged('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getTagged method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetTaggedFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/tagged?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getTagged('me');
	}

	/**
	 * Tests the getActivities method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetActivities()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/activities?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getActivities('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getActivities method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetActivitiesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/activities?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getActivities('me');
	}

	/**
	 * Tests the getBooks method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetBooks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/books?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getBooks('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getBooks method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetBooksFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/books?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getBooks('me');
	}

	/**
	 * Tests the getInterests method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetInterests()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/interests?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getInterests('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getInterests method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetInterestsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/interests?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getInterests('me');
	}

	/**
	 * Tests the getMovies method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetMovies()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/movies?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMovies('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMovies method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetMoviesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/movies?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getMovies('me');
	}

	/**
	 * Tests the getTelevision method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetTelevision()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/television?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getTelevision('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getTelevision method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetTelevisionFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/television?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getTelevision('me');
	}

	/**
	 * Tests the getMusic method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetMusic()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/music?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMusic('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMusic method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetMusicFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/music?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getMusic('me');
	}

	/**
	 * Tests the getSubscribers method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetSubscribers()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/subscribers?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSubscribers('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSubscribers method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetSubscribersFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/subscribers?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getSubscribers('me');
	}

	/**
	 * Tests the getSubscribedTo method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetSubscribedTo()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/subscribedto?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSubscribedTo('me'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSubscribedTo method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetSubscribedToFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with('me/subscribedto?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getSubscribedTo('me');
	}
}
