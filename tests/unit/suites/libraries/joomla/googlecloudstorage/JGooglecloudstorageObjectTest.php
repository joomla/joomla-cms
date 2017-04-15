<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/googlecloudstorage/object.php';
require_once __DIR__ . '/stubs/JGooglecloudstorageObjectMock.php';

/**
 * Test class for JGooglecloudstorage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Googlecloudstorage
 *
 * @since       ??.?
 */
class JGooglecloudstorageObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorageObject  Object under test.
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
		$this->options->set(
			'testAcl',
				array(
				"Owner" => "00b4903a97138b52f86bbff6ae0f21489cf1428e79641bd6e18c9684f034bf13",
				"Entries" => array(
					array(
						"Permission" => "FULL_CONTROL",
						"Scope" => array(
							"type" => "GroupById",
							"ID" => "00b4903a976ccfcd626423a59ea76477f98e19bfdbaf9ecd9da5dc091ea39eff",
						)
					),
					array(
						"Permission" => "FULL_CONTROL",
						"Scope" => array(
							"type" => "UserByEmail",
							"EmailAddress" => "alex.ukf@gmail.com",
							"Name" => "Alex Marin",
						),
					),
					array(
						"Permission" => "FULL_CONTROL",
						"Scope" => array(
							"type" => "GroupById",
							"ID" => "00b4903a971c9d0699ba584e218b6419b0327c60567599c5a3c12d845a371de9",
						),
					),
				)
			)
		);

		$this->object = new JGooglecloudstorageObjectMock($this->options);
	}

	/**
	 * Tests the createAclXml method
	 *
	 * @return void
	 */
	public function testCreateAclXml()
	{
		$expectedResult = '<AccessControlList>
<Owner>
<ID>00b4903a97138b52f86bbff6ae0f21489cf1428e79641bd6e18c9684f034bf13</ID>
</Owner>
<Entries>
<Entry>
<Permission>FULL_CONTROL</Permission>
<Scope type="GroupById">
<ID>00b4903a976ccfcd626423a59ea76477f98e19bfdbaf9ecd9da5dc091ea39eff</ID>
</Scope>
</Entry>
<Entry>
<Permission>FULL_CONTROL</Permission>
<Scope type="UserByEmail">
<EmailAddress>alex.ukf@gmail.com</EmailAddress>
<Name>Alex Marin</Name>
</Scope>
</Entry>
<Entry>
<Permission>FULL_CONTROL</Permission>
<Scope type="GroupById">
<ID>00b4903a971c9d0699ba584e218b6419b0327c60567599c5a3c12d845a371de9</ID>
</Scope>
</Entry>
</Entries>
</AccessControlList>';

		$this->assertThat(
			$this->object->createAclXml($this->options->get("testAcl")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the urlSafeB64Encode method using a sample response
	 *
	 * @return void
	 */
	public function testUrlSafeB64Encode()
	{
		$input = '{"alg":"RS256","typ":"JWT"}';
		$expectedResult = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9";

		$this->assertThat(
			$this->object->urlSafeB64Encode($input),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getJwtHeader method using a sample response
	 *
	 * @return void
	 */
	public function testGetJwtHeader()
	{
		$expectedResult = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9";

		$this->assertThat(
			$this->object->getJwtHeader(),
			$this->equalTo($expectedResult)
		);
	}
}
