<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/linkedin/groups.php';

/**
 * Test class for JLinkedinGroups.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinGroupsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JLinkedinHttp  Mock http object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JLinkedinGroups  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    JLinkedinOAuth  Authentication object for the Twitter object.
	 * @since  12.3
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
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
		$key = "lIio7RcLe5IASG5jpnZrA";
		$secret = "dl3BrWij7LT04NUpy37BRJxGXpWgjNvMrneuQ11EveE";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/linkedin_test.php";

		$this->options = new JRegistry;
		$this->client = $this->getMock('JLinkedinHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JLinkedinGroups($this->options, $this->client);
		$this->oauth = new JLinkedinOAuth($key, $secret, $my_url, $this->options, $this->client);
		$this->oauth->setToken($key, $secret);
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
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGroup($this->oauth, $id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGroup method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getGroup($this->oauth, $id, $fields, $start, $count);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
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
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   12.3
	 */
	public function testGetMemberships($id)
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

		if ($id)
		{
			$path = '/v1/people/' . $id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMemberships($this->oauth, $id, $fields, $start, $count, $membership_state),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMemberships method - failure
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   12.3
	 */
	public function testGetMembershipsFailure($id)
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

		if ($id)
		{
			$path = '/v1/people/' . $id . '/group-memberships';
		}
		else
		{
			$path = '/v1/people/~/group-memberships';
		}

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getMemberships($this->oauth, $id, $fields, $start, $count, $membership_state);
	}

	/**
	 * Tests the getSettings method
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @since   12.3
	 */
	public function testGetSettings($person_id)
	{
		$group_id = '12345';
		$fields = '(group:(id,name),membership-state,email-digest-frequency,email-announcements-from-managers,allow-messages-from-members,email-for-every-new-post)';
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSettings($this->oauth, $person_id, $group_id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSettings method - failure
	 *
	 * @return  void
	 *
	 * @dataProvider seedId
	 * @expectedException DomainException
	 * @since   12.3
	 */
	public function testGetSettingsFailure($person_id)
	{
		$group_id = '12345';
		$fields = '(group:(id,name),membership-state,email-digest-frequency,email-announcements-from-managers,allow-messages-from-members,email-for-every-new-post)';
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getSettings($this->oauth, $person_id, $group_id, $fields, $start, $count);
	}

	/**
	 * Tests the changeSettings method
	 *
	 * @return  void
	 *
	 * @since   12.3
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
			$this->object->changeSettings($this->oauth, $group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the changeSettings method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$this->object->changeSettings($this->oauth, $group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post);
	}

	/**
	 * Tests the joinGroup method
	 *
	 * @return  void
	 *
	 * @since   12.3
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
			$this->object->joinGroup($this->oauth, $group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the joinGroup method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$this->object->joinGroup($this->oauth, $group_id, $show_logo, $digest_frequency, $announcements, $allow_messages, $new_post);
	}

	/**
	 * Tests the leaveGroup method
	 *
	 * @return  void
	 *
	 * @since   12.3
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
			$this->object->leaveGroup($this->oauth, $group_id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the leaveGroup method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
	 */
	public function testLeaveGroupFailure()
	{
		$group_id = '12345';

		$path = '/v1/people/~/group-memberships/' . $group_id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->leaveGroup($this->oauth, $group_id);
	}

	/**
	 * Tests the getDiscussions method
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDiscussions($this->oauth, $id, $fields, $start, $count, $order, $category, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDiscussions method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getDiscussions($this->oauth, $id, $fields, $start, $count, $order, $category, $modified_since);
	}

	/**
	 * Tests the getUserPosts method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetUserPosts()
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
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserPosts($this->oauth, $group_id, $role, $person_id, $fields, $start, $count, $order, $category, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUserPosts method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getUserPosts($this->oauth, $group_id, $role, $person_id, $fields, $start, $count, $order, $category, $modified_since);
	}

	/**
	 * Tests the getPost method
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPost($this->oauth, $post_id, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getPost($this->oauth, $post_id, $fields);
	}

	/**
	 * Tests the getPostComments method
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPostComments($this->oauth, $post_id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPostComments method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$path = $this->oauth->to_url($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getPostComments($this->oauth, $post_id, $fields, $start, $count);
	}

	/**
	 * Tests the createPost method
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost($this->oauth, $group_id, $title, $summary),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the createPost method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$this->object->createPost($this->oauth, $group_id, $title, $summary);
	}
}
