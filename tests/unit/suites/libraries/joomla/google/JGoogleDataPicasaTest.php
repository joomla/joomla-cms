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
class JGoogleDataPicasaTest extends TestCase
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
	 * @var    JGoogleDataPicasa  Object under test.
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

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->http = $this->getMock('JHttp', array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'), array($this->options));
		$this->input = new JInput;
		$this->oauth = new JOAuth2Client($this->options, $this->http, $this->input);
		$this->auth = new JGoogleAuthOauth2($this->options, $this->oauth);
		$this->object = new JGoogleDataPicasa($this->options, $this->auth);

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
	 * Tests the listAlbums method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListAlbums()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaAlbumlistCallback'));
		$results = $this->object->listAlbums('userID');

		$this->assertEquals(count($results), 2);
		$i = 1;

		foreach ($results as $result)
		{
			$this->assertEquals(get_class($result), 'JGoogleDataPicasaAlbum');
			$this->assertEquals($result->getTitle(), 'Album ' . $i);
			$i++;
		}
	}

	/**
	 * Tests the listAlbums method with wrong XML
	 *
	 * @group	JGoogle
	 * @expectedException UnexpectedValueException
	 * @return void
	 */
	public function testListAlbumsException()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaBadXmlCallback'));
		$this->object->listAlbums();
	}

	/**
	 * Tests the createAlbum method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateAlbum()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('dataPicasaAlbumCallback'));
		$result = $this->object->createAlbum('userID', 'New Title', 'private');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaAlbum');
		$this->assertEquals($result->getTitle(), 'New Title');
	}

	/**
	 * Tests the getAlbum method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAlbum()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('picasaAlbumCallback'));
		$result = $this->object->getAlbum('https://picasaweb.google.com/data/entry/api/user/12345678901234567890/albumid/0123456789012345678');
		$this->assertEquals(get_class($result), 'JGoogleDataPicasaAlbum');
		$this->assertEquals($result->getTitle(), 'Album 2');
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

		$functions['listAlbums'] = array('userID');
		$functions['createAlbum'] = array('userID', 'New Title', 'private');
		$functions['getAlbum'] = array('https://picasaweb.google.com/data/entry/api/user/12345678901234567890/albumid/0123456789012345678');

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
		$this->http->expects($this->atLeastOnce())->method('post')->will($this->returnCallback('picasaDataExceptionCallback'));

		$functions['listAlbums'] = array('userID');
		$functions['createAlbum'] = array('userID', 'New Title', 'private');
		$functions['getAlbum'] = array('https://picasaweb.google.com/data/entry/api/user/12345678901234567890/albumid/0123456789012345678');

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
function picasaAlbumCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'album.txt');

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
function dataPicasaAlbumCallback($url, $data, array $headers = null, $timeout = null)
{
	PHPUnit_Framework_TestCase::assertContains('<title>New Title</title>', $data);

	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
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
function picasaAlbumlistCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'albumlist.txt');

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
function picasaExceptionCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = 'BADDATA';

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
function picasaDataExceptionCallback($url, $data, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = 'BADDATA';

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
function picasaBadXmlCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/atom+xml');
	$response->body = '<feed />';

	return $response;
}
