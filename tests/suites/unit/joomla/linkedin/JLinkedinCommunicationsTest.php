<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/linkedin/communications.php';

/**
 * Test class for JLinkedinCommunications.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinCommunicationsTest extends TestCase
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
	 * @var    JLinkedinCommunications  Object under test.
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

		$this->object = new JLinkedinCommunications($this->options, $this->client);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->oauth = new JLinkedinOAuth($this->options, $this->client);
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
	 * Tests the inviteByEmail method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testInviteByEmail()
	{
		$email = 'example@domain.com';
		$first_name = 'Frist';
		$last_name = 'Last';
		$subject = 'Subject';
		$body = 'body';
		$connection = 'friend';

		$path = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/email=' . $email . '">
							<first-name>' . $first_name . '</first-name>
							<last-name>' . $last_name . '</last-name>
						</person>
					</recipient>
				</recipients>
				<subject>' . $subject . '</subject>
				<body>' . $body . '</body>
				<item-content>
				    <invitation-request>
				      <connect-type>' . $connection . '</connect-type>
				    </invitation-request>
				</item-content>
			 </mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->inviteByEmail($this->oauth, $email, $first_name, $last_name, $subject, $body, $connection),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the inviteByEmail method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
	 */
	public function testInviteByEmailFailure()
	{
		$email = 'example@domain.com';
		$first_name = 'Frist';
		$last_name = 'Last';
		$subject = 'Subject';
		$body = 'body';
		$connection = 'friend';

		$path = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/email=' . $email . '">
							<first-name>' . $first_name . '</first-name>
							<last-name>' . $last_name . '</last-name>
						</person>
					</recipient>
				</recipients>
				<subject>' . $subject . '</subject>
				<body>' . $body . '</body>
				<item-content>
				    <invitation-request>
				      <connect-type>' . $connection . '</connect-type>
				    </invitation-request>
				</item-content>
			 </mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->inviteByEmail($this->oauth, $email, $first_name, $last_name, $subject, $body, $connection);
	}

	/**
	 * Tests the inviteById method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testInviteById()
	{
		$id = 'lcnIwDU0S6';
		$first_name = 'Frist';
		$last_name = 'Last';
		$subject = 'Subject';
		$body = 'body';
		$connection = 'friend';

		$name = 'NAME_SEARCH';
		$value = 'mwjY';

		$path = '/v1/people-search:(people:(api-standard-profile-request))';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = '{"apiStandardProfileRequest": {"headers": {"_total": 1,"values": [{"name": "x-li-auth-token","value": "' .
			$name . ':' . $value . '"}]}}}';

		$data['format'] = 'json';
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->at(0))
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$path = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/id=' . $id . '">
						</person>
					</recipient>
				</recipients>
				<subject>' . $subject . '</subject>
				<body>' . $body . '</body>
				<item-content>
				    <invitation-request>
				      <connect-type>' . $connection . '</connect-type>
				       <authorization>
				      	<name>' . $name . '</name>
				        <value>' . $value . '</value>
				      </authorization>
				    </invitation-request>
				</item-content>
			 </mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->at(1))
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->inviteById($this->oauth, $id, $first_name, $last_name, $subject, $body, $connection),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the inviteById method - failure
	 *
	 * @return  void
	 *
	 * @expectedException RuntimeException
	 * @since   12.3
	 */
	public function testInviteByIdFailure()
	{
		$id = 'lcnIwDU0S6';
		$first_name = 'Frist';
		$last_name = 'Last';
		$subject = 'Subject';
		$body = 'body';
		$connection = 'friend';

		$name = 'NAME_SEARCH';
		$value = 'mwjY';

		$path = '/v1/people-search:(people:(api-standard-profile-request))';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['format'] = 'json';
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->at(0))
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->inviteById($this->oauth, $id, $first_name, $last_name, $subject, $body, $connection);
	}

	/**
	 * Tests the sendMessage method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSendMessage()
	{
		$recipient = array('~', 'lcnIwDU0S6');
		$subject = 'Subject';
		$body = 'body';

		$path = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/~"/>
					</recipient>
					<recipient>
						<person path="/people/lcnIwDU0S6"/>
					</recipient>
				  </recipients>
				  <subject>' . $subject . '</subject>
				  <body>' . $body . '</body>
				</mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->sendMessage($this->oauth, $recipient, $subject, $body),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the sendMessage method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
	 */
	public function testSendMessageFailure()
	{
		$recipient = array('~', 'lcnIwDU0S6');
		$subject = 'Subject';
		$body = 'body';

		$path = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/~"/>
					</recipient>
					<recipient>
						<person path="/people/lcnIwDU0S6"/>
					</recipient>
				  </recipients>
				  <subject>' . $subject . '</subject>
				  <body>' . $body . '</body>
				</mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->sendMessage($this->oauth, $recipient, $subject, $body);
	}
}
