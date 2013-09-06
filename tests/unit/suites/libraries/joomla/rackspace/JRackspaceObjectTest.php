<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/rackspace/object.php';
require_once __DIR__ . '/stubs/JRackspaceObjectMock.php';

/**
 * Test class for JRackspaceObject.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspaceObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspaceIssues  Object under test.
	 * @since  ??.?
	 */
	protected $object;

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
		parent::setUp();

		$this->options = new JRegistry;
		$this->object = new JRackspaceObjectMock($this->options);
	}

	/**
	 * Tests the processResponse method using a sample response.
	 *
	 * @return void
	 */
	public function testProcessResponse()
	{
		$response = new JHttpResponse;
		$response->code = 200;
		$response->body = '[{'
			. '"hash": "335304a396086d6745aefca4a18cefdd", '
			. '"last_modified": "2013-08-28T14:40:02.931250", '
			. '"bytes": 77874, '
			. '"name": "1010020_10201709146673325_733886835_n.jpg", '
			. '"content_type": "image/jpeg" '
			. '}]';
		$expectedResult = json_decode($response->body);

		$this->assertThat(
			$this->object->processResponse($response),
			$this->equalTo($expectedResult)
		);
	}
}
