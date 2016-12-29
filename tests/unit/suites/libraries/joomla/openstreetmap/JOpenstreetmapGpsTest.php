<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOpenstreetmapGps.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @since       13.1
 */
class JOpenstreetmapGpsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Openstreetmap object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  13.1
	 */
	protected $input;

	/**
	 * @var    JOpenstreetmapGps Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    JOpenstreetmapOauth  Authentication object for the Openstreetmap object.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample XML.
	 * @since  13.1
	 */
	protected $sampleXml = <<<XML
<?xml version='1.0'?>
<osm></osm>
XML;

	/**
	 * @var    string  Sample XML error message.
	* @since  13.1
	*/
	protected $errorString = <<<XML
<?xml version='1.0'?>
<osm>ERROR</osm>
XML;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 * @since  3.6
	 */
	protected $backupServer;

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
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";

		$access_token = array('key' => 'token_key', 'secret' => 'token_secret');

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->oauth = new JOpenstreetmapOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JOpenstreetmapGps($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('sendheaders', true);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->input, $this->client, $this->oauth, $this->object);
	}

	/**
	 * Tests the retrieveGps method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testRetrieveGps()
	{
		$left = '1';
		$bottom = '1';
		$right = '2';
		$top = '2';
		$page = '0';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'trackpoints?bbox=' . $left . ',' . $bottom . ',' . $right . ',' . $top . '&page=' . $page;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->retrieveGps($left, $bottom, $right, $top, $page),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the retrieveGps method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testRetrieveGpsFailure()
	{
		$left = '1';
		$bottom = '1';
		$right = '2';
		$top = '2';
		$page = '0';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'trackpoints?bbox=' . $left . ',' . $bottom . ',' . $right . ',' . $top . '&page=' . $page;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->retrieveGps($left, $bottom, $right, $top, $page);
	}

	/**
	 * Tests the uploadTrace method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUploadTrace()
	{

		$file = '/htdocs/new_trace.gpx';
		$description = 'Test Trace';
		$tags = '';
		$public = '1';
		$visibility = '1';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'gpx/create';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->uploadTrace($file, $description, $tags, $public, $visibility, $username, $password),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the uploadTrace method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testUploadTraceFailure()
	{

		$file = '/htdocs/new_trace.gpx';
		$description = 'Test Trace';
		$tags = '';
		$public = '1';
		$visibility = '1';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'gpx/create';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->uploadTrace($file, $description, $tags, $public, $visibility, $username, $password);
	}

	/**
	 * Tests the downloadTraceMetadetails method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDownloadTraceMetadetails()
	{

		$id = '123';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'gpx/' . $id . '/details';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->downloadTraceMetadetails($id, $username, $password),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the downloadTraceMetadetails method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testDownloadTraceMetadetailsFailure()
	{

		$id = '123';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'gpx/' . $id . '/details';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->downloadTraceMetadetails($id, $username, $password);
	}

	/**
	 * Tests the downloadTraceMetadata method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDownloadTraceMetadata()
	{

		$id = '123';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'gpx/' . $id . '/data';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->downloadTraceMetadata($id, $username, $password),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the downloadTraceMetadata method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testDownloadTraceMetadataFailure()
	{

		$id = '123';
		$username = 'username';
		$password = 'password';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'gpx/' . $id . '/data';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->downloadTraceMetadata($id, $username, $password);
	}
}
