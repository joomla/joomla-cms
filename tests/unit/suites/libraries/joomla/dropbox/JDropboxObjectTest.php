<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/dropbox/object.php';
require_once __DIR__ . '/stubs/JDropboxObjectMock.php';

/**
 * Test class for JDropbox.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Dropbox
 *
 * @since       ??.?
 */
class JDropboxObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Dropbox object
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JDropboxObject  Object under test.
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
		$this->options->set(
			'testParams',
			array(
				"query" => ".jpg",
				"file_limit" => "10"
			)
		);
		$this->options->set('app.key', 'i2rm20ghq55l5q8');
		$this->options->set('app.state', urlencode('asd^^*SODJDJ;2!#%$^m'));
		$this->options->def('api.url', 'api.dropbox.com');
		$this->options->set('api.oauth2.authorize', 'https://www.dropbox.com/1/oauth2/authorize');
		$this->options->def('api.oauth2.access_token', 'https://api.dropbox.com/1/oauth2/token');

		$this->object = new JDropboxObjectMock($this->options);
	}

	/**
	 * Tests the createParamsString method
	 *
	 * @return void
	 */
	public function testCreateParamsString()
	{
		$expectedResult = "?query=.jpg&file_limit=10";

		$this->assertThat(
			$this->object->createParamsString($this->options->get('testParams')),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getAuthorizationUri method
	 *
	 * @return void
	 */
	public function testGetAuthorizationUri()
	{
		$expectedResult = "https://www.dropbox.com/1/oauth2/authorize"
			. "?response_type=code&client_id=i2rm20ghq55l5q8&state="
			. $this->options->get("app.state");

		$this->assertThat(
			$this->object->getAuthorizationUri(),
			$this->equalTo($expectedResult)
		);
	}
}
