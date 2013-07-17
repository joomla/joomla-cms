<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/operations/service/get.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3OperationsServiceGetTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3  Object under test.
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
		$this->object = new JAmazons3($this->options);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getService method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function testGetService()
	{
		$this->options->set('api.accessKeyId', 'private');
		$this->options->set('api.secretAccessKey', 'private');

		$response = new JHttpResponse;
		$response->code = 200;
		$response->body = "<ListAllMyBucketsResult xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">"
			. "<Owner><ID>6e887773574284f7e38cacbac9e1455ecce62f79929260e9b68db3b84720ed96</ID>"
			. "<DisplayName>alex.ukf</DisplayName></Owner><Buckets><Bucket><Name>jgsoc</Name>"
			. "<CreationDate>2013-06-29T10:29:36.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>";
		$expectedResult = new SimpleXMLElement($response->body);

		$this->assertThat(
			$this->object->service->get->getService(),
			$this->equalTo($expectedResult)
		);
	}
}
