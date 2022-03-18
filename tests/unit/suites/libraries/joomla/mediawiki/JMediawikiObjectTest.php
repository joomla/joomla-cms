<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/stubs/JMediawikiObjectMock.php';

/**
 * Test class for JMediawikiCategories.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       3.1.4
 */
class JMediawikiObjectTest extends \PHPUnit\Framework\TestCase
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
	 * @var    JMediawikiCategories  Object under test.
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

		$this->object = new JMediawikiObjectMock($this->options, $this->client);
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
	 * Tests the buildParameter method
	 *
	 * @return void
	 */
	public function testBuildParameter()
	{
		$this->assertThat(
			$this->object->buildParameter(array('Joomla', 'Joomla', 'Joomla')),
			$this->equalTo('Joomla|Joomla|Joomla')
		);
	}
}
