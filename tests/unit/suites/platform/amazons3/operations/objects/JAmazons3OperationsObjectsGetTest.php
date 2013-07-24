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
class JAmazons3OperationsObjectsGetTest extends PHPUnit_Framework_TestCase
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
		$this->options->set('versionId', '3/L4kqtJlcpXroDTDmpUMLUo');
		$this->options->set('range', '0-9');
		$this->options->set('uploadId', 'XXBsb2FkIElEIGZvciBlbHZpbmcncyVcdS1tb3ZpZS5tMnRzEEEwbG9hZA');
		$this->options->set('max-parts', '2');
		$this->options->set('part-number-marker', '1');

		$this->client = $this->getMock('JAmazons3Http', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3OperationsObjects($this->options, $this->client);
	}

	/**
	 * Common test operations for methods which use GET requests
	 *
	 * @param   string  $subresource  The subresource to be added to the url
	 * @param   string  $versionId    The version id
	 * @param   string  $range        The range of bytes to be returned
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	protected function commonGetTestOperations($subresource = null, $versionId = null, $range = null)
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject");

		if (! is_null($versionId))
		{
			$url .= "?versionId=" . $versionId;
		}

		if (! is_null($subresource))
		{
			if (is_null($versionId))
			{
				$url .= "?";
			}
			else
			{
				$url .= "&";
			}

			$url .= $subresource;
		}

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if (! is_null($range))
		{
			$headers['Range'] = "bytes=" . $range;
		}

		$authorization = $this->object->createAuthorization("GET", $url, $headers);
		$headers['Authorization'] = $authorization;

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('get')
			->with($url, $headers)
			->will($this->returnValue($returnData));

		return $expectedResult;
	}

	/**
	 * Tests the getObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObject()
	{
		$expectedResult = $this->commonGetTestOperations();
		$this->assertThat(
			$this->object->get->getObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObject method with a version Id
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectVersion()
	{
		$expectedResult = $this->commonGetTestOperations(null, $this->options->get("versionId"));
		$this->assertThat(
			$this->object->get->getObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("versionId")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObject method with a version Id
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectRange()
	{
		$expectedResult = $this->commonGetTestOperations(null, null, $this->options->get("range"));
		$this->assertThat(
			$this->object->get->getObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				null,
				$this->options->get("range")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObjectAcl method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectAcl()
	{
		$expectedResult = $this->commonGetTestOperations("acl");
		$this->assertThat(
			$this->object->get->getObjectAcl(
				$this->options->get("testBucket"),
				$this->options->get("testObject")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObjectAcl method with a version Id
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectVersionAcl()
	{
		$expectedResult = $this->commonGetTestOperations("acl", $this->options->get("versionId"));
		$this->assertThat(
			$this->object->get->getObjectAcl(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("versionId")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObjectAcl method with a version Id
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectRangeAcl()
	{
		$expectedResult = $this->commonGetTestOperations("acl", null, $this->options->get("range"));
		$this->assertThat(
			$this->object->get->getObjectAcl(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				null,
				$this->options->get("range")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getObjectTorrent method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetObjectTorrent()
	{
		$expectedResult = $this->commonGetTestOperations("torrent");
		$this->assertThat(
			$this->object->get->getObjectTorrent(
				$this->options->get("testBucket"),
				$this->options->get("testObject")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the listParts method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testListParts()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject");
		$url .= "?uploadId=" . $this->options->get("uploadId");
		$url .= "&max-parts=" . $this->options->get("max-parts");
		$url .= "&part-number-marker=" . $this->options->get("part-number-marker");

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		$authorization = $this->object->createAuthorization("GET", $url, $headers);
		$headers['Authorization'] = $authorization;

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('get')
			->with($url, $headers)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get->listParts(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				array(
					"uploadId" => $this->options->get("uploadId"),
					"max-parts" => $this->options->get("max-parts"),
					"part-number-marker" => $this->options->get("part-number-marker")
				)
			),
			$this->equalTo($expectedResult)
		);
	}
}
