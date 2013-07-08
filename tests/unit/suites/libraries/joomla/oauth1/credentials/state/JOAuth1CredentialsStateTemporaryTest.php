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
class JOAuth1CredentialsStateTemporaryTest extends TestCase
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

		$this->object = $this->getMockBuilder('JOAuth1CredentialsStateTemporary')
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
	 */
	public function testAuthorise()
	{
		$this->object->expects($this->once())
			->method('randomKey')
			->with()
			->will($this->returnValue('verifier_key'));

		$this->object->expects($this->once())
			->method('update');

		$authorised = $this->object->authorise(5, 500);

		$properties = TestReflection::getValue($this->object, 'properties');

		/**
		 * Due to time spent on code execution and authorization, actual expired date returned might not be just
		 * 500 seconds away from now. Here we asume that actual expired date is still good if it is about 500
		 * seconds to 502 seconds away from now.
		 */
		$expiration_date = $properties['expiration_date'];
		$this->assertLessThanOrEqual(time() + 500, $expiration_date);
		$this->assertGreaterThanOrEqual(time() + 500 - 2, $expiration_date);
		unset($properties['expiration_date']);

		$this->assertEquals(
			array(
				'callback_url' => '',
				'verifier_key' => 'verifier_key',
				'key' => '',
				'secret' => '',
				'type' => JOAuth1Credentials::AUTHORISED,
				'temporary_expiration_date' => '',
				'credentials_id' => '',
				'client_key' => '',
				'resource_owner_id' => 5,
			),
			$properties
		);

		$this->assertInstanceOf('JOAuth1CredentialsStateAuthorised', $authorised);
	}

	/**
	 * Test the authorise method - no expiry date.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAuthoriseNoExpiry()
	{
		$this->object->expects($this->once())
			->method('randomKey')
			->with()
			->will($this->returnValue('key'));

		$this->object->expects($this->once())
			->method('update');

		$authorised = $this->object->authorise(5);

		$this->assertEquals(
			array(
				'callback_url' => '',
				'verifier_key' => 'key',
				'key' => '',
				'secret' => '',
				'type' => JOAuth1Credentials::AUTHORISED,
				'temporary_expiration_date' => '',
				'credentials_id' => '',
				'client_key' => '',
				'resource_owner_id' => 5,
				'expiration_date' => 0
			),
			TestReflection::getValue($this->object, 'properties')
		);

		$this->assertInstanceOf('JOAuth1CredentialsStateAuthorised', $authorised);
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
	 */
	public function testDeny()
	{
		$this->object->expects($this->once())
			->method('delete');

		$denied = $this->object->deny();

		$this->assertInstanceOf('JOAuth1CredentialsStateDenied', $denied);
	}

	/**
	 * Test the initialise method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testInitialise()
	{
		$this->object->initialise('clientKey', 'oob', 1800);
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
