<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

/**
 * Test class for JFacebookOauth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * @since       3.2.0
 */
class JFacebookOauthTest extends TestCase
{
	/**
	 * @var    Registry  Options for the Facebook object.
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  3.2.0
	 */
	protected $client;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data..
	 * @since  3.2.0
	 */
	protected $input;

	/**
	* @var    JFacebookOauth  Object under test.
	* @since  3.2.0
	*/
	protected $object;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new Registry;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->input = new JInput;

		$this->object = new JFacebookOauth($this->options, $this->client, $this->input);

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->client, $this->input, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the setScope method
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function testSetScope()
	{
		$this->object->setScope('read_stream');

		$this->assertThat(
			$this->options->get('scope'),
			$this->equalTo('read_stream')
		);
	}

	/**
	 * Tests the getScope method
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function testGetScope()
	{
		$this->options->set('scope', 'read_stream');

		$this->assertThat(
			$this->object->getScope(),
			$this->equalTo('read_stream')
		);
	}
}
