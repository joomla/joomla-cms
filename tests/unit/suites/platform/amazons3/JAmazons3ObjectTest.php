<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/object.php';
require_once __DIR__ . '/stubs/JAmazons3ObjectMock.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3ObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Issues  Object under test.
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
		$this->object = new JAmazons3ObjectMock($this->options);
	}

	/**
	 * Tests the processResponse method using a sample response.
	 *
	 * @return void
	 */
	public function testProcessResponse()
	{
		$response = new JHttpResponse;
		$response->code = 200;
		$response->body = "<ListAllMyBucketsResult xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">"
			. "<Owner><ID>6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96</ID>"
			. "<DisplayName>alex.ukf</DisplayName></Owner><Buckets><Bucket><Name>jgsoc</Name>"
			. "<CreationDate>2013-06-29T10:29:36.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>";
		$expectedResult = new SimpleXMLElement($response->body);

		$this->assertThat(
			$this->object->processResponse($response),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the create authorization method.
	 *
	 * @return void
	 */
	public function testCreateAuthorization()
	{
		$this->options->set('api.accessKeyId', 'MyTestAccessKeyId');
		$this->options->set('api.secretAccessKey', 'MyTestSecretAccessKey');

		$url = "https://" . $this->options->get("api.url") . "/";
		$headers = array(
			"Date" => "Tue, 16 Jul 2013 19:39:06 +0300",
		);
		$expectedResult = "AWS MyTestAccessKeyId:yLA1qJ2sCHVRNPjpbRvcIA9fwGY=";

		$this->assertThat(
			$this->object->createAuthorization("GET", $url, $headers),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the createCanonicalizedAmzHeaders method.
	 *
	 * @return void
	 */
	public function testCreateCanonicalizedAmzHeaders()
	{
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-acl" => "public-read",
			"content-type" => "application/x-download",
			"Content-MD5" => "4gJE4saaMU4BqNR0kLY+lw==",
			"X-Amz-Meta-ReviewedBy" => array(
				"joe@johnsmith.net",
				"jane@johnsmith.net",
			),
			"X-Amz-Meta-FileChecksum" => "0x02661779",
			"X-Amz-Meta-ChecksumAlgorithm" => "crc32",
		);

		$expectedResult = "x-amz-acl:public-read\n"
			. "x-amz-meta-checksumalgorithm:crc32\n"
			. "x-amz-meta-filechecksum:0x02661779\n"
			. "x-amz-meta-reviewedby:joe@johnsmith.net,jane@johnsmith.net\n";

		$this->assertThat(
			$this->object->createCanonicalizedAmzHeaders($headers),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the createCanonicalizedResource method.
	 *
	 * @return void
	 */
	public function testCreateCanonicalizedResource()
	{
		$url = "https://jgsoc." . $this->options->get("api.url") . "/photos/puppy.jpg?acl&versionId=3&lifecycle=inf";

		$expectedResult = "/jgsoc/photos/puppy.jpg?acl&lifecycle=inf&versionId=3";

		$this->assertThat(
			$this->object->createCanonicalizedResource($url),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the filterValidSubresources method.
	 *
	 * @return void
	 */
	public function testFilterValidSubresources()
	{
		$parameters = array(
			"acl",
			"versionId=3",
			"lifecycle=inf",
			"invalidParameter",
			"invalidParameter=value"
		);
		$validParameters = array(
			"acl",
			"versionId=3",
			"lifecycle=inf",
		);

		$this->assertThat(
			$this->object->filterValidSubresources($parameters),
			$this->equalTo($validParameters)
		);
	}
}
