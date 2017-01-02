<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogleDataPicasa.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataPicasaAlbumTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the JOAuth2Client object.
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 */
	protected $http;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 */
	protected $input;

	/**
	 * @var    JOAuth2Client  The OAuth client for sending requests to Google.
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
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->http = $this->getMockBuilder('JHttp')
					->setMethods(array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'))
					->setConstructorArgs(array($this->options))
					->getMock();
		$this->input = new JInput;
		$this->oauth = new JOAuth2Client($this->options, $this->http, $this->input);
		$this->auth = new JGoogleAuthOauth2($this->options, $this->oauth);
		$this->xml = new SimpleXMLElement(file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'album.txt'));
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
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->http);
		unset($this->input);
		unset($this->auth);
		unset($this->oauth);
		unset($this->xml);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAuth()
	{
		$this->assertEquals($this->auth->authenticate(), $this->object->authenticate());
	}

	/**
	 * Tests the isauth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAuth()
	{
		$this->assertEquals($this->auth->isAuthenticated(), $this->object->isAuthenticated());
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
		$link = $this->object->getLink('nothing');
		$this->assertFalse($link);
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
		$this->assertEquals($time, 1293843600);
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
		$time = $this->object->setTime(1293843600.001)->getTime();
		$this->assertEquals($time, 1293843600.001);
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
	 * Tests the listPhotos method with wrong XML
	 *
	 * @group	JGoogle
	 * @expectedException UnexpectedValueException
	 * @return void
	 */
	public function testListPhotosException()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaBadXmlCallback'));
		$this->object->listPhotos();
	}

	/**
	 * Tests the upload method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUpload()
	{
		$this->http->expects($this->exactly(4))->method('post')->will($this->returnCallback('dataPicasaUploadCallback'));
		$result = $this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');

		$result = $this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.gif');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');

		$result = $this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.jpg');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');

		$result = $this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.bmp');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaPhoto');
	}

	/**
	 * Tests the upload method with an unknown file type
	 *
	 * @group	JGoogle
	 * @expectedException RuntimeException
	 * @return void
	 */
	public function testUploadUnknown()
	{
		$this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photo.txt');
	}

	/**
	 * Tests the upload method with an invalid file
	 *
	 * @group	JGoogle
	 * @expectedException PHPUnit_Framework_Error_Warning
	 * @return void
	 */
	public function testUploadFake()
	{
		$this->object->upload(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fakephoto.png');
	}

	/**
	 * Tests the setOption method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetOption()
	{
		$this->object->setOption('key', 'value');

		$this->assertThat(
			$this->options->get('key'),
			$this->equalTo('value')
		);
	}

	/**
	 * Tests the getOption method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetOption()
	{
		$this->options->set('key', 'value');

		$this->assertThat(
			$this->object->getOption('key'),
			$this->equalTo('value')
		);
	}

	/**
	 * Tests that all functions properly return false
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testFalse()
	{
		$this->oauth->setToken(false);

		$functions['delete'] = array();
		$functions['save'] = array();
		$functions['refresh'] = array();
		$functions['listPhotos'] = array();
		$functions['upload'] = array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png');

		foreach ($functions as $function => $params)
		{
			$this->assertFalse(call_user_func_array(array($this->object, $function), $params));
		}
	}

	/**
	 * Tests that all functions properly return Exceptions
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testExceptions()
	{
		$this->http->expects($this->atLeastOnce())->method('get')->will($this->returnCallback('picasaExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('delete')->will($this->returnCallback('picasaExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('post')->will($this->returnCallback('picasaDataExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('put')->will($this->returnCallback('picasaDataExceptionCallback'));

		$functions['delete'] = array();
		$functions['save'] = array();
		$functions['refresh'] = array();
		$functions['listPhotos'] = array();
		$functions['upload'] = array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png');

		foreach ($functions as $function => $params)
		{
			$exception = false;

			try
			{
				call_user_func_array(array($this->object, $function), $params);
			}
			catch (UnexpectedValueException $e)
			{
				$exception = true;
				$this->assertEquals($e->getMessage(), 'Unexpected data received from Google: `BADDATA`.');
			}
			$this->assertTrue($exception);
		}
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
function emptyPicasaCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
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
function picasaPhotolistCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photolist.txt');

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
 * @since   12.3
 */
function dataPicasaUploadCallback($url, $data, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photo.txt');

	return $response;
}
