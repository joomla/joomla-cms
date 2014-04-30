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
class JAmazons3ObjectsPostTest extends PHPUnit_Framework_TestCase
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
		$this->options->set(
			"testObjects",
			array(
				array(
					"Key" => "404.txt"
				),
				array(
					"Key" => "SampleDocument.txt",
					"VersionId" => "OYcLXagmS.WaD..oyH4KRguB95_YhLs7",
				),
			)
		);
		$this->options->set("serialNr", "20899872");
		$this->options->set("tokenCode", "301749");
		$this->options->set(
			"testFields",
			array(
				"key" => "testFile.txt",
				"file" => "test content",
			)
		);
		$this->options->set("testObject", "testObject");
		$this->options->set("days", "5");

		$this->client = $this->getMock('JHttp', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3Objects($this->options, $this->client);
	}

	/**
	 * Tests the deleteObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testDeleteMultipleObjects()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?delete";

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
			. "<Delete>\n"
			. "<Object>\n"
			. "<Key>404.txt</Key>\n"
			. "</Object>\n"
			. "<Object>\n"
			. "<Key>SampleDocument.txt</Key>\n"
			. "<VersionId>OYcLXagmS.WaD..oyH4KRguB95_YhLs7</VersionId>\n"
			. "</Object>\n"
			. "</Delete>";

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('post')
			->with($url, $content)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->post->deleteMultipleObjects(
				$this->options->get("testBucket"),
				$this->options->get("testObjects")
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
	public function testDeleteMultipleObjectsMfa()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url") . "/?delete";

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
			. "<Delete>\n"
			. "<Quiet>true</Quiet>\n"
			. "<Object>\n"
			. "<Key>404.txt</Key>\n"
			. "</Object>\n"
			. "<Object>\n"
			. "<Key>SampleDocument.txt</Key>\n"
			. "<VersionId>OYcLXagmS.WaD..oyH4KRguB95_YhLs7</VersionId>\n"
			. "</Object>\n"
			. "</Delete>";

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('post')
			->with($url, $content)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->post->deleteMultipleObjects(
				$this->options->get("testBucket"),
				$this->options->get("testObjects"),
				true,
				$this->options->get("serialNr"),
				$this->options->get("tokenCode")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the postObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPostObject()
	{
		$testFields = $this->options->get("testFields");
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $testFields["key"];

		$content = $testFields["file"];

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('post')
			->with($url, $content)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->post->postObject(
				$this->options->get("testBucket"),
				$this->options->get("testFields")
			),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the postObjectRestore method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testPostObjectRestore()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject") . "?restore";

		$content = "<RestoreRequest xmlns=\"http://s3.amazonaws.com/doc/2006-3-01\">\n"
			. "<Days>" . $this->options->get("testDays") . "</Days>\n"
			. "</RestoreRequest>\n";

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "<test>response</test>";
		$expectedResult = new SimpleXMLElement($returnData->body);

		$this->client->expects($this->once())
			->method('post')
			->with($url, $content)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->post->postObjectRestore(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("testDays")
			),
			$this->equalTo($expectedResult)
		);
	}
}
