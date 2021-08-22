<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JGithub.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       1.7.0
 */
class JGithubHttpTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  2.5.0
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  2.5.0
	 */
	protected $transport;

	/**
	 * @var    JGithubHttp  Object under test.
	 * @since  2.5.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->transport = $this->getMockBuilder('JHttpTransportStream')
						->setMethods(array('request'))
						->setConstructorArgs(array($this->options))
						->setMockClassName('CustomTransport')
						->disableOriginalConstructor()
						->getMock();

		$this->object = new JGithubHttp($this->options, $this->transport);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	protected function tearDown()
	{
		unset($this->options, $this->transport, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the __construct method
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function test__Construct()
	{
		// Verify the options are set in the object
		$this->assertThat(
			$this->object->getOption('userAgent'),
			$this->equalTo('JGitHub/2.0')
		);

		$this->assertThat(
			$this->object->getOption('timeout'),
			$this->equalTo(120)
		);
	}
}
