<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1CredentialsStateAuthorised.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1CredentialsStateNewTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1CredentialsStateAuthorised
	 */
	protected $object;

	/**
	 * The db object.
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->setConstructorArgs(array($this->db))
			->setMethods(array('create', 'delete', 'randomKey', 'update'))
			->getMock();
	}

	/**
	 * Test the authorise method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testAuthorise()
	{
		$this->object->authorise(5);
	}

	/**
	 * Test the convert method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testConvert()
	{
		$this->object->convert();
	}

	/**
	 * Test the deny method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testDeny()
	{
		$this->object->deny();
	}

	/**
	 * Test the initialise method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testInitialise()
	{
		$this->object->expects($this->at(0))
		->method('randomKey')
		->with()
		->will($this->returnValue('key'));

		$this->object->expects($this->at(1))
		->method('randomKey')
		->with(true)
		->will($this->returnValue('secret'));

		$this->object->expects($this->at(2))
		->method('create');

		$initialised = $this->object->initialise('key', 'oob', 1800);

		$this->assertEquals(
			array(
				'callback_url' => 'oob',
				'verifier_key' => '',
				'key' => 'key',
				'secret' => 'secret',
				'type' => JOAuth1Credentials::TEMPORARY,
				'temporary_expiration_date' => time() + 1800,
				'credentials_id' => '',
				'client_key' => 'key',
				'resource_owner_id' => 0,
				'expiration_date' => ''
			),
			TestReflection::getValue($this->object, 'properties')
		);

		$this->assertInstanceOf('JOAuth1CredentialsStateTemporary', $initialised);
	}

	/**
	 * Test the revoke method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testRevoke()
	{
		$this->object->revoke();
	}
}
