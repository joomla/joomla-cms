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
class JAmazons3OperationsObjectsDeleteTest extends PHPUnit_Framework_TestCase
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
		$this->options->set('testObject', 'testObject');
		$this->options->set("versionId", "UIORUnfndfiufdisojhr398493jfdkjFJjkndnqUifhnw89493jJFJ");
		$this->options->set("serialNr", "20899872");
		$this->options->set("tokenCode", "301749");
		$this->options->set("uploadId", "VXBsb2FkIElEIGZvciBlbHZpbmcncyBteS1tb3ZpZS5tMnRzIHVwbG9hZ");

		$this->client = $this->getMock('JAmazons3Http', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3OperationsObjects($this->options, $this->client);
	}

	/**
	 * Common test operations for methods which use DELETE requests
	 *
	 * @param   string   $objectName  The name of the object that will be deleted
	 * @param   boolean  $versioning  Tells whether versioning should be used in the request
	 * @param   boolean  $mfa         Tells whether the x-amz-mfa should be included
	 * @param   string   $uploadId    The upload id
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	protected function commonDeleteTestOperations($objectName, $versioning = false, $mfa = false, $uploadId = false)
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/" . $objectName;
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if ($uploadId)
		{
			$url .= "?uploadId=" . $this->options->get("uploadId");
		}
		else
		{
			$headers["Content-Length"] = "0";

			if ($versioning)
			{
				$url .= "?versionId=" . $this->options->get("versionId");
			}
		}

		if ($mfa)
		{
			$headers["x-amz-mfa"] = $this->options->get("serialNr") . " " . $this->options->get("tokenCode");
		}

		$authorization = $this->object->createAuthorization("DELETE", $url, $headers);
		$headers['Authorization'] = $authorization;

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('delete')
			->with($url, $headers)
			->will($this->returnValue($returnData));

		return $expectedResult;
	}

	/**
	 * Tests the deleteObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteObject()
	{
		$expectedResult = $this->commonDeleteTestOperations($this->options->get("testObject"));
		$this->assertThat(
			$this->object->delete->deleteObject($this->options->get("testBucket"), $this->options->get("testObject")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteObjectWithVersioning()
	{
		$expectedResult = $this->commonDeleteTestOperations(
			$this->options->get("testObject"), true
		);
		$this->assertThat(
			$this->object->delete->deleteObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("versionId")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteObjectWithVersioningInMFAEnabledBuckets()
	{
		$expectedResult = $this->commonDeleteTestOperations(
			$this->options->get("testObject"), true, true
		);
		$this->assertThat(
			$this->object->delete->deleteObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("versionId"),
				$this->options->get("serialNr"),
				$this->options->get("tokenCode")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the abortMultipartUpload method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testAbortMultipartUpload()
	{
		$expectedResult = $this->commonDeleteTestOperations(
			$this->options->get("testObject"),
			false,
			false,
			$this->options->get("uploadId")
		);
		$this->assertThat(
			$this->object->delete->abortMultipartUpload(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("uploadId")
			),
			$this->equalTo($expectedResult)
		);
	}
}
