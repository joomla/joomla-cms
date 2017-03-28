<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstaller.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerTest extends TestCaseDatabase
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

		$this->object = new JInstaller;
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
		unset($this->object);

		parent::tearDown();
	}


	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
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
		$object1 = JInstaller::getInstance();

		$this->assertInstanceOf(
			'JInstaller',
			$object1
		);

		$this->assertSame(
			$object1,
			JInstaller::getInstance()
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

		$this->assertFalse(
			$this->object->isOverwrite(),
			'Get or Set overwrite failed'
		);

		$this->assertFalse(
			$this->object->setOverwrite(true),
			'setOverwrite did not return the old value properly.'
		);

		$this->assertTrue(
			$this->object->isOverwrite(),
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

		$this->assertEquals(
			$this->object->getRedirectUrl(),
			'http://www.example.com',
			'Get or Set Redirect URL failed'
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

		$this->assertFalse(
			$this->object->isUpgrade(),
			'Get or Set Upgrade failed'
		);

		$this->assertFalse(
			$this->object->setUpgrade(true),
			'setUpgrade did not return the old value properly.'
		);

		$this->assertTrue(
			$this->object->isUpgrade(),
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
		$this->assertEquals(
			$this->object->getPath('path1_getpath', 'default_value'),
			'default_value',
			'getPath did not return the default value for an undefined path'
		);

		$this->object->setPath('path2_getpath', JPATH_BASE . '/required_path');

		$this->assertEquals(
			$this->object->getPath('path2_getpath', 'default_value'),
			JPATH_BASE . '/required_path',
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
	public function testAbortDefault()
	{
		// Build the mock object.
		$adapterMock  = $this->getMockBuilder('test')
					->setMethods(array('_rollback_testtype'))
					->getMock();

		$adapterMock->expects($this->once())
			->method('_rollback_testtype')
			->with($this->equalTo(array('type' => 'testtype')))
			->will($this->returnValue(true));

		$this->object->setAdapter('testadapter', $adapterMock);

		$this->object->pushStep(array('type' => 'testtype'));

		$this->assertTrue(
			$this->object->abort(null, 'testadapter')
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

		$this->assertFalse(
			$this->object->abort(null, false)
		);
	}

	/**
	 * @testdox  Ensure parseLanguages() returns 0 when there are no children in the language tag
	 *
	 * @covers   JInstaller::parseLanguages
	 */
	public function testParseLanguagesWithNoChildren()
	{
		$emptyXml = new SimpleXMLElement('<languages></languages>');

		$this->assertEquals(
			0,
			$this->object->parseLanguages($emptyXml)
		);
	}

	/**
	 * @testdox  Ensure parseFiles() returns 0 when there are no children in the files tag
	 *
	 * @covers   JInstaller::parseFiles
	 */
	public function testParseFilesWithNoChildren()
	{
		$emptyXml = new SimpleXMLElement('<files></files>');

		$this->assertEquals(
			0,
			$this->object->parseFiles($emptyXml)
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
		$this->assertInternalType(
			'array',
			$xml,
			'Ensure JInstaller::parseXMLInstallFile returns an array'
		);

		// Verify the version string in the $xml object matches that from the XML file
		$this->assertEquals(
			$xml['version'],
			'2.5.0',
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
		$this->assertInstanceOf(
			'SimpleXmlElement',
			$this->object->isManifest(__DIR__ . '/data/pkg_joomla.xml'),
			'Ensure JInstaller::isManifest properly tests a valid manifest file'
		);
	}
}
