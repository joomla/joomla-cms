<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMediawiki.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiTest extends \PHPUnit\Framework\TestCase
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
	 * @var    JMediawiki Object under test.
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

		$this->object = new JMediawiki($this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->options);
		unset($this->client);
		unset($this->object);
	}

	/**
	 * Tests the magic __get method - pages
	 *
	 * @return void
	 */
	public function test__GetPages()
	{
		$this->assertThat(
			$this->object->pages,
			$this->isInstanceOf('JMediawikiPages')
		);
	}

	/**
	 * Tests the magic __get method - users
	 *
	 * @return void
	 */
	public function test__GetUsers()
	{
		$this->assertThat(
			$this->object->users,
			$this->isInstanceOf('JMediawikiUsers')
		);
	}

	/**
	 * Tests the magic __get method - links
	 *
	 * @return void
	 */
	public function test__GetLinks()
	{
		$this->assertThat(
			$this->object->links,
			$this->isInstanceOf('JMediawikiLinks')
		);
	}

	/**
	 * Tests the magic __get method - categories
	 *
	 * @return void
	 */
	public function test__GetCategories()
	{
		$this->assertThat(
			$this->object->categories,
			$this->isInstanceOf('JMediawikiCategories')
		);
	}

	/**
	 * Tests the magic __get method - images
	 *
	 * @return void
	 */
	public function test__GetImages()
	{
		$this->assertThat(
			$this->object->images,
			$this->isInstanceOf('JMediawikiImages')
		);
	}

	/**
	 * Tests the magic __get method - search
	 *
	 * @return void
	 */
	public function test__GetSearch()
	{
		$this->assertThat(
			$this->object->search,
			$this->isInstanceOf('JMediawikiSearch')
		);
	}

	/**
	 * Tests the setOption method
	 *
	 * @return void
	 */
	public function testSetOption()
	{
		$this->object->setOption('api.url', 'https://example.com/settest');

		$this->assertThat(
			$this->options->get('api.url'),
			$this->equalTo('https://example.com/settest')
		);
	}

	/**
	 * Tests the getOption method
	 *
	 * @return void
	 */
	public function testGetOption()
	{
		$this->options->set('api.url', 'https://example.com/gettest');

		$this->assertThat(
			$this->object->getOption('api.url', 'https://example.com/gettest'),
			$this->equalTo('https://example.com/gettest')
		);
	}
}
