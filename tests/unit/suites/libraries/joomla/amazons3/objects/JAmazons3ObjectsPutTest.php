<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/objects.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3ObjectsPutTest extends PHPUnit_Framework_TestCase
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
		$this->options->set(
			"testCopySource",
			array(
				"x-amz-copy-source" => "/jgsoc/my-movie.m2ts?versionId=OYcLXagmS.WaD..oyH4KRguB95_YhLs7",
				"x-amz-copy-source-range" => "bytes=500-6291456",
			)
		);
		$this->options->set(
			"testParts",
			array(
				array(
					"PartNumber" => "1",
					"ETag" => "a54357aff0632cce46d942af68356b38",
				),
				array(
					"PartNumber" => "2",
					"ETag" => "0c78aef83f66abc1fa1e8477f296d394",
				),
				array(
					"PartNumber" => "3",
					"ETag" => "acbd18db4cc2f85cedef654fccc4a4d8",
				),
			)
		);

		$this->client = $this->getMock('JHttp', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3Objects($this->options, $this->client);
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

		$content = $this->options->get("testContent");

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
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

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n"
			. "Response headers: []\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "")
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

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n"
			. "Response headers: []\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, "")
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

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
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

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
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

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
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

	/**
	 * Tests the uploadPartCopy method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testUploadPartCopy()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?partNumber=" . $this->options->get("testPartNumber")
			. "&uploadId=" . $this->options->get("testUploadId");
		$content = "";

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
			->will($this->returnValue(null));

		$this->assertThat(
			$this->object->put->uploadPartCopy(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testPartNumber"),
				$this->options->get("testUploadId"),
				$this->options->get("testCopySource")
			),
			$this->equalTo(null)
		);
	}

	/**
	 * Tests the completeMultipartUpload method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testCompleteMultipartUpload()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?uploadId=" . $this->options->get("testUploadId");

		$content = "<CompleteMultipartUpload>\n"
			. "<Part>\n"
			. "<PartNumber>1</PartNumber>\n"
			. "<ETag>a54357aff0632cce46d942af68356b38</ETag>\n"
			. "</Part>\n"
			. "<Part>\n"
			. "<PartNumber>2</PartNumber>\n"
			. "<ETag>0c78aef83f66abc1fa1e8477f296d394</ETag>\n"
			. "</Part>\n"
			. "<Part>\n"
			. "<PartNumber>3</PartNumber>\n"
			. "<ETag>acbd18db4cc2f85cedef654fccc4a4d8</ETag>\n"
			. "</Part>\n"
			. "</CompleteMultipartUpload>";

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n"
			. "Response headers: []\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('put')
			->with($url, $content)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->put->completeMultipartUpload(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testUploadId"),
				$this->options->get("testParts")
			),
			$this->equalTo($expectedResult)
		);
	}
}
