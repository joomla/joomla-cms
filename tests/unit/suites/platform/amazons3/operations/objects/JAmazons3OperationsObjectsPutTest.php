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
}
