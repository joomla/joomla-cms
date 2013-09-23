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
class JAmazons3ObjectsHeadTest extends PHPUnit_Framework_TestCase
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
		$this->options->set('testObject', 'testObject');
		$this->options->set('versionId', '3/L4kqtJlcpXroDTDmpUMLUo');
		$this->options->set('range', 'bytes=0-9');

		$this->client = $this->getMock('JHttp', array('delete', 'get', 'head', 'put', 'post', 'optionss3'));

		$this->object = new JAmazons3Objects($this->options, $this->client);
	}

	/**
	 * Tests the headObject method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testHeadObject()
	{
		$url = "https://" . $this->options->get("testBucket") . "." . $this->options->get("api.url")
			. "/" . $this->options->get("testObject");

		$url .= "?versionId=" . $this->options->get("versionId");

		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = "Response code: " . $returnData->code . ".\n"
			. "Response headers: []\n";
		$expectedResult = $returnData->body;

		$this->client->expects($this->once())
			->method('head')
			->with($url)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->head->headObject(
				$this->options->get("testBucket"),
				$this->options->get("testObject"),
				$this->options->get("versionId"),
				array("Range" => $this->options->get("range"))
			),
			$this->equalTo($expectedResult)
		);
	}
}
