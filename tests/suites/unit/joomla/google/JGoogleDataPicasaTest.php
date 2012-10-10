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
class JGoogleDataPicasaTest extends PHPUnit_Framework_TestCase
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
		$this->options = new JRegistry;
		$this->http = $this->getMock('JOauthHttp', array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'), array($this->options));
		$this->input = new JInput;
		$this->oauth = new JOauthV2client($this->options, $this->http, $this->input);
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
function picasaAlbumCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = JFile::read(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'album.txt');

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
function dataPicasaAlbumCallback($url, $data, array $headers = null, $timeout = null)
{
	PHPUnit_Framework_TestCase::assertContains('<title>New Title</title>', $data);

	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
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
 * @since   12.2
 */
function picasaAlbumlistCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = JFile::read(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'albumlist.txt');

	return $response;
}
