<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/linkedin/groups.php';

/**
 * Test class for JLinkedinGroups.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinGroupsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  13.1
	 */
	protected $input;

	/**
	 * @var    JLinkedinGroups  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    JLinkedinOAuth  Authentication object for the Twitter object.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  13.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  13.1
	 */
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/linkedin_test.php";

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JLinkedinOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));

		$this->object = new JLinkedinGroups($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getGroup method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetGroup()
	{
		$id = '12345';
		$fields = '(id,name,short-description,description,relation-to-viewer:(membership-state,available-actions),is-open-to-non-members)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/groups/' . $id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGroup($id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGroup method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testGetGroupFailure()
	{
		$id = '12345';
		$fields = '(id,name,short-description,description,relation-to-viewer:(membership-state,available-actions),is-open-to-non-members)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/groups/' . $id;
		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getGroup($id, $fields, $start, $count);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedId()
	{
		// Member ID
		return array(
			array('lcnIwDU0S6'),
			array(null)
			);
	}

	/**
	 * Tests the getMemberships method
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   13.1
	 */
	public function testGetMemberships($person_id)
	{
		$fields = '(id,name,short-description,description,relation-to-viewer:(membership-state,available-actions),is-open-to-non-members)';
		$start = 1;
		$count = 10;
		$membership_state = 'member';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['membership-state'] = $membership_state;

		if ($person_id)
		{
			$path = '/v1/people/' . $person_id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMemberships($person_id, $fields, $start, $count, $membership_state),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMemberships method - failure
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetMembershipsFailure($person_id)
	{
		$fields = '(id,name,short-description,description,relation-to-viewer:(membership-state,available-actions),is-open-to-non-members)';
		$start = 1;
		$count = 10;
		$membership_state = 'member';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['membership-state'] = $membership_state;

		if ($person_id)
		{
			$path = '/v1/people/' . $person_id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getMemberships($person_id, $fields, $start, $count, $membership_state);
	}

	/**
	 * Tests the getSettings method
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   13.1
	 */
	public function testGetSettings($person_id)
	{
		$group_id = '12345';
		$fields = '(group:(id,name),membership-state,email-digest-frequency,email-announcements-from-managers,
			allow-messages-from-members,email-for-every-new-post)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		if ($person_id)
		{
			$path = '/v1/people/' . $person_id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= '/' . $group_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSettings($person_id, $group_id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSettings method - failure
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetSettingsFailure($person_id)
	{
		$group_id = '12345';
		$fields = '(group:(id,name),membership-state,email-digest-frequency,email-announcements-from-managers,
			allow-messages-from-members,email-for-every-new-post)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		if ($person_id)
		{
			$path = '/v1/people/' . $person_id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= '/' . $group_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getSettings($person_id, $group_id, $fields, $start, $count);
	}

	/**
	 * Tests the changeSettings method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testChangeSettings()
	{
		$group_id = '12345';
		$show_logo = true;
		$digest_frequency = 'daily';
		$announcements = true;
		$allow_messages = true;
		$new_post = true;

		$path = '/v1/people/~/group-memberships/' . $group_id;

		$xml = '<group-membership>
				  <show-group-logo-in-profile>true</show-group-logo-in-profile>
				  <email-digest-frequency>
				    <code>daily</code>
				  </email-digest-frequency>
				  <email-announcements-from-managers>true</email-announcements-from-managers>
				  <allow-messages-from-members>true</allow-messages-from-members>
				  <email-for-every-new-post>true</email-for-every-new-post>
				</group-membership>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->changeSettings($group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the changeSettings method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testChangeSettingsFailure()
	{
		$group_id = '12345';
		$show_logo = true;
		$digest_frequency = 'daily';
		$announcements = true;
		$allow_messages = true;
		$new_post = true;

		$path = '/v1/people/~/group-memberships/' . $group_id;

		$xml = '<group-membership>
				  <show-group-logo-in-profile>true</show-group-logo-in-profile>
				  <email-digest-frequency>
				    <code>daily</code>
				  </email-digest-frequency>
				  <email-announcements-from-managers>true</email-announcements-from-managers>
				  <allow-messages-from-members>true</allow-messages-from-members>
				  <email-for-every-new-post>true</email-for-every-new-post>
				</group-membership>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 403;
		$returnData->body = 'Throttle limit for calls to this resource is reached.';

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->changeSettings($group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post);
	}

	/**
	 * Tests the joinGroup method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testJoinGroup()
	{
		$group_id = '12345';
		$show_logo = true;
		$digest_frequency = 'daily';
		$announcements = true;
		$allow_messages = true;
		$new_post = true;

		$path = '/v1/people/~/group-memberships';

		$xml = '<group-membership>
				  <group>
				    <id>' . $group_id . '</id>
				  </group>
				  <show-group-logo-in-profile>true</show-group-logo-in-profile>
				  <email-digest-frequency>
				    <code>daily</code>
				  </email-digest-frequency>
				  <email-announcements-from-managers>true</email-announcements-from-managers>
				  <allow-messages-from-members>true</allow-messages-from-members>
				  <email-for-every-new-post>false</email-for-every-new-post>
				  <membership-state>
				    <code>member</code>
				  </membership-state>
				</group-membership>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->joinGroup($group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the joinGroup method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testJoinGroupFailure()
	{
		$group_id = '12345';
		$show_logo = true;
		$digest_frequency = 'daily';
		$announcements = true;
		$allow_messages = true;
		$new_post = true;

		$path = '/v1/people/~/group-memberships';

		$xml = '<group-membership>
				  <group>
				    <id>' . $group_id . '</id>
				  </group>
				  <show-group-logo-in-profile>true</show-group-logo-in-profile>
				  <email-digest-frequency>
				    <code>daily</code>
				  </email-digest-frequency>
				  <email-announcements-from-managers>true</email-announcements-from-managers>
				  <allow-messages-from-members>true</allow-messages-from-members>
				  <email-for-every-new-post>false</email-for-every-new-post>
				  <membership-state>
				    <code>member</code>
				  </membership-state>
				</group-membership>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 403;
		$returnData->body = 'Throttle limit for calls to this resource is reached.';

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->joinGroup($group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post);
	}

	/**
	 * Tests the leaveGroup method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testLeaveGroup()
	{
		$group_id = '12345';

		$path = '/v1/people/~/group-memberships/' . $group_id;

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->leaveGroup($group_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the leaveGroup method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testLeaveGroupFailure()
	{
		$group_id = '12345';

		$path = '/v1/people/~/group-memberships/' . $group_id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = 'unauthorized';

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->leaveGroup($group_id);
	}

	/**
	 * Tests the getDiscussions method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetDiscussions()
	{
		$id = '12345';
		$fields = '(creation-timestamp,title,summary,creator:(first-name,last-name),likes,attachment:(content-url,title),relation-to-viewer)';
		$start = 1;
		$count = 10;
		$order = 'recency';
		$category = 'discussion';
		$modified_since = '1302727083000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['order'] = $order;
		$data['category'] = $category;
		$data['modified-since'] = $modified_since;

		$path = '/v1/groups/' . $id . '/posts';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDiscussions($id, $fields, $start, $count, $order, $category, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDiscussions method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetDiscussionsFailure()
	{
		$id = '12345';
		$fields = '(creation-timestamp,title,summary,creator:(first-name,last-name),likes,attachment:(content-url,title),relation-to-viewer)';
		$start = 1;
		$count = 10;
		$order = 'recency';
		$category = 'discussion';
		$modified_since = '1302727083000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['order'] = $order;
		$data['category'] = $category;
		$data['modified-since'] = $modified_since;

		$path = '/v1/groups/' . $id . '/posts';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getDiscussions($id, $fields, $start, $count, $order, $category, $modified_since);
	}

	/**
	 * Tests the getUserPosts method
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   13.1
	 */
	public function testGetUserPosts($person_id)
	{
		$group_id = '12345';
		$role = 'creator';
		$fields = '(creation-timestamp,title,summary,creator:(first-name,last-name),likes,attachment:(content-url,title),relation-to-viewer)';
		$start = 1;
		$count = 10;
		$order = 'recency';
		$category = 'discussion';
		$modified_since = '1302727083000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['role'] = $role;
		$data['start'] = $start;
		$data['count'] = $count;
		$data['order'] = $order;
		$data['category'] = $category;
		$data['modified-since'] = $modified_since;

		if ($person_id)
		{
			$path = '/v1/people/' . $person_id . '/group-memberships/' . $group_id . '/posts';
		}
		else
		{
			$path = '/v1/people/~/group-memberships/' . $group_id . '/posts';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserPosts($group_id, $role, $person_id, $fields, $start, $count, $order, $category, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUserPosts method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetUserPostsFailure()
	{
		$group_id = '12345';
		$role = 'creator';
		$person_id = '123345456';
		$fields = '(creation-timestamp,title,summary,creator:(first-name,last-name),likes,attachment:(content-url,title),relation-to-viewer)';
		$start = 1;
		$count = 10;
		$order = 'recency';
		$category = 'discussion';
		$modified_since = '1302727083000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['role'] = $role;
		$data['start'] = $start;
		$data['count'] = $count;
		$data['order'] = $order;
		$data['category'] = $category;
		$data['modified-since'] = $modified_since;

		$path = '/v1/people/' . $person_id . '/group-memberships/' . $group_id . '/posts';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getUserPosts($group_id, $role, $person_id, $fields, $start, $count, $order, $category, $modified_since);
	}

	/**
	 * Tests the getPost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPost()
	{
		$post_id = 'g-12345';
		$fields = '(id,type,category,creator,title,relation-to-viewer:(is-following,is-liked),likes,comments,site-group-post-url)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/posts/' . $post_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPost($post_id, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetPostFailure()
	{
		$post_id = 'g-12345';
		$fields = '(id,type,category,creator,title,relation-to-viewer:(is-following,is-liked),likes,comments,site-group-post-url)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/posts/' . $post_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getPost($post_id, $fields);
	}

	/**
	 * Tests the getPostComments method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPostComments()
	{
		$post_id = 'g-12345';
		$fields = '(creator:(first-name,last-name,picture-url),creation-timestamp,text))';
		$start = 1;
		$count = 5;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/posts/' . $post_id . '/comments';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPostComments($post_id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPostComments method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetPostCommentsFailure()
	{
		$post_id = 'g-12345';
		$fields = '(creator:(first-name,last-name,picture-url),creation-timestamp,text))';
		$start = 1;
		$count = 5;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/posts/' . $post_id . '/comments';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getPostComments($post_id, $fields, $start, $count);
	}

	/**
	 * Tests the createPost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreatePost()
	{
		$group_id = '12345';
		$title = 'post title';
		$summary = 'post summary';

		$path = '/v1/groups/' . $group_id . '/posts';

		$xml = '<post><title>' . $title . '</title><summary>' . $summary . '</summary></post>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;
		$returnData->headers = array('Location' => 'https://api.linkedin.com/v1/posts/g_12334_234512');

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost($group_id, $title, $summary),
			$this->equalTo('g_12334_234512')
		);
	}

	/**
	 * Tests the createPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testCreatePostFailure()
	{
		$group_id = '12345';
		$title = 'post title';
		$summary = 'post summary';

		$path = '/v1/groups/' . $group_id . '/posts';

		$xml = '<post><title>' . $title . '</title><summary>' . $summary . '</summary></post>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->createPost($group_id, $title, $summary);
	}

	/**
	 * Tests the _likeUnlike method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test_likeUnlike()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the likePost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testLikePost()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-liked';

		$xml = '<is-liked>true</is-liked>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->likePost($post_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the likePost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testLikePostFailure()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-liked';

		$xml = '<is-liked>true</is-liked>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->likePost($post_id);
	}

	/**
	 * Tests the unlikePost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUnlikePost()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-liked';

		$xml = '<is-liked>false</is-liked>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unlikePost($post_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the _followUnfollow method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test_followUnfollow()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the followPost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testFollowPost()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-following';

		$xml = '<is-following>true</is-following>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->followPost($post_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the followPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testFollowPostFailure()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-following';

		$xml = '<is-following>true</is-following>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->followPost($post_id);
	}

	/**
	 * Tests the unfollowPost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUnfollowPost()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/relation-to-viewer/is-following';

		$xml = '<is-following>false</is-following>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unfollowPost($post_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the flagPost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testFalgPost()
	{
		$flag = 'promotion';
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/category/code';

		$xml = '<code>' . $flag . '</code>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->flagPost($post_id, $flag),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the flagPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testFalgPostFailure()
	{
		$flag = 'promotion';
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id . '/category/code';

		$xml = '<code>' . $flag . '</code>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->flagPost($post_id, $flag);
	}

	/**
	 * Tests the deletePost method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeletePost()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id;

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deletePost($post_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the deletePost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testDeletePostFailure()
	{
		$post_id = 'g_12345';

		$path = '/v1/posts/' . $post_id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->deletePost($post_id);
	}

	/**
	 * Tests the getComment method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetComment()
	{
		$comment_id = 'g-12345';
		$fields = '(id,text,creator,creation-timestamp,relation-to-viewer)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/comments/' . $comment_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComment($comment_id, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComment method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetCommentFailure()
	{
		$comment_id = 'g-12345';
		$fields = '(id,text,creator,creation-timestamp,relation-to-viewer)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/comments/' . $comment_id;

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getComment($comment_id, $fields);
	}

	/**
	 * Tests the addComment method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testAddComment()
	{
		$post_id = 'g_12345';
		$comment = 'some comment';

		$path = '/v1/posts/' . $post_id . '/comments';

		$xml = '<comment><text>' . $comment . '</text></comment>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;
		$returnData->headers = array('Location' => 'https://api.linkedin.com/v1/comments/g_12334_234512');

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->addComment($post_id, $comment),
			$this->equalTo('g_12334_234512')
		);
	}

	/**
	 * Tests the addComment method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testAddCommentFailure()
	{
		$post_id = 'g_12345';
		$comment = 'some comment';

		$path = '/v1/posts/' . $post_id . '/comments';

		$xml = '<comment><text>' . $comment . '</text></comment>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->addComment($post_id, $comment);
	}

	/**
	 * Tests the deleteComment method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteComment()
	{
		$comment_id = 'g_12345';

		$path = '/v1/comments/' . $comment_id;

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteComment($comment_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the deleteComment method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testDeleteCommentFailure()
	{
		$comment_id = 'g_12345';

		$path = '/v1/comments/' . $comment_id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->deleteComment($comment_id);
	}

	/**
	 * Tests the getSuggested method
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   13.1
	 */
	public function testGetSuggested($person_id)
	{
		$fields = '(id,name,is-open-to-non-members)';

		// Set request parameters.
		$data['format'] = 'json';

		// Set the API base
		$path = '/v1/people/';

		if ($person_id)
		{
			$path .= $person_id . '/suggestions/groups';
		}
		else
		{
			$path .= '~/suggestions/groups';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSuggested($person_id, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSuggested method - failure
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetSuggestedFailure($person_id)
	{
		$fields = '(id,name,is-open-to-non-members)';

		// Set request parameters.
		$data['format'] = 'json';

		// Set the API base
		$path = '/v1/people/';

		if ($person_id)
		{
			$path .= $person_id . '/suggestions/groups';
		}
		else
		{
			$path .= '~/suggestions/groups';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getSuggested($person_id, $fields);
	}

	/**
	 * Tests the deleteSuggestion method
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   13.1
	 */
	public function testDeleteSuggestion($person_id)
	{
		$suggestion_id = '12345';

		// Set the API base
		$path = '/v1/people/';

		if ($person_id)
		{
			$path .= $person_id . '/suggestions/groups/' . $suggestion_id;
		}
		else
		{
			$path .= '~/suggestions/groups/' . $suggestion_id;
		}

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteSuggestion($suggestion_id, $person_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the deleteSuggestion method - failure
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testDeleteSuggestionFailure($person_id)
	{
		$suggestion_id = '12345';

		// Set the API base
		$path = '/v1/people/';

		if ($person_id)
		{
			$path .= $person_id . '/suggestions/groups/' . $suggestion_id;
		}
		else
		{
			$path .= '~/suggestions/groups/' . $suggestion_id;
		}

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->deleteSuggestion($suggestion_id, $person_id);
	}
}
