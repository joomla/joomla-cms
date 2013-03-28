<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstaller.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerTest extends TestCase
{
	/**
	 * @var  JInstaller
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
		$newDbo = $this->getMockDatabase();
		JFactory::$database = &$newDbo;

		$this->object = JInstaller::getInstance();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::getInstance
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$this->assertThat(
			$this->object = JInstaller::getInstance(),
			$this->isInstanceOf('JInstaller'),
			'JInstaller::getInstance failed'
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::setOverwrite
	 * @covers  JInstaller::isOverwrite
	 *
	 * @return void
	 */
	public function testIsAndSetOverwrite()
	{
		$this->object->setOverwrite(false);

		$this->assertThat(
			$this->object->isOverwrite(),
			$this->equalTo(false),
			'Get or Set overwrite failed'
		);

		$this->assertThat(
			$this->object->setOverwrite(true),
			$this->equalTo(false),
			'setOverwrite did not return the old value properly.'
		);

		$this->assertThat(
			$this->object->isOverwrite(),
			$this->equalTo(true),
			'getOverwrite did not return the expected value.'
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::setRedirectUrl
	 * @covers  JInstaller::getRedirectUrl
	 *
	 * @return void
	 */
	public function testGetAndSetRedirectUrl()
	{
		$this->object->setRedirectUrl('http://www.example.com');

		$this->assertThat(
			$this->object->getRedirectUrl(),
			$this->equalTo('http://www.example.com'),
			'Get or Set Redirect Url failed'
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::setUpgrade
	 * @covers  JInstaller::isUpgrade
	 *
	 * @return void
	 */
	public function testIsAndSetUpgrade()
	{
		$this->object->setUpgrade(false);

		$this->assertThat(
			$this->object->isUpgrade(),
			$this->equalTo(false),
			'Get or Set Upgrade failed'
		);

		$this->assertThat(
			$this->object->setUpgrade(true),
			$this->equalTo(false),
			'setUpgrade did not return the old value properly.'
		);

		$this->assertThat(
			$this->object->isUpgrade(),
			$this->equalTo(true),
			'getUpgrade did not return the expected value.'
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::getPath
	 *
	 * @return void
	 */
	public function testGetPath()
	{
		$this->assertThat(
			$this->object->getPath('path1_getpath', 'default_value'),
			$this->equalTo('default_value'),
			'getPath did not return the default value for an undefined path'
		);

		$this->object->setPath('path2_getpath', JPATH_BASE . '/required_path');

		$this->assertThat(
			$this->object->getPath('path2_getpath', 'default_value'),
			$this->equalTo(JPATH_BASE . '/required_path'),
			'getPath did not return the previously set value for the path'
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::abort
	 *
	 * @return void
	 */
	public function testAbortQuery()
	{
		$this->object->pushStep(array('type' => 'query'));

		$this->assertThat(
			$this->object->abort(),
			$this->isFalse()
		);
	}

	/**
	 * Test...
	 *
	 * @covers  JInstaller::abort
	 *
	 * @return void
	 */
	public function testAbortDefault()
	{
		$adapterMock = $this->getMock('test', array('_rollback_testtype'));

		$adapterMock->expects($this->once())
			->method('_rollback_testtype')
			->with($this->equalTo(array('type' => 'testtype')))
			->will($this->returnValue(true));

		$this->object->setAdapter('testadapter', $adapterMock);

		$this->object->pushStep(array('type' => 'testtype'));

		$this->assertThat(
			$this->object->abort(null, 'testadapter'),
			$this->isTrue()
		);
	}

	/**
	 * Test that if the type is not good we fall back properly
	 *
	 * @covers  JInstaller::abort
	 *
	 * @return void
	 */
	public function testAbortBadType()
	{
		$this->object->pushStep(array('type' => 'badstep'));

		$this->assertThat(
			$this->object->abort(null, false),
			$this->isFalse()
		);
	}

	/**
	 * This test is weak and may need removal at some point
	 *
	 * @covers  JInstaller::abort
	 *
	 * @expectedException  RuntimeException
	 *
	 * @return void
	 */
	public function testAbortDebug()
	{
		$configMock = $this->getMock('test', array('get'));

		$configMock->expects($this->atLeastOnce())
			->method('get')
			->with($this->equalTo('debug'))
			->will($this->returnValue(true));

		JFactory::$config = $configMock;

		$this->assertThat(
			$this->object->abort(),
			$this->isTrue()
		);
	}

	/**
	 * Tests the parseXMLInstallFile method
	 *
	 * @since   3.1
	 *
	 * @return  void
	 */
	public function testParseXMLInstallFile()
	{
		$xml = JInstaller::parseXMLInstallFile(__DIR__ . '/data/pkg_joomla.xml');

		// Verify the method returns an array of data
		$this->assertThat(
			$xml,
			$this->isType('array'),
			'Ensure JInstaller::parseXMLInstallFile returns an array'
		);

		// Verify the version string in the $xml object matches that from the XML file
		$this->assertThat(
			$xml['version'],
			$this->equalTo('2.5.0'),
			'The version string should be 2.5.0 as specified in the parsed XML file'
		);
	}

	/**
	 * Tests the isManifest method
	 *
	 * @since   3.1
	 *
	 * @return  void
	 */
	public function testIsManifest()
	{
		$this->assertThat(
			$this->object->isManifest(__DIR__ . '/data/pkg_joomla.xml'),
			$this->isInstanceOf('SimpleXmlElement'),
			'Ensure JInstaller::isManifest properly tests a valid manifest file'
		);
	}
}
