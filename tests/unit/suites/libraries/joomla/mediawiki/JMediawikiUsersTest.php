<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/mediawiki/mediawiki.php';
require_once JPATH_PLATFORM . '/joomla/mediawiki/http.php';
require_once JPATH_PLATFORM . '/joomla/mediawiki/users.php';

/**
 * Test class for JMediawikiUsers.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiUsersTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Mediawiki object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JMediawikiHttp  Mock client object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JMediawikiUsers  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    string  Sample xml string.
	 * @since  12.3
	 */
	protected $sampleString = '<a><b></b><c></c></a>';

	/**
	 * @var    string  Sample xml error message.
	 * @since  12.3
	 */
	protected $errorString = '<message>Generic Error</message>';

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
		$this->options = new JRegistry;
		$this->client = $this->getMock('JMediawikiHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JMediawikiUsers($this->options, $this->client);
	}

	/**
	 * Tests the login method
	 *
	 * @return void
	 */
	public function testLogin()
	{
	}

	/**
	 * Tests the logout method
	 *
	 * @return void
	 */
	public function testLogout()
	{
	}

	/**
	 * Tests the getUserInfo method
	 *
	 * @return void
	 */
	public function testGetUserInfo()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=users&ususers=Joomla&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserInfo(array('Joomla')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCurrentUserInfo method
	 *
	 * @return void
	 */
	public function testGetCurrentUserInfo()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&meta=userinfo&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCurrentUserInfo(),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getUserContribs method
	 *
	 * @return void
	 */
	public function testGetUserContribs()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=usercontribs&ucuser=Joomla&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserContribs('Joomla'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the blockUser method
	 *
	 * @return void
	 */
	public function testBlockUser()
	{
	}

	/**
	 * Tests the unBlockUserByName method
	 *
	 * @return void
	 */
	public function testUnBlockUserByName()
	{
	}

	/**
	 * Tests the unBlockUserByID method
	 *
	 * @return void
	 */
	public function testUnBlockUserByID()
	{
	}

	/**
	 * Tests the assignGroup method
	 *
	 * @return void
	 */
	public function testAssignGroup()
	{
	}

	/**
	 * Tests the emailUser method
	 *
	 * @return void
	 */
	public function testEmailUser()
	{
	}

	/**
	 * Tests the getToken method
	 *
	 * @return void
	 */
	public function testGetToken()
	{
	}
}
