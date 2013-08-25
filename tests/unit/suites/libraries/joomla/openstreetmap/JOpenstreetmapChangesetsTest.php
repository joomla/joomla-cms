<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOpenstreetmapChangesets.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @since       13.1
 */
class JOpenstreetmapChangesetsTest extends TestCase
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
	 * @var    JOpenstreetmapChangesets Object under test.
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
	 * Sets up the fixture, for example, opens a network connection.
	* This method is called before a test is executed.
	*
	* @access protected
	*
	* @return void
	*/
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";

		$access_token = array('key' => 'token_key', 'secret' => 'token_secret');

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JOpenstreetmapOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JOpenstreetmapChangesets($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('sendheaders', true);
	}

	/**
	 * Tests the createChangeset method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testCreateChangeset()
	{
		$changeset = array
		(
				array
				(
						"comment" => "my changeset comment",
						"created_by" => "Josm"
				),
				array
				(
						"A" => "a",
						"B" => "b"
				)
		);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		// 		$path = 'http://api.openstreetmap.org/api/0.6/changeset/create';
		$path = 'changeset/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->createChangeset($changeset),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the createChangeset method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testCreateChangesetFailure()
	{
		$changeset = array
		(
				array
				(
						"comment" => "my changeset comment",
						"created_by" => "JOsm"
				),
				array
				(
						"A" => "a",
						"B" => "b"
				)
		);

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'changeset/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->createChangeset($changeset);
	}

	/**
	 * Tests the readChangeset method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testReadChangeset()
	{
		$id = '14153708';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->changeset = new SimpleXMLElement($this->sampleXml);

		$path = 'changeset/' . $id;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->readChangeset($id),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the readChangeset method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testReadChangesetFailure()
	{
		$id = '14153708';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->changeset = new SimpleXMLElement($this->sampleXml);

		$path = 'changeset/' . $id;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->readChangeset($id);
	}

	/**
	 * Tests the updateChangeset method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testUpdateChangeset()
	{
		$id = '14153708';
		$tags = array
		(
				"comment" => "my changeset comment",
				"created_by" => "JOsm (en)"
		);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'changeset/' . $id;

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->updateChangeset($id, $tags),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the updateChangeset method - failure
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testUpdateChangesetFailure()
	{
		$id = '14153708';
		$tags = array
		(
				"comment" => "my changeset comment",
				"created_by" => "JOsm (en)"
		);

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'changeset/' . $id;

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->updateChangeset($id, $tags);
	}

	/**
	 * Tests the closeChangeset method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testCloseChangeset()
	{
		$id = '14153708';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'changeset/' . $id . '/close';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertNull($this->object->closeChangeset($id));
	}

	/**
	 * Tests the closeChangeset method - failure
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testCloseChangesetFailure()
	{
		$id = '14153708';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'changeset/' . $id . '/close';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->closeChangeset($id);
	}

	/**
	 * Tests the downloadChangeset method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDownloadChangeset()
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->create = new SimpleXMLElement($this->sampleXml);

		$path = 'changeset/' . $id . '/download';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->downloadChangeset($id),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the downloadChangeset method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testDownloadChangesetFailure()
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->create = new SimpleXMLElement($this->sampleXml);

		$path = 'changeset/' . $id . '/download';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->downloadChangeset($id);
	}

	/**
	 * Tests the expandBBoxChangeset method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testExpandBBoxChangeset()
	{
		$id = '14153708';
		$node_list = array(array(4,5),array(6,7));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'changeset/' . $id . '/expand_bbox';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->expandBBoxChangeset($id, $node_list),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the expandBBoxChangeset method - failure
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testExpandBBoxChangesetFailure()
	{
		$id = '14153708';
		$node_list = array(array(4,5),array(6,7));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'changeset/' . $id . '/expand_bbox';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->expandBBoxChangeset($id, $node_list);
	}

	/**
	 * Tests the queryChangeset method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testQueryChangeset()
	{
		$param = 'open';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->osm = new SimpleXMLElement($this->sampleXml);

		$path = 'changesets/' . $param;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->queryChangeset($param),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the queryChangeset method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testQueryChangesetFailure()
	{
		$param = 'open';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->osm = new SimpleXMLElement($this->sampleXml);

		$path = 'changesets/' . $param;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->queryChangeset($param);
	}

	/**
	 * Tests the diffUploadChangeset method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testDiffUploadChangeset()
	{
		$id = '123';
		$xml = '<osmChange>
				<modify>
				<node id="12" timestamp="2012-12-02T00:00:00.0+11:00" lat="-33.9133118622908" lon="151.117335519304">
				<tag k="created_by" v="JOsm"/>
				</node>
				</modify>
				</osmChange>';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'changeset/' . $id . '/upload';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->diffUploadChangeset($xml, $id),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the diffUploadChangeset method - failure
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testDiffUploadChangesetFailure()
	{
		$id = '123';
		$xml = '<osmChange>
				<modify>
				<node id="12" timestamp="2007-01-02T00:00:00.0+11:00" lat="-33.9133118622908" lon="151.117335519304">
				<tag k="created_by" v="JOsm"/>
				</node>
				</modify>
				</osmChange>';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'changeset/' . $id . '/upload';

		$this->client->expects($this->once())
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->diffUploadChangeset($xml, $id);
	}

}
