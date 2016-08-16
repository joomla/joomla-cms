<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstallerAdapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.4.4
 */
class JInstallerAdapterTest extends TestCaseDatabase
{
	/**
	 * Used in tests for callbacks involving JInstaller::setOverwrite()
	 *
	 * @var  boolean
	 */
	protected static $installerOverwrite;

	/**
	 * Used in tests for callbacks involving JInstaller::setUpgrade()
	 *
	 * @var  boolean
	 */
	protected static $installerUpgrade;

	/**
	 * Sample Manifest String
	 */
	protected $sampleManifest = '<extension><name>com_content</name><element>com_content</element><description>Dummy Description Text</description><update><schemas><schemapath type="mysql">sql/updates/mysql</schemapath></schemas></update></extension>';

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 */
	public function setUp()
	{
		parent::setUp();

		// Mock JFilter
		$filterMock = $this->getMock('JFilterInput', array('filter'));
		$filterSig = md5(serialize(array(array(), array(), 0, 0, 1)));
		TestReflection::setValue('JFilterInput', 'instances', array($filterSig => $filterMock));
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown()
	{
		// Reset the filter instances.
		TestReflection::setValue('JFilterInput', 'instances', array());

		parent::tearDown();
	}

	/**
	 * @testdox Tests the public constructor
	 * 
	 * @covers  JInstallerAdapter::__construct
	 */
	public function testConstructor()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase, array('foo' => 'bar')));

		$this->assertAttributeInstanceOf('JTableExtension', 'extension', $object);

		$this->assertAttributeSame($mockDatabase, 'db', $object);
		$this->assertAttributeSame($mockInstaller, 'parent', $object);

		$this->assertEquals(
			'bar',
			$object->foo,
			'Tests any options are set as class variables by JObject'
		);
	}

	/**
	 * @testdox Test checking if an existing extension exists
	 * 
	 * @covers  JInstallerAdapter::checkExistingExtension
	 */
	public function testCheckExistingExtensionForExistingExtension()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));

		// A set of data for an extension
		$type = 'plugin';
		$element = 'plg_finder_content';
		$extensionId = 444;

		// We expect the find method to be called once and this will return the extension ID
		$mockTableExtension->expects($this->once())
			->method('find')
			->with(array('element' => $element, 'type' => $type))
			->willReturn($extensionId);

		// Ensure that load is called once the extension ID is found
		$mockTableExtension->expects($this->once())
			->method('load')
			->with(array('element' => $element, 'type' => $type));

		TestReflection::setValue($object, 'extension', $mockTableExtension);
		TestReflection::setValue($object, 'type', $type);
		TestReflection::setValue($object, 'element', $element);

		// Invoke the method
		TestReflection::invoke($object, 'checkExistingExtension');

		$this->assertAttributeEquals(
			$extensionId,
			'currentExtensionId',
			$object,
			'The extension ID was not populated correctly for a found extension'
		);
	}

	/**
	 * @testdox Test checking if an existing extension exists with an extension that doesn't exist
	 * 
	 * @covers  JInstallerAdapter::checkExistingExtension
	 */
	public function testCheckExistingExtensionForExtensionThatDoesNotExist()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));

		// A set of data for an extension
		$type = 'plugin';
		$element = 'plg_finder_foo';

		// We expect the find method to be called once and this will return the extension ID
		$mockTableExtension->expects($this->once())
			->method('find')
			->with(array('element' => $element, 'type' => $type))
			->willReturn(null);

		// Ensure that load is not called when the extension isn't found
		$mockTableExtension->expects($this->never())
			->method('load');

		TestReflection::setValue($object, 'extension', $mockTableExtension);
		TestReflection::setValue($object, 'type', $type);
		TestReflection::setValue($object, 'element', $element);

		// Invoke the method
		TestReflection::invoke($object, 'checkExistingExtension');

		$this->assertNull(
			TestReflection::getValue($object, 'currentExtensionId'),
			'The extension ID should be null for an extension that was not found'
		);
	}


	/**
	 * @testdox Test checking if an existing extension exists
	 *
	 * @expectedException RuntimeException
	 * @covers  JInstallerAdapter::checkExistingExtension
	 */
	public function testCheckExistingExtensionReturnsErrorWhenTableGivesException()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find'), array($this->getMockDatabase()));

		$type = 'plugin';
		$element = 'plg_finder_content';

		$mockTableExtension->expects($this->once())
			->method('find')
			->with(array('element' => $element, 'type' => $type))
			->willThrowException(new RuntimeException);

		TestReflection::setValue($object, 'extension', $mockTableExtension);
		TestReflection::setValue($object, 'type', $type);
		TestReflection::setValue($object, 'element', $element);

		// Invoke the method
		TestReflection::invoke($object, 'checkExistingExtension');
	}

	/**
	 * @testdox JInstallerAdapter::checkExtensionInFilesystem works with an existing XML file and with upgrade flag set to true
	 * 
	 * @covers  JInstallerAdapter::checkExtensionInFilesystem
	 */
	public function testCheckExtensionInFilesystem()
	{
		$mockInstaller = $this->getMock('JInstaller', array('getPath', 'isOverwrite', 'isUpgrade', 'setOverwrite', 'setUpgrade'));
		$mockInstaller->expects($this->once())
			->method('getPath')
			->with('extension_root')
			->willReturn(JPATH_MANIFESTS . '/files/joomla.xml');

		$mockInstaller->expects($this->once())
			->method('isOverwrite')
			->willReturn(true);

		$mockInstaller->expects($this->any())
			->method('isUpgrade')
			->willReturn(true);

		$mockInstaller->expects($this->once())
			->method('setOverwrite')
			->willReturnCallback(array($this, 'installerOverwrite'));

		$mockInstaller->expects($this->once())
			->method('setUpgrade')
			->willReturnCallback(array($this, 'installerUpgrade'));

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));
		$manifestObject = simplexml_load_string($this->sampleManifest);

		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		TestReflection::invoke($object, 'checkExtensionInFilesystem');

		$this->assertAttributeEquals(
			'install',
			'route',
			$object,
			'JInstallerAdapter::checkExtensionInFilesystem() should not update the route unless an extension ID has been set'
		);

		$this->assertTrue(
			self::$installerOverwrite,
			'JInstallerAdapter::checkExtensionInFilesystem() should call JInstaller::setOverwrite with the parameter true'
		);

		$this->assertTrue(
			self::$installerUpgrade,
			'JInstallerAdapter::checkExtensionInFilesystem() should call JInstaller::setUpgrade with the parameter true'
		);
	}

	/**
	 * @testdox JInstallerAdapter::checkExtensionInFilesystem sets the route to update when upgrade is set to true, a file exists and an extension ID is set
	 * 
	 * @covers  JInstallerAdapter::checkExtensionInFilesystem
	 */
	public function testsCheckExtensionInFilesystemInstallerRouteSetWhenFilesystemExistsWithExtensionIdSet()
	{
		$mockInstaller = $this->getMock('JInstaller', array('getPath', 'isOverwrite', 'isUpgrade', 'setOverwrite', 'setUpgrade'));
		$mockInstaller->expects($this->once())
			->method('getPath')
			->with('extension_root')
			->willReturn(JPATH_MANIFESTS . '/files/joomla.xml');

		$mockInstaller->expects($this->once())
			->method('isOverwrite')
			->willReturn(true);

		$mockInstaller->expects($this->any())
			->method('isUpgrade')
			->willReturn(true);

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));
		$manifestObject = simplexml_load_string($this->sampleManifest);

		TestReflection::setValue($object, 'manifest', $manifestObject);
		TestReflection::setValue($object, 'currentExtensionId', 444);

		// Invoke the method
		TestReflection::invoke($object, 'checkExtensionInFilesystem');

		$this->assertAttributeEquals(
			'update',
			'route',
			$object,
			'JInstallerAdapter::checkExtensionInFilesystem() should change the route to upgrade when an extension ID has been set'
		);
	}

	/**
	 * @testdox JInstallerAdapter::checkExtensionInFilesystem throws an exception when a file exists and overwrite is set to false
	 *
	 * @expectedException  RuntimeException
	 * @covers  JInstallerAdapter::checkExtensionInFilesystem
	 */
	public function testsCheckExtensionInFilesystemWithOverwriteSetFalse()
	{
		$mockInstaller = $this->getMock('JInstaller', array('getPath', 'isOverwrite', 'isUpgrade'));
		$mockInstaller->expects($this->any())
			->method('getPath')
			->with('extension_root')
			->willReturn(JPATH_MANIFESTS . '/files/joomla.xml');

		$mockInstaller->expects($this->any())
			->method('isOverwrite')
			->willReturn(false);

		$mockInstaller->expects($this->any())
			->method('isUpgrade')
			->willReturn(false);

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));
		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->update);

		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		TestReflection::invoke($object, 'checkExtensionInFilesystem');
	}

	/**
	 * @testdox JInstallerAdapter::discover_install works correctly
	 * 
	 * @covers  JInstallerAdapter::discover_install
	 */
	public function testDiscoverInstall()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->never())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->once())
			->method('finaliseInstall')
			->willReturn(null);

		$manifestObject = simplexml_load_string($this->sampleManifest);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertEquals(
			444,
			$object->discover_install(),
			'The extension ID was not returned when running discover install'
		);

		$this->assertEquals(
			'Dummy Description Text',
			$mockInstaller->message,
			'The description text was not set into JInstaller'
		);
	}

	/**
	 * @testdox JInstallerAdapter::discover_install works correctly
	 * 
	 * @covers  JInstallerAdapter::discover_install
	 */
	public function testDiscoverInstallWithNoDescription()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->never())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->once())
			->method('finaliseInstall')
			->willReturn(null);

		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->description);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertEquals(
			444,
			$object->discover_install(),
			'The extension ID was not returned when running discover install'
		);

		$this->assertEquals(
			'',
			$mockInstaller->message,
			'The description text was not set into JInstaller'
		);
	}

	/**
	 * Provides the data to test the discover_install method.
	 *
	 * @return  array
	 */
	public function casesTestExceptionThrownInDiscoverInstall()
	{
		return array(
			array('setupInstallPaths'),
			array('storeExtension'),
			array('parseQueries'),
			array('finaliseInstall')
		);
	}

	/**
	 * @testdox JInstallerAdapter::discover_install deals with an exception being thrown in various called JInstallerAdapter internal methods
	 *
	 * @param   string  $method  The method to throw an exception in
	 *
	 * @dataProvider  casesTestExceptionThrownInDiscoverInstall
	 * @covers  JInstallerAdapter::discover_install
	 */
	public function testDiscoverInstallWithExceptionThrownInAdapterMethods($method)
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->once())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths'
			)
		);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock setupInstallPaths throwing an exception
		$object->expects($this->once())
			->method($method)
			->willThrowException(new RuntimeException());

		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->description);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		$this->assertFalse(
			$object->discover_install(),
			'Discover install should return null if an exception is thrown in JInstallerAdapter::' . $method . '()'
		);
	}

	/**
	 * @testdox Test getting the discover install class var
	 * 
	 * @covers  JInstallerAdapter::getDiscoverInstallSupported
	 */
	public function testDefaultGetDiscoverInstallSupported()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$this->assertTrue(
			$object->getDiscoverInstallSupported(),
			'Discover Install should be enabled by default'
		);
	}

	/**
	 * @testdox Test getting the discover install class var
	 * 
	 * @covers  JInstallerAdapter::getDiscoverInstallSupported
	 */
	public function testGetDiscoverInstallSupported()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		TestReflection::setValue($object, 'supportsDiscoverInstall', false);

		$this->assertFalse(
			$object->getDiscoverInstallSupported(),
			'Discover Install should be false is the class var is set'
		);
	}

	/**
	 * @testdox Test getting the element from the manifest
	 * 
	 * @covers  JInstallerAdapter::getElement
	 */
	public function testGetElementWithElementInManifest()
	{
		// Create the test object
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		// Mock the manifest Object and set it into the test object
		$manifestObject = simplexml_load_string($this->sampleManifest);
		$object->manifest = $manifestObject;

		$this->assertEquals(
			'com_content',
			$object->getElement(),
			'The element was not retrieved correctly from the manifest'
		);
	}

	/**
	 * @testdox Test getting the element by injecting it
	 * 
	 * @covers  JInstallerAdapter::getElement
	 */
	public function testGetElementWithInjectedElement()
	{
		// Create the test object
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$this->assertEquals(
			'com_contact',
			$object->getElement('com_contact'),
			'The element was not retrieved correctly after being injected'
		);
	}

	/**
	 * @testdox Test getting the element by injecting it
	 * 
	 * @covers  JInstallerAdapter::getElement
	 */
	public function testGetElementWithElementFromName()
	{
		// Create the test object
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array('getName')
		);
		$object->expects($this->once())
			->method('getName')
			->willReturn('plg_finder_content');

		// Mock the manifest Object and set it into the test object
		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->element);
		$object->manifest = $manifestObject;

		$this->assertEquals(
			'plg_finder_content',
			$object->getElement(),
			'The element was not retrieved correctly after being retrieved from JInstallerAdapter::getName()'
		);
	}

	/**
	 * @testdox Test getting the simple xml object from the manifest
	 * 
	 * @covers  JInstallerAdapter::getManifest
	 */
	public function testGetManifest()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));
		$manifestObject = simplexml_load_string($this->sampleManifest);

		$object->manifest = $manifestObject;

		$this->assertEquals(
			$manifestObject,
			$object->getManifest(),
			'The manifest was not retrieved correctly'
		);
	}

	/**
	 * @testdox Test getting the name from the manifest
	 * 
	 * @covers  JInstallerAdapter::getName
	 */
	public function testGetName()
	{
		// Create the test object
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		// Mock the manifest Object and set it into the test object
		$manifestObject = simplexml_load_string('<extension><name>plg_finder_content</name></extension>');
		$object->manifest = $manifestObject;

		$this->assertEquals(
			'plg_finder_content',
			$object->getName(),
			'The name was not retrieved correctly from JInstallerAdapter::getName()'
		);
	}


	/**
	 * @testdox Test getting the default route
	 */
	public function testGetDefaultRoute()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$this->assertEquals(
			'install',
			$object->getRoute(),
			'JInstallerAdapter::getRoute() should return "install" by default'
		);
	}

	/**
	 * @testdox Test getting a non-default route from the class
	 * 
	 * @covers  JInstallerAdapter::getRoute
	 */
	public function testGetRouteForSetObject()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		TestReflection::setValue($object, 'route', 'update');

		$this->assertEquals(
			'update',
			$object->getRoute(),
			'JInstallerAdapter::getRoute should return the set value'
		);
	}

	/**
	 * Provides the data to test the getScriptClassName method.
	 *
	 * @return  array
	 */
	public function casesGetScriptClassName()
	{
		return array(
			array(
				'com_content',
				'com_contentInstallerScript',
				'An extension installer script should return it\'s name with InstallerScript suffixed'
			),
			array(
				'pkg_en-GB',
				'pkg_enGBInstallerScript',
				'An extension with a hyphen in the name should have the hypen removed in the installer script name'
			),
		);
	}

	/**
	 * @testdox Test getting the script class name for an extension
	 *
	 * @param   string  $element         The element name to set in the clas
	 * @param   string  $element         The expected script name
	 * @param   string  $failureMessage  The failure message
	 *
	 * @dataProvider  casesGetScriptClassName
	 * @covers  JInstallerAdapter::getScriptClassName
	 */
	public function testGetScriptClassName($element, $expected, $failureMessage)
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		TestReflection::setValue($object, 'element', $element);

		$this->assertEquals(
			$expected,
			TestReflection::invoke($object, 'getScriptClassName'),
			$failureMessage
		);
	}

	/**
	 * @testdox JInstallerAdapter::install works correctly
	 * 
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstall()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->never())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->once())
			->method('finaliseInstall')
			->willReturn(null);

		$manifestObject = simplexml_load_string($this->sampleManifest);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertEquals(
			444,
			$object->install(),
			'The extension ID was not returned when running install'
		);

		$this->assertEquals(
			'Dummy Description Text',
			$mockInstaller->message,
			'The description text was not set into JInstaller'
		);
	}

	/**
	 * @testdox JInstallerAdapter::install works correctly when the route is set to update
	 * 
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstallOnUpdateRoute()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->never())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags',
				'setupUpdates'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);
		TestReflection::setValue($object, 'route', 'update');

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->once())
			->method('finaliseInstall')
			->willReturn(null);

		// Check that setupUpdates has been called
		$object->expects($this->once())
			->method('setupUpdates');

		$manifestObject = simplexml_load_string($this->sampleManifest);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertEquals(
			444,
			$object->install(),
			'The extension ID was not returned when running install'
		);

		$this->assertEquals(
			'Dummy Description Text',
			$mockInstaller->message,
			'The description text was not set into JInstaller'
		);
	}

	/**
	 * @testdox JInstallerAdapter::install works correctly
	 * 
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstallAbortsWhenSetupUpdatesThrowsException()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->once())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags',
				'setupUpdates'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);
		TestReflection::setValue($object, 'route', 'update');

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->any())
			->method('finaliseInstall')
			->willReturn(null);

		// Check that setupUpdates has been called
		$object->expects($this->once())
			->method('setupUpdates')
			->willThrowException(new RuntimeException());

		$manifestObject = simplexml_load_string($this->sampleManifest);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertFalse(
			$object->install(),
			'The extension ID was not returned when running install'
		);
	}

	/**
	 * @testdox JInstallerAdapter::install works correctly
	 * 
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstallWithNoDescription()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->never())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags'
			)
		);

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock the finaliseInstall method which doesn't exist in the class
		$object->expects($this->once())
			->method('finaliseInstall')
			->willReturn(null);

		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->description);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		// Invoke the method
		$this->assertEquals(
			444,
			$object->install(),
			'The extension ID was not returned when running install'
		);

		$this->assertEquals(
			'',
			$mockInstaller->message,
			'The description text was not set into JInstaller'
		);
	}

	/**
	 * Provides the data to test the getScriptClassName method.
	 *
	 * @return  array
	 */
	public function casesTestExceptionThrownInInstall()
	{
		return array(
			array('setupInstallPaths'),
			array('checkExistingExtension'),
			array('checkExtensionInFilesystem'),
			array('createExtensionRoot'),
			array('copyBaseFiles'),
			array('storeExtension'),
			array('parseQueries'),
			array('finaliseInstall')
		);
	}

	/**
	 * @testdox JInstallerAdapter::install deals with an exception being thrown in various called JInstallerAdapter internal methods
	 *
	 * @param   string  $method  The method to throw an exception in
	 *
	 * @dataProvider  casesTestExceptionThrownInInstall
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstallWithExceptionThrownInAdapterMethods($method)
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->once())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags'
			)
		);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock setupInstallPaths throwing an exception
		$object->expects($this->once())
			->method($method)
			->willThrowException(new RuntimeException());

		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->description);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		$this->assertFalse(
			$object->install(),
			'Install should return false if an exception is thrown in JInstallerAdapter::' . $method . '()'
		);
	}

	/**
	 * @testdox JInstallerAdapter::install deals with an exception being thrown in JInstallerAdapter::finaliseInstall()
	 * 
	 * @covers  JInstallerAdapter::install
	 */
	public function testInstallWithExceptionThrownInFinaliseInstall()
	{
		$mockInstaller = $this->getMock('JInstaller', array('abort'));

		// For this test we to ensure abort is not called in JInstaller
		$mockInstaller->expects($this->once())
			->method('abort');

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array(
				'getName',
				'getElement',
				'finaliseInstall',
				'triggerManifestScript',
				'parseQueries',
				'storeExtension',
				'setupScriptfile',
				'setupInstallPaths',
				'createExtensionRoot',
				'checkExistingExtension',
				'checkExtensionInFilesystem',
				'copyBaseFiles',
				'parseOptionalTags'
			)
		);

		// Mock the response of internal methods
		$object->expects($this->once())
			->method('getName')
			->willReturn('com_content');
		$object->expects($this->once())
			->method('getElement')
			->willReturn('com_content');

		// Mock setupInstallPaths throwing an exception
		$object->expects($this->once())
			->method('finaliseInstall')
			->willThrowException(new RuntimeException());

		$manifestObject = simplexml_load_string($this->sampleManifest);
		unset($manifestObject->description);
		TestReflection::setValue($object, 'manifest', $manifestObject);

		$this->assertFalse(
			$object->install(),
			'Install should return false if an exception is thrown in JInstallerAdapter::finaliseInstall()'
		);
	}


	/**
	 * @testdox Test parse queries throws an exception with install route and JInstallerAdapter::doDatabaseTransactions() returning false
	 *
	 * @expectedException  RuntimeException
	 * @covers  JInstallerAdapter::parseQueries
	 */
	public function testParseQueriesWithInstallRoute()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array('doDatabaseTransactions')
		);
		TestReflection::setValue($object, 'route', 'install');
		$object->expects($this->once())
			->method('doDatabaseTransactions')
			->willReturn(false);

		TestReflection::invoke($object, 'parseQueries');
	}

	/**
	 * @testdox Test parse queries throws an exception with install route and JInstallerAdapter::doDatabaseTransactions() returning false
	 *
	 * @expectedException  RuntimeException
	 * @covers  JInstallerAdapter::parseQueries
	 */
	public function testParseQueriesWithUpdateRouteAndParsingReturningFalseReturnsException()
	{
		// The sample Schema
		$schema = simplexml_load_string('<extension><update><schemas><schemapath type="mysql">sql/updates/mysql</schemapath></schemas></update></extension>');

		$mockInstaller = $this->getMock('JInstaller', array('parseSchemaUpdates'));
		$mockInstaller->expects($this->once())
			->method('parseSchemaUpdates')
			->with($schema->update->schemas, 444)
			->willReturn(false);

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array('getManifest')
		);
		TestReflection::setValue($object, 'route', 'update');

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Set up the mock manifest object
		$object->expects($this->any())
			->method('getManifest')
			->willReturn($schema);

		TestReflection::invoke($object, 'parseQueries');
	}

	/**
	 * @testdox Test JInstallerAdapter::parseQueries() correctly calls JInstaller::parseSchemaUpdates() when in update route
	 * 
	 * @covers  JInstallerAdapter::parseQueries
	 */
	public function testParseQueriesWithUpdateRouteAndParsingReturningTrueCallsParseSchemaUpdatesCorrectly()
	{
		// The sample Schema
		$schema = simplexml_load_string('<extension><update><schemas><schemapath type="mysql">sql/updates/mysql</schemapath></schemas></update></extension>');

		$mockInstaller = $this->getMock('JInstaller', array('parseSchemaUpdates'));
		$mockInstaller->expects($this->once())
			->method('parseSchemaUpdates')
			->with($schema->update->schemas, 444)
			->willReturn(true);

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array('getManifest')
		);
		TestReflection::setValue($object, 'route', 'update');

		// Set up a mock JTableExtension
		$mockTableExtension = $this->getMock('JTableExtension', array('find', 'load'), array($this->getMockDatabase()));
		$mockTableExtension->extension_id = 444;
		TestReflection::setValue($object, 'extension', $mockTableExtension);

		// Set up the mock manifest object
		$object->expects($this->any())
			->method('getManifest')
			->willReturn($schema);

		TestReflection::invoke($object, 'parseQueries');
	}

	/**
	 * @testdox Test setting a SimpleXML object into the manifest
	 * 
	 * @covers  JInstallerAdapter::setManifest
	 */
	public function testSetManifest()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));
		$manifestObject = simplexml_load_string($this->sampleManifest);

		$this->assertInstanceOf(
			'JInstallerAdapter',
			$object->setManifest($manifestObject),
			'JInstallerAdapter::setManifest() should return an instance of itself'
		);

		$this->assertEquals(
			$manifestObject,
			$object->manifest,
			'The manifest was not set correctly as a class variable'
		);
	}


	/**
	 * @testdox Test setting a string as the route
	 * 
	 * @covers  JInstallerAdapter::setRoute
	 */
	public function testSetRoute()
	{
		$mockInstaller = $this->getMock('JInstaller');
		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$this->assertEquals(
			'install',
			TestReflection::getValue($object, 'route'),
			'The default route of JInstallerAdapter should be "install"'
		);

		$this->assertInstanceOf(
			'JInstallerAdapter',
			$object->setRoute('update'),
			'JInstallerAdapter::setRoute() should return an instance of itself'
		);

		$this->assertEquals(
			'update',
			TestReflection::getValue($object, 'route'),
			'The route was not set correctly as a class variable'
		);
	}

	/**
	 * Provides the data to test the triggerManifestScript method.
	 *
	 * @return  array
	 */
	public function casesTestTriggerManifestScript()
	{
		return array(
			array('install'),
			array('uninstall'),
			array('update'),
		);
	}

	/**
	 * @testdox Test triggering the manifest script for an installer where results are true
	 *
	 * @param   string  $method  The method to run
	 *
	 * @dataProvider  casesTestTriggerManifestScript
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptForMethodsTakingInstallerObjectOnly($method)
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		$mockScript->expects($this->once())
			->method($method)
			->with($object)
			->willReturn(true);

		$mockInstaller->manifestClass = $mockScript;

		$this->assertTrue(
			TestReflection::invoke($object, 'triggerManifestScript', $method)
		);
	}

	/**
	 * Provides the data to test the triggerManifestScript method.
	 *
	 * @return  array
	 */
	public function casesTestTriggerManifestScriptFlights()
	{
		return array(
			array('preflight'),
			array('postflight'),
		);
	}

	/**
	 * @testdox Test triggering the manifest script for an installer where results are true
	 *
	 * @param   string  $method  The method to run
	 *
	 * @dataProvider  casesTestTriggerManifestScriptFlights
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptForMethodsTakingInstallerObjectAndRoute($method)
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		$routeValue = 'update';
		TestReflection::setValue($object, 'route', $routeValue);

		$mockScript->expects($this->once())
			->method($method)
			->with($routeValue, $object)
			->willReturn(true);

		$mockInstaller->manifestClass = $mockScript;

		$this->assertTrue(
			TestReflection::invoke($object, 'triggerManifestScript', $method)
		);
	}

	/**
	 * @testdox Test an exception is thrown when the preflight method returns false
	 *
	 * @expectedException  RuntimeException
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptPreflightReturningFalseThrowsException()
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		$routeValue = 'update';
		TestReflection::setValue($object, 'route', $routeValue);

		$mockScript->expects($this->once())
			->method('preflight')
			->with($routeValue, $object)
			->willReturn(false);

		$mockInstaller->manifestClass = $mockScript;

		TestReflection::invoke($object, 'triggerManifestScript', 'preflight');
	}

	/**
	 * Provides the data to test the triggerManifestScript method.
	 *
	 * @return  array
	 */
	public function casesTestTriggerManifestException()
	{
		return array(
			array('install'),
			array('update'),
		);
	}

	/**
	 * @testdox Test an exception is thrown when the install or update methods return false
	 *
	 * @param   string  $method  The method to run
	 *
	 * @dataProvider       casesTestTriggerManifestException
	 * @expectedException  RuntimeException
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptInstallOrUpdateReturningFalseThrowsException($method)
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		TestReflection::setValue($object, 'route', $method);

		$mockScript->expects($this->once())
			->method($method)
			->with($object)
			->willReturn(false);

		$mockInstaller->manifestClass = $mockScript;

		TestReflection::invoke($object, 'triggerManifestScript', $method);
	}

	/**
	 * @testdox Test an exception isn't thrown when the uninstall method returns false
	 * 
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptUninstallReturningFalseDoesNotThrowAnException()
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		TestReflection::setValue($object, 'route', 'uninstall');

		$mockScript->expects($this->once())
			->method('uninstall')
			->with($object)
			->willReturn(false);

		$mockInstaller->manifestClass = $mockScript;

		$this->assertTrue(
			TestReflection::invoke($object, 'triggerManifestScript', 'uninstall')
		);
	}

	/**
	 * @testdox Test an exception isn't thrown when the postflight method returns false
	 * 
	 * @covers  JInstallerAdapter::triggerManifestScript
	 */
	public function testTriggerManifestScriptPostflightReturningFalseDoesNotThrowAnException()
	{
		$mockInstaller = $this->getMock('JInstaller', array('set'));

		$mockDatabase = $this->getMockDatabase();
		$object       = $this->getMockForAbstractClass('JInstallerAdapter', array($mockInstaller, $mockDatabase));

		$mockScript   = $this->getMock('DummyScript', array('preflight', 'postflight', 'install', 'uninstall', 'update'));

		TestReflection::setValue($object, 'route', 'uninstall');

		$mockScript->expects($this->once())
			->method('postflight')
			->with('uninstall', $object)
			->willReturn(false);

		$mockInstaller->manifestClass = $mockScript;

		$this->assertTrue(
			TestReflection::invoke($object, 'triggerManifestScript', 'postflight')
		);
	}

	/**
	 * @testdox Test running the update method
	 * 
	 * @covers  JInstallerAdapter::update
	 */
	public function testUpdate()
	{
		$mockInstaller = $this->getMock('JInstaller', array('setOverwrite', 'setUpgrade'));

		$mockInstaller->expects($this->once())
			->method('setUpgrade')
			->with(true);

		$mockInstaller->expects($this->once())
			->method('setOverwrite')
			->with(true);

		$mockDatabase = $this->getMockDatabase();
		$object = $this->getMockForAbstractClass(
			'JInstallerAdapter',
			array($mockInstaller, $mockDatabase),
			'',
			true,
			true,
			true,
			array('install')
		);

		// Tests the update method proxies to install
		$object->expects($this->once())
			->method('install');

		$object->update();

		$this->assertAttributeEquals(
			'update',
			'route',
			$object,
			'Checks the route is set in the class var'
		);
	}

	/**
	 * A callback to proxy for JInstaller::setOverwrite()
	 *
	 * @param   boolean  $value  Is overwrite set or not
	 */
	public static function installerOverwrite($value)
	{
		self::$installerOverwrite = (bool) $value;
	}

	/**
	 * A callback to proxy for JInstaller::setUpgrade()
	 *
	 * @param   boolean  $value  Is upgrade set or not
	 */
	public static function installerUpgrade($value)
	{
		self::$installerUpgrade = (bool) $value;
	}
}
