<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JMediawikiSearch.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       3.1.4
 */
class JMediawikiSearchTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var    JRegistry  Options for the Mediawiki object.
	 * @since  3.1.4
	 */
	protected $options;

	/**
	 * @var    JMediawikiHttp  Mock client object.
	 * @since  3.1.4
	 */
	protected $client;

	/**
	 * @var    JMediawikiSearch  Object under test.
	 * @since  3.1.4
	 */
	protected $object;

	/**
	 * @var    string  Sample xml string.
	 * @since  3.1.4
	 */
	protected $sampleString = '<a><b></b><c></c></a>';

	/**
	 * @var    string  Sample xml error message.
	 * @since  3.1.4
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

		$this->object = new JMediawikiSearch($this->options, $this->client);
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
		unset($this->options, $this->client, $this->object);
	}

	/**
	 * Tests the search method
	 *
	 * @return void
	 */
	public function testSearch()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=search&srsearch=test&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->search('test'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}

	/**
	 * Tests the openSearch method
	 *
	 * @return void
	 */
	public function testOpenSearch()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/api.php?action=query&list=search&search=test&format=xml')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->openSearch('test'),
			$this->equalTo(simplexml_load_string($this->sampleString))
		);
	}
}
