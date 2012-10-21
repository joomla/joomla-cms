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
require_once JPATH_PLATFORM . '/joomla/mediawiki/pages.php';

/**
 * Test class for JMediawikiPages.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiPagesTest extends PHPUnit_Framework_TestCase
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
	 * @var    JMediawikiPages  Object under test.
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

		$this->object = new JMediawikiPages($this->options, $this->client);
	}

	/**
	 * Tests the editPage method
	 *
	 * @return void
	 */
	public function testEditPage()
	{
	}

	/**
	 * Tests the deletePageByName method
	 *
	 * @return void
	 */
	public function testDeletePageByName()
	{
	}

	/**
	 * Tests the deletePageByID method
	 *
	 * @return void
	 */
	public function testDeletePageByID()
	{
	}

	/**
	 * Tests the undeletePage method
	 *
	 * @return void
	 */
	public function testUndeletePage()
	{
	}

	/**
	 * Tests the movePageByName method
	 *
	 * @return void
	 */
	public function testMovePageByName()
	{
	}

	/**
	 * Tests the movePageByID method
	 *
	 * @return void
	 */
	public function testMovePageByID()
	{
	}

	/**
	 * Tests the rollback method
	 *
	 * @return void
	 */
	public function testRollback()
	{
	}

	/**
	 * Tests the changeProtection method
	 *
	 * @return void
	 */
	public function testChangeProtection()
	{
	}

	/**
	 * Tests the getPageInfo method
	 *
	 * @return void
	 */
	public function testGetPageInfo()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=info&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPageInfo(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getPageProperties method
	 *
	 * @return void
	 */
	public function testGetPageProperties()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=pageprops&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPageProperties(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getRevisions method
	 *
	 * @return void
	 */
	public function testGetRevisions()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=pageprops&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPageProperties(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getBackLinks method
	 *
	 * @return void
	 */
	public function testGetBackLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=backlinks&bltitle=Joomla&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getBackLinks('Joomla'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getIWBackLinks method
	 *
	 * @return void
	 */
	public function testGetIWBackLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=iwbacklinks&iwbltitle=Joomla&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getIWBackLinks('Joomla'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
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
