<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogle.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleEmbedMapsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the JOAuth2Client object.
	 */
	protected $options;

	/**
	 * @var    JUri  URI of the page being rendered.
	 */
	protected $uri;

	/**
	 * @var    JHttp  Mock client object.
	 */
	protected $http;

	/**
	 * @var    JGoogle  Object under test.
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;

		$this->http = $this->getMockBuilder('JHttp')
					->setMethods(array('get'))
					->setConstructorArgs(array($this->options))
					->getMock();
		$this->uri = new JUri;
		$this->object = new JGoogleEmbedMaps($this->options, $this->uri, $this->http);
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
		unset($this->options);
		unset($this->uri);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getKey method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetKey()
	{
		$this->object->setOption('key', 'abcdefghijklmnopqrstuvwxyz');
		$key = $this->object->getKey();
		$this->assertEquals($key, 'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * Tests the setKey method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetKey()
	{
		$this->object->setKey('abcdefghijklmnopqrstuvwxyz');
		$key = $this->object->getOption('key');
		$this->assertEquals($key, 'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * Tests the getMapID method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetMapId()
	{
		$id = $this->object->getMapId();
		$this->assertEquals($id, 'map_canvas');

		$this->object->setOption('mapid', 'canvas');
		$id = $this->object->getMapId();
		$this->assertEquals($id, 'canvas');
	}

	/**
	 * Tests the setMapID method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetMapId()
	{
		$this->object->setMapId('map_canvas');
		$id = $this->object->getOption('mapid');
		$this->assertEquals($id, 'map_canvas');
	}

	/**
	 * Tests the getMapClass method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetMapClass()
	{
		$class = $this->object->getMapClass();
		$this->assertEquals($class, '');

		$this->object->setOption('mapclass', 'map_class');
		$class = $this->object->getMapClass();
		$this->assertEquals($class, 'map_class');
	}

	/**
	 * Tests the setMapClass method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetMapClass()
	{
		$this->object->setMapClass('map_class');
		$class = $this->object->getOption('mapclass');
		$this->assertEquals($class, 'map_class');
	}

	/**
	 * Tests the getMapStyle method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetMapStyle()
	{
		$style = $this->object->getMapStyle();
		$this->assertEquals($style, '');

		$this->object->setOption('mapstyle', 'map_style');
		$style = $this->object->getMapStyle();
		$this->assertEquals($style, 'map_style');
	}

	/**
	 * Tests the setMapStyle method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetMapStyle()
	{
		$this->object->setMapStyle('map_style');
		$style = $this->object->getOption('mapstyle');
		$this->assertEquals($style, 'map_style');
	}

	/**
	 * Tests the getMapType method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetMapType()
	{
		$type = $this->object->getMapType();
		$this->assertEquals($type, 'ROADMAP');

		$this->object->setOption('maptype', 'SATELLITE');
		$type = $this->object->getMapType();
		$this->assertEquals($type, 'SATELLITE');
	}

	/**
	 * Tests the setMapType method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetMapType()
	{
		$this->object->setMapType('HYBRID');
		$type = $this->object->getOption('maptype');
		$this->assertEquals($type, 'HYBRID');
	}

	/**
	 * Tests the getAdditionalMapOptions method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAdditionalMapOptions()
	{
		$options = $this->object->getAdditionalMapOptions();
		$this->assertEquals($options, array());

		$this->object->setOption('mapoptions', array('key' => 'value'));
		$options = $this->object->getAdditionalMapOptions();
		$this->assertEquals($options, array('key' => 'value'));
	}

	/**
	 * Tests the setAdditionalMapOptions method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetAdditionalMapOptions()
	{
		$this->object->setAdditionalMapOptions(array('key' => 'value'));
		$options = $this->object->getOption('mapoptions');
		$this->assertEquals($options, array('key' => 'value'));
	}

	/**
	 * Tests the getAdditionalJavascript method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAdditionalJavascript()
	{
		$script = $this->object->getAdditionalJavascript();
		$this->assertEquals($script, '');

		$this->object->setOption('extrascript', 'alert();');
		$script = $this->object->getAdditionalJavascript();
		$this->assertEquals($script, 'alert();');
	}

	/**
	 * Tests the setAdditionalJavascript method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetAdditionalJavascript()
	{
		$this->object->setAdditionalJavascript('alert();');
		$script = $this->object->getOption('extrascript');
		$this->assertEquals($script, 'alert();');
	}

	/**
	 * Tests the getZoom method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetZoom()
	{
		$zoom = $this->object->getZoom();
		$this->assertEquals($zoom, 0);

		$this->object->setOption('zoom', 5);
		$zoom = $this->object->getZoom();
		$this->assertEquals($zoom, 5);
	}

	/**
	 * Tests the setZoom method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetZoom()
	{
		$this->object->setZoom(5);
		$zoom = $this->object->getOption('zoom');
		$this->assertEquals($zoom, 5);
	}

	/**
	 * Tests the getCenter method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetCenter()
	{
		$center = $this->object->getCenter();
		$this->assertEquals($center, array(0, 0));

		$this->object->setOption('mapcenter', array(85.2, 45.6));
		$center = $this->object->getCenter();
		$this->assertEquals($center, array(85.2, 45.6));
	}

	/**
	 * Tests the setCenter method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetCenter()
	{
		$this->http->expects($this->exactly(5))->method('get')->will($this->returnCallback('mapsGeocodeCallback'));

		$reference[] = array('loc' => array(37, -122), 'title' => '37, -122', 'options' => array(), 'events' => array());
		$this->object->setCenter(array(37, -122));
		$center = $this->object->getOption('mapcenter');
		$this->assertEquals($center, array(37, -122));

		$reference[] = array('loc' => array(37.44188340, -122.14301950), 'title' => 'Palo Alto', 'options' => array(), 'events' => array());
		$this->object->setCenter('Palo Alto');
		$center = $this->object->getOption('mapcenter');
		$this->assertEquals($center, array(37.44188340, -122.14301950));

		$this->object->setCenter('San Francisco', false);
		$center = $this->object->getOption('mapcenter');
		$this->assertEquals($center, array(37.77492950, -122.41941550));

		$reference[] = array('loc' => array(37.44188340, -122.14301950), 'title' => 'somewhere', 'options' => array('key' => 'value'), 'events' => array());
		$this->object->setCenter('Palo Alto', 'somewhere', array('key' => 'value'));
		$center = $this->object->getOption('mapcenter');
		$this->assertEquals($center, array(37.44188340, -122.14301950));

		$obj = $this->object->setCenter('Nowhere');
		$center = $this->object->getOption('mapcenter');
		$this->assertFalse($obj);
		$this->assertEquals($center, array(37.44188340, -122.14301950));

		$obj = $this->object->setCenter('Nowhere', false);
		$center = $this->object->getOption('mapcenter');
		$this->assertFalse($obj);
		$this->assertEquals($center, array(37.44188340, -122.14301950));

		$markers = $this->object->listMarkers();
		$this->assertEquals($reference, $markers);
	}

	/**
	 * Tests the addMarker method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAddMarker()
	{
		$this->http->expects($this->exactly(4))->method('get')->will($this->returnCallback('mapsGeocodeCallback'));

		$marker = $this->object->addMarker(array(37, -122));
		$this->assertEquals($marker, array('loc' => array(37, -122), 'title' => '37, -122', 'options' => array(), 'events' => array()));

		$marker = $this->object->addMarker('Palo Alto');
		$this->assertEquals($marker, array('loc' => array(37.44188340, -122.14301950), 'title' => 'Palo Alto', 'options' => array(), 'events' => array()));

		$marker = $this->object->addMarker('Palo Alto', 'somewhere', array('key' => 'value'), array());
		$this->assertEquals($marker, array('loc' => array(37.44188340, -122.14301950), 'title' => 'somewhere', 'options' => array('key' => 'value'), 'events' => array()));

		$marker = $this->object->addMarker('Palo Alto', 'somewhere', array('key' => 'value'), array('click' => 'function(e) { map.setCenter(this.getPosition()); }'));
		$this->assertEquals($marker, array('loc' => array(37.44188340, -122.14301950), 'title' => 'somewhere', 'options' => array('key' => 'value'), 'events' => array('click' => 'function(e) { map.setCenter(this.getPosition()); }')));

		$marker = $this->object->addMarker('Nowhere');
		$this->assertFalse($marker);
	}

	/**
	 * Tests the listMarkers method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListMarkers()
	{
		$markers = $this->object->listMarkers();
		$this->assertEquals($markers, array());

		$this->object->setOption('markers', array(array('loc' => array(37, -122), 'title' => 'Somewhere', 'options' => array())));
		$markers = $this->object->listMarkers();
		$this->assertEquals($markers, array(array('loc' => array(37, -122), 'title' => 'Somewhere', 'options' => array())));
	}

	/**
	 * Tests the deleteMarkers method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testDeleteMarkers()
	{
		$this->http->expects($this->exactly(3))->method('get')->will($this->returnCallback('mapsGeocodeCallback'));

		$marker0 = $this->object->addMarker(array(37, -122));
		$marker1 = $this->object->addMarker('Palo Alto');
		$marker2 = $this->object->addMarker('Palo Alto', 'somewhere', array('key' => 'value'));

		$marker = $this->object->deleteMarker();
		$this->assertEquals($marker, $marker2);

		$marker3 = $this->object->addMarker('San Francisco');

		$marker = $this->object->deleteMarker(1);
		$this->assertEquals($marker, $marker1);
		$marker = $this->object->deleteMarker(0);
		$this->assertEquals($marker, $marker0);
		$marker = $this->object->deleteMarker();
		$this->assertEquals($marker, $marker3);
	}

	/**
	 * Tests the deleteMarkers method with an out of bounds index
	 *
	 * @group	JGoogle
	 * @expectedException OutOfBoundsException
	 * @return void
	 */
	public function testDeleteMarkersException()
	{
		$this->object->deleteMarker();
	}

	/**
	 * Tests the isAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAsync()
	{
		$async = $this->object->isAsync();
		$this->assertTrue($async);

		$this->object->setOption('async', false);
		$async = $this->object->isAsync();
		$this->assertFalse($async);

		$this->object->setOption('async', true);
		$async = $this->object->isAsync();
		$this->assertTrue($async);
	}

	/**
	 * Tests the useAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUseAsync()
	{
		$this->object->useAsync();
		$async = $this->object->getOption('async');
		$this->assertTrue($async);
	}

	/**
	 * Tests the useAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUseSync()
	{
		$this->object->useSync();
		$async = $this->object->getOption('async');
		$this->assertFalse($async);
	}

	/**
	 * Tests the getAsyncCallback method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAsyncCallback()
	{
		$callback = $this->object->getAsyncCallback();
		$this->assertEquals($callback, 'initialize');

		$this->object->setOption('callback', 'start');
		$callback = $this->object->getAsyncCallback();
		$this->assertEquals($callback, 'start');
	}

	/**
	 * Tests the setAsyncCallback method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetAsyncCallback()
	{
		$this->object->setAsyncCallback('start');
		$callback = $this->object->getOption('callback');
		$this->assertEquals($callback, 'start');
	}

	/**
	 * Tests the hasSensor method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testHasSensor()
	{
		$sensor = $this->object->hasSensor();
		$this->assertFalse($sensor);

		$this->object->setOption('sensor', true);
		$sensor = $this->object->hasSensor();
		$this->assertTrue($sensor);

		$this->object->setOption('sensor', false);
		$sensor = $this->object->hasSensor();
		$this->assertFalse($sensor);
	}

	/**
	 * Tests the useSensor method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUseSensor()
	{
		$this->object->useSensor();
		$sensor = $this->object->getOption('sensor');
		$this->assertTrue($sensor);
	}

	/**
	 * Tests the noSensor method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testNoSensor()
	{
		$this->object->useSensor();
		$this->object->noSensor();
		$sensor = $this->object->getOption('sensor');
		$this->assertFalse($sensor);
	}

	/**
	 * Tests the getAutoload method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAutoload()
	{
		$load = $this->object->getAutoload();
		$this->assertEquals($load, 'false');

		$this->object->setOption('autoload', 'mootools');
		$load = $this->object->getAutoload();
		$this->assertEquals($load, 'mootools');
	}

	/**
	 * Tests the setAutoload method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetAutoload()
	{
		$this->object->setAutoload('jquery');
		$load = $this->object->getOption('autoload');
		$this->assertEquals($load, 'jquery');
	}

	/**
	 * Tests the getHeader method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetHeader()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('mapsGeocodeCallback'));

		$this->object->useAsync();
		$this->object->setAsyncCallback('asynchronouscallback');
		$this->object->setZoom(8);
		$this->object->setCenter('San Francisco', 'Home', array('centerkey' => 'value'));
		$this->object->setMaptype('SATELLITE');
		$this->object->setMapId('MAPID');
		$this->object->setKey('123456');
		$this->object->useSensor();
		$this->object->setAdditionalMapOptions(array('mapkey1' => 5, 'mapkey2' => array ('subkey' => 'subvalue')));
		$this->object->addMarker(array(25, 75));
		$this->object->setAdditionalJavascript('alert();');
		$this->object->setAutoload('onload');

		$header = $this->object->getHeader();

		// Variables
		$this->assertContains('zoom: 8', $header);
		$this->assertContains('center: new google.maps.LatLng(37.7749295,-122.4194155)', $header);
		$this->assertContains('mapTypeId: google.maps.MapTypeId.SATELLITE', $header);
		$this->assertContains('alert();', $header);
		$this->assertContains('center: new google.maps.LatLng(37.7749295,-122.4194155)', $header);

		// Markers
		$this->assertContains('position: new google.maps.LatLng(37.7749295,-122.4194155),', $header);
		$this->assertContains('position: new google.maps.LatLng(25,75),', $header);
		$this->assertContains("title:'Home'", $header);
		$this->assertContains("title:'25, 75'", $header);
		$this->assertContains('"centerkey":"value"', $header);

		// Loading
		$this->assertContains("function asynchronouscallback() {", $header);
		$this->assertContains("script.src = 'http://maps.googleapis.com/maps/api/js?key=123456&sensor=true&callback=asynchronouscallback", $header);
		$this->assertContains('window.onload=', $header);

		$this->object->setAutoload('jquery');
		$header = $this->object->getHeader();
		$this->assertContains('jQuery(document).ready(', $header);

		$this->object->setAutoload('mootools');
		$header = $this->object->getHeader();
		$this->assertContains("window.addEvent('domready',", $header);

		$this->object->noSensor();
		$this->object->useSync();
		$header = $this->object->getHeader();
		$this->assertContains("<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=123456&sensor=false'>", $header);
	}

	/**
	 * Tests the getBody method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetBody()
	{
		$this->object->setMapId('MAPID');
		$this->object->setMapClass('class1 class2');
		$this->object->setMapStyle('width: 100%');

		$body = $this->object->getBody();

		$this->assertContains("<div id='MAPID' class='class1 class2' style='width: 100%'></div>", $body);
	}

	/**
	 * Tests the echoHeader method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEchoHeader()
	{
		$this->object->setKey('123456');

		$header = $this->object->getHeader();
		$this->expectOutputString($header);
		$this->object->echoHeader();
	}

	/**
	 * Tests the echoBody method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEchoBody()
	{
		$body = $this->object->getBody();
		$this->expectOutputString($body);
		$this->object->echoBody();
	}

	/**
	 * Tests the geocodeAddress method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGeocodeAddress()
	{
		$this->http->expects($this->exactly(2))->method('get')->will($this->returnCallback('mapsGeocodeCallback'));

		$geocode = $this->object->geocodeAddress('Palo Alto');
		$this->assertEquals($geocode['geometry']['location'], array('lat' => 37.44188340, 'lng' => -122.14301950));

		$geocode = $this->object->geocodeAddress('Wonderland');
		$this->assertNull($geocode);
	}

	/**
	 * Tests the geocodeAddress method with 400 error
	 *
	 * @group	JGoogle
	 * @expectedException RuntimeException
	 * @return void
	 */
	public function testGeocodeAddress400()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('mapsGeocode400Callback'));
		$this->object->geocodeAddress('Palo Alto');
	}

	/**
	 * Tests the geocodeAddress method with bad json
	 *
	 * @group	JGoogle
	 * @expectedException RuntimeException
	 * @return void
	 */
	public function testGeocodeAddressBadJson()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('mapsGeocodeBadJsonCallback'));
		$this->object->geocodeAddress('Palo Alto');
	}
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function mapsGeocodeCallback($url, array $headers = null, $timeout = null)
{
	$query = parse_url($url, PHP_URL_QUERY);
	
	parse_str($query, $params);
	
	$address = strtolower($params['address']);

	switch ($address)
	{
		case 'san francisco':
		$data = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'sanfrancisco.txt');
		break;

		case 'palo alto':
		$data = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'paloalto.txt');
		break;

		default:
		$data = "{\n   \"results\" : [],\n   \"status\" : \"ZERO_RESULTS\"\n}\n";
	}

	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = $data;

	return $response;
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function mapsGeocode400Callback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 400;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = '';

	return $response;
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function mapsGeocodeBadJsonCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = 'BADDATA';

	return $response;
}
