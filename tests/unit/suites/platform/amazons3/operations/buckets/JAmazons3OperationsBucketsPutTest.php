<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/operations/buckets.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3OperationsBucketsPutTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Object  Object under test.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->options->set('api.accessKeyId', 'testAccessKeyId');
		$this->options->set('api.secretAccessKey', 'testSecretAccessKey');
		$this->options->set('api.url', 's3.amazonaws.com');
		$this->options->set('testBucket', 'testBucket');
		$this->options->set('testBucketRegion', 'testBucketRegion');
		$this->options->set(
			'testCORS',
			array(
				array(
					"ID" => "RuleUniqueId",
					"AllowedOrigin" => array("http://*.example1.com", "http://*.example2.com"),
					"AllowedMethod" => array("PUT", "POST", "DELETE"),
					"AllowedHeader" => "*",
					"MaxAgeSeconds" => "3000",
					"ExposeHeader"  => "x-amz-server-side-encryption",
				),
				array(
					"AllowedOrigin" => "*",
					"AllowedMethod" => "GET",
					"AllowedHeader" => "*",
					"MaxAgeSeconds" => "3000",
				)
			)
		);
		$this->options->set(
			'testLifecycle',
			array(
				array(
					"ID" => "RuleUniqueId",
					"Prefix" => "glacierobjects",
					"Status"  => "Enabled",
					"Transition" => array(
						"Date" => "2013-12-31T00:00:00.000Z",
						"StorageClass" => "GLACIER",
					),
					"Expiration" => array(
						"Date" => "2022-10-12T00:00:00.000Z",
					),
				),
			)
		);
		$this->options->set(
			'testPolicy',
			array(
				"Version" => "2008-10-17",
				"Id" => "aaaa-bbbb-cccc-eeee",
				"Statement" => array(
					array(
					"Effect" => "Allow",
					"Sid" => "1",
					"Principal" => array(
						"CanonicalUser" => "6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96"
					),
					"Action" => "s3:*",
					"Resource" => "arn:aws:s3:::gsoc-test-2/*",
					),
				),
			)
		);
		$this->options->set(
			'testLogging',
			array(
				"TargetBucket" => "gsoc-test-2",
				"TargetPrefix" => "mybucket-access_log",
				"TargetGrants" => array(
					"Grant" => array(
						"Grantee" => array(
							"EmailAddress" => "alex.ukf@gmail.com",
						),
						"Permission" => "READ",
					),
				),
			)
		);
		$this->options->set(
			'testNotification',
			array(
				"Topic" => "arn:aws:sns:us-east-1:123456789012:myTopic",
				"Event" => "s3:ReducedRedundancyLostObject",
			)
		);
		$this->options->set(
			'testTagging',
			array(
				array(
					"Key" => "Project",
					"Value" => "Project One",
				),
				array(
					"Key" => "User",
					"Value" => "alexukf",
				),
			)
		);
		$this->options->set("testRequestPayment", "Requester");
		$this->options->set(
			'testVersioningWithoutMfaDelete',
			array(
				"Status" => "Enabled",
			)
		);
		$this->options->set(
			'testVersioningWithMfaDelete',
			array(
				"Status" => "Enabled",
				"MfaDelete" => "Enabled",
			)
		);
		$this->options->set("serialNr", "20899872");
		$this->options->set("tokenCode", "301749");
		$this->options->set(
			'testWebsite',
			array(
				"IndexDocument" => array(
					"Suffix" => "index.html"
				),
				"ErrorDocument" => array(
					"Key" => "SomeErrorDocument.html"
				),
				"RoutingRules" => array(
					"RoutingRule" => array(
						"Condition" => array(
							"KeyPrefixEquals" => "docs/"
						),
						"Redirect" => array(
							"ReplaceKeyPrefixWith" => "documents/"
						)
					)
				)
			)
		);

		$this->client = $this->getMock('JAmazons3Http', array('delete', 'get', 'head', 'put'));

		$this->object = new JAmazons3OperationsBuckets($this->options, $this->client);
	}

	/**
	 * Tests the putBucket method with a region different from the default one
	 * and no ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketWithRegion()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/";
		$headers = array();
		$content = "";

		if ($this->options->get("testBucketRegion") != "")
		{
			$content = "<CreateBucketConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">"
				. "<LocationConstraint>" . $this->options->get("testBucketRegion") . "</LocationConstraint>"
				. "</CreateBucketConfiguration>";

			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$headers["Content-Length"] = strlen($content);
		}

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucket($this->options->get("testBucket"), $this->options->get("testBucketRegion")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucket method with the default region and canned ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketWithCannedAcl()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-acl" => "public-read",
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "", $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucket($this->options->get("testBucket"), "", array("acl" => "public-read")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucket method with the default region and explicitly specified
	 * ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketWithExplicitAcl()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/";

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-grant-read" => "emailAddress=\"xyz@amazon.com\", emailAddress=\"abc@amazon.com\"",
			"x-amz-grant-full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\"",
			"x-amz-grant-write-acp" => "uri=\"http://acs.amazonaws.com/groups/global/AuthenticatedUsers\"",
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "", $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucket(
				$this->options->get("testBucket"),
				"",
				array(
					"read" => "emailAddress=\"xyz@amazon.com\", emailAddress=\"abc@amazon.com\"",
					"full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\"",
					"write-acp" => "uri=\"http://acs.amazonaws.com/groups/global/AuthenticatedUsers\"",
				)
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketAcl method with canned ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketAclCanned()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?acl";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-acl" => "public-read",
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "", $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketAcl($this->options->get("testBucket"), array("acl" => "public-read")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketAcl method with explicitly specified ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketAclExplicit()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?acl";

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-grant-read" => "emailAddress=\"xyz@amazon.com\", emailAddress=\"abc@amazon.com\"",
			"x-amz-grant-full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\"",
			"x-amz-grant-write-acp" => "uri=\"http://acs.amazonaws.com/groups/global/AuthenticatedUsers\"",
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "", $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketAcl(
				$this->options->get("testBucket"),
				array(
					"read" => "emailAddress=\"xyz@amazon.com\", emailAddress=\"abc@amazon.com\"",
					"full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\"",
					"write-acp" => "uri=\"http://acs.amazonaws.com/groups/global/AuthenticatedUsers\"",
				)
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketCors method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketCors()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?cors";
		$headers = array();

		$content = "<CORSConfiguration>\n"
			. "<CORSRule>\n"
			. "<ID>" . "RuleUniqueId" . "</ID>\n"
			. "<AllowedOrigin>" . "http://*.example1.com" . "</AllowedOrigin>\n"
			. "<AllowedOrigin>" . "http://*.example2.com" . "</AllowedOrigin>\n"
			. "<AllowedMethod>" . "PUT" . "</AllowedMethod>\n"
			. "<AllowedMethod>" . "POST" . "</AllowedMethod>\n"
			. "<AllowedMethod>" . "DELETE" . "</AllowedMethod>\n"
			. "<AllowedHeader>" . "*" . "</AllowedHeader>\n"
			. "<MaxAgeSeconds>" . "3000" . "</MaxAgeSeconds>\n"
			. "<ExposeHeader>" . "x-amz-server-side-encryption" . "</ExposeHeader>\n"
			. "</CORSRule>\n"
			. "<CORSRule>\n"
			. "<AllowedOrigin>" . "*" . "</AllowedOrigin>\n"
			. "<AllowedMethod>" . "GET" . "</AllowedMethod>\n"
			. "<AllowedHeader>" . "*" . "</AllowedHeader>\n"
			. "<MaxAgeSeconds>" . "3000" . "</MaxAgeSeconds>\n"
			. "</CORSRule>\n"
			. "</CORSConfiguration>\n";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketCors(
				$this->options->get("testBucket"),
				$this->options->get("testCORS")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketLifecycle method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketLifecycle()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?lifecycle";
		$headers = array();

		$content = "<LifecycleConfiguration>\n"
			. "<Rule>\n"
			. "<ID>" . "RuleUniqueId" . "</ID>\n"
			. "<Prefix>" . "glacierobjects" . "</Prefix>\n"
			. "<Status>" . "Enabled" . "</Status>\n"
			. "<Transition>\n"
			. "<Date>" . "2013-12-31T00:00:00.000Z" . "</Date>\n"
			. "<StorageClass>" . "GLACIER" . "</StorageClass>\n"
			. "</Transition>\n"
			. "<Expiration>\n"
			. "<Date>" . "2022-10-12T00:00:00.000Z" . "</Date>\n"
			. "</Expiration>\n"
			. "</Rule>\n"
			. "</LifecycleConfiguration>\n";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketLifecycle(
				$this->options->get("testBucket"),
				$this->options->get("testLifecycle")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketPolicy method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketPolicy()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?policy";
		$headers = array();

		$content = '{"Version":"2008-10-17","Id":"aaaa-bbbb-cccc-eeee",'
			. '"Statement":[{"Effect":"Allow","Sid":"1",'
			. '"Principal":{"CanonicalUser":"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96"},'
			. '"Action":"s3:*","Resource":"arn:aws:s3:::gsoc-test-2\/*"}]}';

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 204;
		$returnData->body = "Response code: 204.\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketPolicy(
				$this->options->get("testBucket"),
				$this->options->get("testPolicy")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketLogging method, which sets the logging parameters for a bucket
	 * and specifies permissions for who can view and modify the logging parameters
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketLogging()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?logging";
		$headers = array();

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
			. "<BucketLoggingStatus xmlns=\"http://doc.s3.amazonaws.com/2006-03-01\">\n"
			. "<LoggingEnabled>\n"
			. "<TargetBucket>gsoc-test-2</TargetBucket>\n"
			. "<TargetPrefix>mybucket-access_log</TargetPrefix>\n"
			. "<TargetGrants>\n"
			. "<Grant>\n"
			. "<Grantee xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"AmazonCustomerByEmail\">\n"
			. "<EmailAddress>alex.ukf@gmail.com</EmailAddress>\n"
			. "</Grantee>\n"
			. "<Permission>READ</Permission>\n"
			. "</Grant>\n"
			. "</TargetGrants>\n"
			. "</LoggingEnabled>\n"
			. "</BucketLoggingStatus>\n";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketLogging(
				$this->options->get("testBucket"),
				$this->options->get("testLogging")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketNotification method, which enables notifications of
	 * specified events for a bucket
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketNotification()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?notification";
		$headers = array();

		$content = "<NotificationConfiguration>\n"
			. "<TopicConfiguration>\n"
			. "<Topic>arn:aws:sns:us-east-1:123456789012:myTopic</Topic>\n"
			. "<Event>s3:ReducedRedundancyLostObject</Event>\n"
			. "</TopicConfiguration>\n"
			. "</NotificationConfiguration>";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketNotification(
				$this->options->get("testBucket"),
				$this->options->get("testNotification")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putBucketTagging which adds a set of tags to an existing bucket
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketTagging()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?tagging";
		$headers = array();

		$content = "<Tagging>\n"
			. "<TagSet>\n"
			. "<Tag>\n"
			. "<Key>Project</Key>\n"
			. "<Value>Project One</Value>\n"
			. "</Tag>\n"
			. "<Tag>\n"
			. "<Key>User</Key>\n"
			. "<Value>alexukf</Value>\n"
			. "</Tag>\n"
			. "</TagSet>\n"
			. "</Tagging>";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketTagging(
				$this->options->get("testBucket"),
				$this->options->get("testTagging")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Thests the putBucketRequestPayment, which sets the request payment
	 * configuration of a bucket
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketRequestPayment()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?requestPayment";
		$headers = array();

		$content = "<RequestPaymentConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n"
			. "<Payer>Requester</Payer>\n"
			. "</RequestPaymentConfiguration>";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketRequestPayment(
				$this->options->get("testBucket"),
				$this->options->get("testRequestPayment")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Thests the putBucketVersioning, using only the Status request element
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketVersioningWithoutMfaDelete()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?versioning";
		$headers = array();

		$content = "<VersioningConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n"
			. "<Status>Enabled</Status>\n"
			. "</VersioningConfiguration>";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketVersioning(
				$this->options->get("testBucket"),
				$this->options->get("testVersioningWithoutMfaDelete")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Thests the putBucketVersioning, using both Status and MfaDelete request elements
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketVersioningWithMfaDelete()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?versioning";
		$headers = array();

		$content = "<VersioningConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n"
			. "<Status>Enabled</Status>\n"
			. "<MfaDelete>Enabled</MfaDelete>\n"
			. "</VersioningConfiguration>";

		$headers["x-amz-mfa"] = $this->options->get("serialNr") . " " . $this->options->get("tokenCode");
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketVersioning(
				$this->options->get("testBucket"),
				$this->options->get("testVersioningWithMfaDelete"),
				$this->options->get("serialNr"),
				$this->options->get("tokenCode")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Thests the putBucketWebsite, which sets the configuration of the website
	 * that is specified in the website subresource
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutBucketWebsite()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?website";
		$headers = array();

		$content = "<WebsiteConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n"
			. "<IndexDocument>\n"
			. "<Suffix>index.html</Suffix>\n"
			. "</IndexDocument>\n"
			. "<ErrorDocument>\n"
			. "<Key>SomeErrorDocument.html</Key>\n"
			. "</ErrorDocument>\n"
			. "<RoutingRules>\n"
			. "<RoutingRule>\n"
			. "<Condition>\n"
			. "<KeyPrefixEquals>docs/</KeyPrefixEquals>\n"
			. "</Condition>\n"
			. "<Redirect>\n"
			. "<ReplaceKeyPrefixWith>documents/</ReplaceKeyPrefixWith>\n"
			. "</Redirect>\n"
			. "</RoutingRule>\n"
			. "</RoutingRules>\n"
			. "</WebsiteConfiguration>";

		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));

		$headers["Date"] = date("D, d M Y H:i:s O");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->putBucketWebsite(
				$this->options->get("testBucket"),
				$this->options->get("testWebsite")
			),
			$this->equalTo($expectedResult)
		);
	}
}
