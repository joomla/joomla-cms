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
		$returnData->body = "The bucket exists and you have permission to access it.\n";
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
		$returnData->body = "The bucket exists and you have permission to access it.\n";
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
		unset($headers["Content-type"]);

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "The bucket exists and you have permission to access it.\n";
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
}
