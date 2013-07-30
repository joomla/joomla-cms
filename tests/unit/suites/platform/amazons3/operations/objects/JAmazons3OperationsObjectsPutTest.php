<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/operations/objects.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3OperationsObjectsPutTest extends PHPUnit_Framework_TestCase
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
		$this->options->set("testObject", "testObject");
		$this->options->set("testContent", "testContent");
		$this->options->set("testObjectCopy", "testObjectCopy");
		$this->options->set(
			"testRequestHeaders",
			array(
				"x-amz-grant-read" => "uri=\"http://acs.amazonaws.com/groups/global/AllUsers\"",
				"x-amz-grant-write-acp" => "emailAddress=\"alex.ukf@gmail.com\"",
				"x-amz-grant-full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\""
			)
		);
		$this->options->set("testPartNumber", "1");
		$this->options->set("testUploadId", "VCVsb2FkIElEIGZvciBlbZZpbmcncyBteS1tb3ZpZS5tMnRzIHVwbG9hZR");

		$this->client = $this->getMock('JAmazons3Http', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3OperationsObjects($this->options, $this->client);
	}

	/**
	 * Tests the putObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutObject()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject");
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		$content = $this->options->get("testContent");

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers['Authorization'] = $authorization;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue(null));

		$this->assertThat(
			$this->object->put->putObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testContent")
			),
			$this->equalTo(null)
		);
	}

	/**
	 * Tests the putObjectAcl method with canned ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutObjectAclCanned()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?acl";
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
			$this->object->put->putObjectAcl(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				array(
					"acl" => "public-read"
				)
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the putObjectAcl method with explicitly specified ACL permissions
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutObjectAclExplicit()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?acl";
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
			$this->object->put->putObjectAcl(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
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
	 * Tests the putObjectCopy method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPutObjectCopy()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObjectCopy");
		$content = "";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-grant-read" => "uri=\"http://acs.amazonaws.com/groups/global/AllUsers\"",
			"x-amz-grant-write-acp" => "emailAddress=\"alex.ukf@gmail.com\"",
			"x-amz-grant-full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\""
		);
		$headers["x-amz-copy-source"] = "/" . $this->options->get("testBucket")
			. "/" . $this->options->get("testObject");
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers['Authorization'] = $authorization;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue(null));

		$this->assertThat(
			$this->object->put->putObjectCopy(
				$this->options->get("testBucket"),
				$this->options->get("testObjectCopy"),
				"/" . $this->options->get("testBucket") . "/" . $this->options->get("testObject"),
				$this->options->get("testRequestHeaders")
			),
			$this->equalTo(null)
		);
	}

	/**
	 * Tests the initiateMultipartUpload method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testInitiateMultipartUpload()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?uploads";
		$content = "";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-grant-read" => "uri=\"http://acs.amazonaws.com/groups/global/AllUsers\"",
			"x-amz-grant-write-acp" => "emailAddress=\"alex.ukf@gmail.com\"",
			"x-amz-grant-full-control" => "id=\"6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96\""
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers['Authorization'] = $authorization;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue(null));

		$this->assertThat(
			$this->object->put->initiateMultipartUpload(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testRequestHeaders")
			),
			$this->equalTo(null)
		);
	}

	/**
	 * Tests the uploadPart method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testUploadPart()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?partNumber=" . $this->options->get("testPartNumber")
			. "&uploadId=" . $this->options->get("testUploadId");
		$content = "";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$authorization = $this->object->createAuthorization("PUT", $url, $headers);
		$headers['Authorization'] = $authorization;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content, $headers)
			->will($this->returnValue(null));

		$this->assertThat(
			$this->object->put->uploadPart(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testPartNumber"),
				$this->options->get("testUploadId")
			),
			$this->equalTo(null)
		);
	}
}
