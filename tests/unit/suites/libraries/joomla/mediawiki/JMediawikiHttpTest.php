<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JMediawikiHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       3.1.4
 */
class JMediawikiHttpTest extends \PHPUnit\Framework\TestCase
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
	protected $transport;

	/**
	 * @var    JMediawikiHttp  Object under test.
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
				$this->transport = $this->getMockBuilder('JHttpTransportStream')
				->setMethods(array('request'))
				->setConstructorArgs(array($this->options))
				->setMockClassName('CustomTransport')
				->disableOriginalConstructor()
				->getMock();

		$this->object = new JMediawikiHttp($this->options, $this->transport);
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
		unset($this->options, $this->transport, $this->object);
	}

	/**
	 * Tests the get method
	 *
	 * @return void
	 */
	public function testGet()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('GET', $uri)
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->get('https://example.com/gettest'),
			$this->equalTo('requestResponse')
		);
	}

	/**
	 * Tests the post method
	 *
	 * @return void
	 */
	public function testPost()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('POST', $uri, array())
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->post('https://example.com/gettest', array()),
			$this->equalTo('requestResponse')
		);
	}
}
