<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/mediawiki/mediawiki.php';
require_once JPATH_PLATFORM . '/joomla/mediawiki/http.php';
require_once JPATH_PLATFORM . '/joomla/mediawiki/links.php';

/**
 * Test class for JMediawikiLinks.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiLinksTest extends PHPUnit_Framework_TestCase
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
	 * @var    JMediawikiLinks  Object under test.
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

		$this->object = new JMediawikiLinks($this->options, $this->client);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=links&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLinks(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetLinksUsed()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&generator=links&prop=info&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLinksUsed(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetIWLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=links&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getIWLinks(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetLangLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=langlinks&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLangLinks(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetExtLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=extlinks&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getExtLinks(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testEnumerateLinks()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&meta=siteinfo&alcontinue=&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->enumerateLinks(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}
}
