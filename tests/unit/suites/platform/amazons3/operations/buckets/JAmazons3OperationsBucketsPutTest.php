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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful.\n";
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
		$returnData->body = "The request was successful. Amazon S3 returned a 204 No Content response.\n";
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
}
