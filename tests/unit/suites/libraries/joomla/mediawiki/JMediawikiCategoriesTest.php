<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMediawikiCategories.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiCategoriesTest extends PHPUnit_Framework_TestCase
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
	 * @var    JMediawikiCategories  Object under test.
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
		$this->client = $this->getMockBuilder('JMediawikiHttp')->setMethods(array('get', 'post', 'delete', 'patch', 'put'))->getMock();

		$this->object = new JMediawikiCategories($this->options, $this->client);
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
		unset($this->options, $this->client, $this->object);
	}

	/**
	 * Tests the getCategories method
	 *
	 * @return void
	 */
	public function testGetCategories()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=categories&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCategories(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategoriesUsed method
	 *
	 * @return void
	 */
	public function testGetCategoriesUsed()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&generator=categories&prop=info&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCategoriesUsed(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategoriesInfo method
	 *
	 * @return void
	 */
	public function testGetCategoriesInfo()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&prop=categoryinfo&titles=Main Page&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCategoriesInfo(array('Main Page')),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getCategoryMembers method
	 *
	 * @return void
	 */
	public function testGetCategoryMembers()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=categorymembers&cmtitle=Category:Help&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCategoryMembers('Category:Help'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the enumerateCategories method
	 *
	 * @return void
	 */
	public function testEnumerateCategories()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=allcategories&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->enumerateCategories(),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the getChangeTags method
	 *
	 * @return void
	 */
	public function testGetChangeTags()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=tags&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getChangeTags(),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}
}
