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
}
