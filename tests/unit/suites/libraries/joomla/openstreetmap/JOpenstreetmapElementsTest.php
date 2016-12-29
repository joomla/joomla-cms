<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOpenstreetmapElements.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @since       13.1
 */
class JOpenstreetmapElementsTest extends TestCase
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
	 * @var    JOpenstreetmapElements Object under test.
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

		$this->object = new JOpenstreetmapElements($this->options, $this->client, $this->oauth);

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
	 * Tests the createNode method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testCreateNode()
	{
		$changeset = '123';
		$latitude = '2';
		$longitude = '2';
		$tags = array("A" => "a","B" => "b");

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'node/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->createNode($changeset, $latitude, $longitude, $tags),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the createNode method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testCreateNodeFailure()
	{
		$changeset = '123';
		$latitude = '2';
		$longitude = '2';
		$tags = array("A" => "a","B" => "b");

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'node/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->createNode($changeset, $latitude, $longitude, $tags);
	}

	/**
	 * Tests the createWay method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testCreateWay()
	{
		$changeset = '123';
		$tags = array("A" => "a","B" => "b");
		$nds = array("a", "b");

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'way/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->createWay($changeset, $tags, $nds),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the createWay method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testCreateWayFailure()
	{
		$changeset = '123';
		$tags = array("A" => "a","B" => "b");
		$nds = array("a", "b");

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'way/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->createWay($changeset, $tags, $nds);
	}

	/**
	 * Tests the createRelation method
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function testCreateRelation()
	{
		$changeset = '123';
		$tags = array("A" => "a","B" => "b");
		$members = array(array("type" => "node","role" => "stop","ref" => "123"),array("type" => "way","ref" => "123"));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = 'relation/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->createRelation($changeset, $tags, $members),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the createRelation method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testCreateRelationFailure()
	{
		$changeset = '123';
		$tags = array("A" => "a","B" => "b");
		$members = array(array("type" => "node","role" => "stop","ref" => "123"),array("type" => "way","ref" => "123"));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = 'relation/create';

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->createRelation($changeset, $tags, $members);
	}

	/**
	 * Provides test data for element type.
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedElement()
	{
		// Element type
		return array(
				array('node'),
				array('way'),
				array('relation')
		);
	}

	/**
	 * Provides test data for element type - faliures
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedElementFailure()
	{
		// Element type
		return array(
				array('node'),
				array('way'),
				array('relation'),
				array('other')
		);
	}

	/**
	 * Tests the readElement method
	 *
	 * @param   string  $element  Element type
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testReadElement($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->readElement($element, $id),
				$this->equalTo($returnData->$element)
		);
	}

	/**
	 * Tests the readElement method - failure
	 *
	 * @param   string  $element  Element type
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testReadElementFailure($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id;

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->readElement($element, $id);
	}

	/**
	 * Tests the updateElement method
	 *
	 * @param   string  $element  Element type
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testUpdateElement($element)
	{
		$id = '123';
		$xml = "<?xml version='1.0'?><osm><element></element></osm>";

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = $element . '/' . $id;

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->updateElement($element, $xml, $id),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the updateElement method - failure
	 *
	 * @param   string  $element  Element type
	 *
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testUpdateElementFailure($element)
	{
		$id = '123';
		$xml = "<?xml version='1.0'?><osm><element></element></osm>";

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $element . '/' . $id;

		$this->client->expects($this->any())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->updateElement($element, $xml, $id);
	}

	/**
	 * Tests the deleteElement method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  array
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testDeleteElement($element)
	{
		$id = '123';
		$version = '1.0';
		$changeset = '123';
		$latitude = '2';
		$longitude = '2';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = $element . '/' . $id;

		$this->client->expects($this->once())
		->method('delete')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->deleteElement($element, $id, $version, $changeset, $latitude, $longitude),
				$this->equalTo($this->sampleXml)
		);
	}

	/**
	 * Tests the deleteElement method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  array
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testDeleteElementFailure($element)
	{
		$id = '123';
		$version = '1.0';
		$changeset = '123';
		$latitude = '2';
		$longitude = '2';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $element . '/' . $id;

		$this->client->expects($this->any())
		->method('delete')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->deleteElement($element, $id, $version, $changeset, $latitude, $longitude);
	}

	/**
	 * Tests the historyOfElement method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testHistoryOfElement($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/history';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->historyOfElement($element, $id),
				$this->equalTo($returnData->$element)
		);
	}

	/**
	 * Tests the historyOfElement method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testHistoryOfElementFailure($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/history';

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->historyOfElement($element, $id);
	}

	/**
	 * Tests the versionOfElement method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testVersionOfElement($element)
	{
		$id = '123';
		$version = '1';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/' . $version;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->versionOfElement($element, $id, $version),
				$this->equalTo($returnData->$element)
		);
	}

	/**
	 * Tests the versionOfElement method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testVersionOfElementFailure($element)
	{
		$id = '123';
		$version = '1';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/' . $version;

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->versionOfElement($element, $id, $version);
	}

	/**
	 * Provides test data for element type.
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedElements()
	{
		// Elements type
		return array(
				array('nodes'),
				array('ways'),
				array('relations')
		);
	}

	/**
	 * Provides test data for element type - faliures
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedElementsFailure()
	{
		// Elements type
		return array(
				array('nodes'),
				array('ways'),
				array('relations'),
				array('others')
		);
	}

	/**
	 * Tests the multiFetchElements method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElements
	 */
	public function testMultiFetchElements($element)
	{
		$params = '123,456,789';
		$single_element = substr($element, 0, strlen($element) - 1);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->$single_element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '?' . $element . "=" . $params;

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->multiFetchElements($element, $params),
				$this->equalTo($returnData->$single_element)
		);
	}

	/**
	 * Tests the multiFetchElements method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementsFailure
	 */
	public function testMultiFetchElementsFailure($element)
	{
		$params = '123,456,789';
		$single_element = substr($element, 0, strlen($element) - 1);

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->$single_element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '?' . $element . "=" . $params;

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->multiFetchElements($element, $params);
	}

	/**
	 * Tests the relationsForElement method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testRelationsForElement($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/relations';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->relationsForElement($element, $id),
				$this->equalTo($returnData->$element)
		);
	}

	/**
	 * Tests the relationsForElement method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testRelationsForElementFailure($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->$element = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/relations';

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->relationsForElement($element, $id);
	}

	/**
	 * Tests the waysForNode method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testWaysForNode()
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->way = new SimpleXMLElement($this->sampleXml);

		$path = 'node/' . $id . '/ways';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->waysForNode($id),
				$this->equalTo($returnData->way)
		);
	}

	/**
	 * Tests the waysForNode method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testWaysForNodeFailure()
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->way = new SimpleXMLElement($this->sampleXml);

		$path = 'node/' . $id . '/ways';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->waysForNode($id);
	}

	/**
	 * Provides test data for full element type.
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedFullElement()
	{
		// Full element type
		return array(
				array('way'),
				array('relation')
		);
	}

	/**
	 * Provides test data for full element type - faliures
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedFullElementFailure()
	{
		// Full element type
		return array(
				array('way'),
				array('relation'),
				array('other')
		);
	}

	/**
	 * Tests the fullElement method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedFullElement
	 */
	public function testFullElement($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;
		$returnData->node = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/full';

		$this->client->expects($this->once())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->fullElement($element, $id),
				$this->equalTo($returnData->node)
		);
	}

	/**
	 * Tests the fullElement method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedFullElementFailure
	 */
	public function testFullElementFailure($element)
	{
		$id = '123';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;
		$returnData->node = new SimpleXMLElement($this->sampleXml);

		$path = $element . '/' . $id . '/full';

		$this->client->expects($this->any())
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->fullElement($element, $id);
	}

	/**
	 * Tests the redaction method
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @dataProvider seedElement
	 */
	public function testRedaction($element)
	{
		$id = '123';
		$version = '1';
		$redaction_id = '1';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleXml;

		$path = $element . '/' . $id . '/' . $version . '/redact?redaction=' . $redaction_id;

		$this->client->expects($this->once())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->redaction($element, $id, $version, $redaction_id),
				$this->equalTo(new SimpleXMLElement($this->sampleXml))
		);
	}

	/**
	 * Tests the redaction method - failure
	 *
	 * @param   string  $element  Element type
	 * 
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 * @dataProvider seedElementFailure
	 */
	public function testRedactionFailure($element)
	{
		$id = '123';
		$version = '1';
		$redaction_id = '1';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $element . '/' . $id . '/' . $version . '/redact?redaction=' . $redaction_id;

		$this->client->expects($this->any())
		->method('put')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->redaction($element, $id, $version, $redaction_id);
	}
}
