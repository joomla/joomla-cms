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
class JAmazons3OperationsBucketsDeleteTest extends PHPUnit_Framework_TestCase
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

		$this->client = $this->getMock('JAmazons3Http', array('delete', 'get', 'head', 'put'));

		$this->object = new JAmazons3OperationsBuckets($this->options, $this->client);
	}

	/**
	 * Common test operations for methods which use DELETE requests
	 *
	 * @param   string  $subresource  The subresource that is used for creating the DELETE request.
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	protected function commonDeleteTestOperations($subresource)
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/" . $subresource;
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
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
	 * Tests the deleteBucket method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucket()
	{
		$expectedResult = $this->commonDeleteTestOperations("");
		$this->assertThat(
			$this->object->delete->deleteBucket($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteBucketCors method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucketCors()
	{
		$expectedResult = $this->commonDeleteTestOperations("?cors");
		$this->assertThat(
			$this->object->delete->deleteBucketCors($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteBucketLifecycle method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucketLifecycle()
	{
		$expectedResult = $this->commonDeleteTestOperations("?lifecycle");
		$this->assertThat(
			$this->object->delete->deleteBucketLifecycle($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteBucketPolicy method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucketPolicy()
	{
		$expectedResult = $this->commonDeleteTestOperations("?policy");
		$this->assertThat(
			$this->object->delete->deleteBucketPolicy($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteBucketTagging method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucketTagging()
	{
		$expectedResult = $this->commonDeleteTestOperations("?tagging");
		$this->assertThat(
			$this->object->delete->deleteBucketTagging($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the deleteBucketWebsite method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteBucketWebsite()
	{
		$expectedResult = $this->commonDeleteTestOperations("?website");
		$this->assertThat(
			$this->object->delete->deleteBucketWebsite($this->options->get("testBucket")),
			$this->equalTo($expectedResult)
		);
	}
}
