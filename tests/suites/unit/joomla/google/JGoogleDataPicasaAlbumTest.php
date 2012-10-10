<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/google/data/picasa.php';

/**
 * Test class for JGoogleDataPicasa.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.2
 */
class JGoogleDataPicasaAlbumTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the JOauthV2client object.
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 */
	protected $client;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 */
	protected $input;

	/**
	 * @var    JOauthV2client  The OAuth client for sending requests to Google.
	 */
	protected $oauth;

	/**
	 * @var    JGoogleAuthOauth2  The Google OAuth client for sending requests.
	 */
	protected $auth;

	/**
	 * @var    string  The XML data for the album.
	 */
	protected $xml;

	/**
	 * @var    JGoogleDataPicasaAlbum  Object under test.
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
		$this->options = new JRegistry;
		$this->http = $this->getMock('JOauthHttp', array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'), array($this->options));
		$this->input = new JInput;
		$this->oauth = new JOauthV2client($this->options, $this->http, $this->input);
		$this->auth = new JGoogleAuthOauth2($this->options, $this->oauth);
		$this->xml = new SimpleXMLElement(JFile::read(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'album.txt'));
		$this->object = new JGoogleDataPicasaAlbum($this->xml, $this->options, $this->auth);

		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('clientsecret', 'jeDs8rKw_jDJW8MMf-ff8ejs');
		$this->object->setOption('redirecturi', 'http://localhost/oauth');

		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time() - 1800;
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAuth()
	{
		$this->assertEquals($this->auth->auth(), $this->object->auth());
	}

	/**
	 * Tests the isauth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAuth()
	{
		$this->assertEquals($this->auth->isAuth(), $this->object->authenticated());
	}

	/**
	 * Tests the delete method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testDelete()
	{
		$this->http->expects($this->once())->method('delete')->will($this->returnCallback('emptyPicasaCallback'));
		$result = $this->object->delete();
		$this->assertTrue($result);
	}

	/**
	 * Tests the getLink method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetLink()
	{
		$link = $this->object->getLink();
		$this->assertEquals($link, 'https://picasaweb.google.com/data/entry/api/user/12345678901234567890/albumid/0123456789012345678');
		$link = $this->object->getLink('self');
		$this->assertEquals($link, 'https://picasaweb.google.com/data/entry/api/user/12345678901234567890/albumid/0123456789012345678');
	}

	/**
	 * Tests the getTitle method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetTitle()
	{
		$title = $this->object->getTitle();
		$this->assertEquals($title, 'Album 2');
	}

	/**
	 * Tests the getSummary method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetSummary()
	{
		$summary = $this->object->getSummary();
		$this->assertEquals($summary, 'Summary');
	}

	/**
	 * Tests the getLocation method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetLocation()
	{
		$location = $this->object->getLocation();
		$this->assertEquals($location, 'California');
	}

	/**
	 * Tests the getAccess method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAccess()
	{
		$access = $this->object->getAccess();
		$this->assertEquals($access, 'protected');
	}

	/**
	 * Tests the getTime method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetTime()
	{
		$time = $this->object->getTime();
		$this->assertEquals($time, 1293843600000);
	}

	/**
	 * Tests the setTitle method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetTitle()
	{
		$title = $this->object->setTitle('New Title')->getTitle();
		$this->assertEquals($title, 'New Title');
	}

	/**
	 * Tests the setSummary method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetSummary()
	{
		$summary = $this->object->setSummary('New Summary')->getSummary();
		$this->assertEquals($summary, 'New Summary');
	}

	/**
	 * Tests the setLocation method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetLocation()
	{
		$location = $this->object->setLocation('San Francisco')->getLocation();
		$this->assertEquals($location, 'San Francisco');
	}

	/**
	 * Tests the setAccess method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetAccess()
	{
		$access = $this->object->setAccess('public')->getAccess();
		$this->assertEquals($access, 'public');
	}

	/**
	 * Tests the setTime method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetTime()
	{
		$time = $this->object->setTime(1293843600001)->getTime();
		$this->assertEquals($time, 1293843600001);
	}

	/**
	 * Tests the save method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSave()
	{
		$this->http->expects($this->exactly(2))->method('put')->will($this->returnCallback('dataPicasaAlbumCallback'));
		$this->object->setTitle('New Title');
		$this->object->save();
		$this->object->save(true);
	}

	/**
	 * Tests the refresh method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testRefresh()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaAlbumCallback'));
		$result = $this->object->refresh();
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaAlbum');
	}

	/**
	 * Tests the listPhotos method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListPhotos()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaPhotolistCallback'));
		$results = $this->object->listPhotos();

		$this->assertEquals(count($results), 2);
		$i = 1;
		foreach ($results as $result)
		{
			$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');
			$this->assertEquals($result->getTitle(), 'Photo' . $i . '.jpg');
			$i++;
		}
	}

	/**
	 * Tests the listPhotos method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUpload()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('dataPicasaUploadCallback'));
		$result = $this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png');

		$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');
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
 * @since   12.2
 */
function emptyPicasaCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
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
 * @since   12.2
 */
function picasaPhotolistCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = JFile::read(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photolist.txt');

	return $response;
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.2
 */
function dataPicasaUploadCallback($url, $data, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = JFile::read(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photo.txt');

	return $response;
}
