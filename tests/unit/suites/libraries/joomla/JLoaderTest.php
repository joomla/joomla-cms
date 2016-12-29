<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLoader.
 *
 * @package  Joomla.UnitTest
 * @since    11.1
 */
class JLoaderTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Container for JLoader static values during tests.
	 *
	 * @var    array
	 * @since  12.3
	 */
	protected static $cache = array();

	/**
	 * JLoader is an abstract class of static functions and variables, so will test without instantiation
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $object;

	/**
	 * The path to the bogus object for loader testing.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $bogusPath;

	/**
	 * The full path (including filename) to the bogus object.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $bogusFullPath;

	/**
	 * Cache the JLoader settings while we are resetting things for testing.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public static function setUpBeforeClass()
	{
		self::$cache['classes']    = TestReflection::getValue('JLoader', 'classes');
		self::$cache['imported']   = TestReflection::getValue('JLoader', 'imported');
		self::$cache['prefixes']   = TestReflection::getValue('JLoader', 'prefixes');
		self::$cache['namespaces'] = TestReflection::getValue('JLoader', 'namespaces');
		self::$cache['classAliases'] = TestReflection::getValue('JLoader', 'classAliases');
	}

	/**
	 * Restore the JLoader cache settings after testing the class.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public static function tearDownAfterClass()
	{
		JLoader::setup();
		TestReflection::setValue('JLoader', 'classes', self::$cache['classes']);
		TestReflection::setValue('JLoader', 'imported', self::$cache['imported']);
		TestReflection::setValue('JLoader', 'prefixes', self::$cache['prefixes']);
		TestReflection::setValue('JLoader', 'namespaces', self::$cache['namespaces']);
		TestReflection::setValue('JLoader', 'classAliases', self::$cache['classAliases']);
	}

	/**
	 * The test cases for importing classes
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesImport()
	{
		return array(
			'factory' => array('joomla.factory', null, null, true, 'factory should load properly', true),
			'jfactory' => array('joomla.jfactory', null, null, false, 'JFactory does not exist so should not load properly', true),
			'fred.factory' => array('fred.factory', null, null, false, 'fred.factory does not exist', true),
			'bogus' => array('bogusload', JPATH_TEST_STUBS, '', true, 'bogusload.php should load properly', false),
			'helper' => array('joomla.user.helper', null, '', true, 'userhelper should load properly', true));
	}

	/**
	 * The test cases for jimport-ing classes
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesJimport()
	{
		return array(
			'fred.factory' => array('fred.factory', false, 'fred.factory does not exist'),
			'browser' => array('joomla.environment.browser', true, 'JBrowser should load properly'));
	}

	/**
	 * Tests the JLoader::discover method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testDiscover()
	{
		$classes = JLoader::getClassList();

		JLoader::discover(null, 'invalid/folder');

		$this->assertThat(JLoader::getClassList(), $this->equalTo($classes), 'Tests that an invalid folder is ignored.');

		JLoader::discover(null, JPATH_TEST_STUBS . '/discover1');
		$classes = JLoader::getClassList();

		$this->assertThat(
			realpath($classes['challenger']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover1/challenger.php')),
			'Checks that the class path is correct (1).'
		);

		$this->assertThat(
			realpath($classes['columbia']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover1/columbia.php')),
			'Checks that the class path is correct (2).'
		);

		$this->assertThat(isset($classes['enterprise']), $this->isFalse(), 'Checks that non-php files are ignored.');

		JLoader::discover('Shuttle', JPATH_TEST_STUBS . '/discover1');
		$classes = JLoader::getClassList();

		$this->assertThat(
			realpath($classes['shuttlechallenger']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover1/challenger.php')),
			'Checks that the class path with prefix is correct (1).'
		);

		$this->assertThat(
			realpath($classes['shuttlecolumbia']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover1/columbia.php')),
			'Checks that the class path with prefix is correct (2).'
		);

		JLoader::discover('Shuttle', JPATH_TEST_STUBS . '/discover2', false);
		$classes = JLoader::getClassList();

		$this->assertThat(
			realpath($classes['shuttlechallenger']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover1/challenger.php')),
			'Checks that the original class paths are maintained when not forced.'
		);

		$this->assertThat(
			isset($classes['atlantis']), $this->isFalse(), 'Checks that directory was not recursed.');

		JLoader::discover('Shuttle', JPATH_TEST_STUBS . '/discover2', true, true);
		$classes = JLoader::getClassList();

		$this->assertThat(
			realpath($classes['shuttlechallenger']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover2/challenger.php')),
			'Checks that force overrides existing classes.'
		);

		$this->assertThat(
			realpath($classes['shuttleatlantis']),
			$this->equalTo(realpath(JPATH_TEST_STUBS . '/discover2/discover3/atlantis.php')),
			'Checks that recurse works.'
		);
	}

	/**
	 * Tests the JLoader::getClassList method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetClassList()
	{
		$this->assertThat(JLoader::getClassList(), $this->isType('array'), 'Tests the we get an array back.');
	}

	/**
	 * Tests the JLoader::load method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoad()
	{
		JLoader::discover('Shuttle', JPATH_TEST_STUBS . '/discover2', true);

		JLoader::load('ShuttleChallenger');

		$this->assertThat(JLoader::load('ShuttleChallenger'), $this->isTrue(), 'Tests that the class file was loaded.');

		$this->assertThat(defined('CHALLENGER_LOADED'), $this->isTrue(), 'Tests that the class file was loaded.');

		$this->assertThat(JLoader::load('Mir'), $this->isFalse(), 'Tests that an unknown class is ignored.');

		$this->assertThat(JLoader::load('JLoaderTest'), $this->isTrue(), 'Tests that a loaded class returns true.');
	}

	/**
	 * Tests the JLoader::load method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadForSinglePart()
	{
		JLoader::registerPrefix('Joomla', JPATH_TEST_STUBS . '/loader', true);

		$this->assertTrue(class_exists('JoomlaPatch'), 'Tests that a class with a single part is loaded in the base path.');
		$this->assertTrue(class_exists('JoomlaPatchTester'), 'Tests that a class with multiple parts is loaded from the correct path.');
		$this->assertTrue(class_exists('JoomlaTester'), 'Tests that a class with a single part is loaded from a folder (legacy behavior).');
		$this->assertFalse(class_exists('JoomlaNotPresent'), 'Tests that a non-existing class is not found.');
	}

	/**
	 * Tests if JLoader::applyAliasFor runs automatically when loading a class by its real name
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testApplyAliasForAutorun()
	{
		JLoader::discover('Shuttle', JPATH_TEST_STUBS . '/discover2', true);

		JLoader::registerAlias('ShuttleOV105', 'ShuttleEndeavour');

		$this->assertThat(JLoader::load('ShuttleEndeavour'), $this->isTrue(), 'Tests that the class file was loaded.');

		$this->assertTrue(class_exists('ShuttleOV105'), 'Tests that loading a class also loads its aliases');
	}

	/**
	 * The success of this test depends on some files being in the file system to be imported. If the FS changes, this test may need revisited.
	 *
	 * @param   string   $filePath     Path to object
	 * @param   string   $base         Path to location of object
	 * @param   string   $libraries    Which libraries to use
	 * @param   boolean  $expect       Result of import (True = success)
	 * @param   string   $message      Failure message
	 * @param   boolean  $useDefaults  Use the default function arguments
	 *
	 * @return  void
	 *
	 * @dataProvider casesImport
	 * @since   11.1
	 */
	public function testImport($filePath, $base, $libraries, $expect, $message, $useDefaults)
	{
		if ($useDefaults)
		{
			$output = JLoader::import($filePath);
		}
		else
		{
			$output = JLoader::import($filePath, $base, $libraries);
		}

		$this->assertThat($output, $this->equalTo($expect), $message);
	}

	/**
	 * This tests the convenience function jimport.
	 *
	 * @param   string   $object   Name of object to be imported
	 * @param   boolean  $expect   Expected result
	 * @param   string   $message  Failure message to be displayed
	 *
	 * @return  void
	 *
	 * @dataProvider casesJimport
	 * @since   11.1
	 */
	public function testJimport($object, $expect, $message)
	{
		$this->assertEquals($expect, jimport($object), $message);
	}

	/**
	 * Tests the JLoader::register method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testRegister()
	{
		JLoader::register('BogusLoad', $this->bogusFullPath);

		$this->assertThat(
			in_array($this->bogusFullPath, JLoader::getClassList()),
			$this->isTrue(),
			'Tests that the BogusLoad class has been registered.'
		);

		JLoader::register('fred', 'fred.php');

		$this->assertThat(
			in_array('fred.php', JLoader::getClassList()),
			$this->isFalse(),
			'Tests that a file that does not exist does not get registered.'
		);
	}

	/**
	 * Tests the JLoader::registerNamespace method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRegisterNamespace()
	{
		// Try with a valid path.
		$path = JPATH_TEST_STUBS . '/discover1';
		JLoader::registerNamespace('discover', $path);

		$namespaces = JLoader::getNamespaces();

		$this->assertContains($path, $namespaces['discover']);

		// Try to add an other path for the namespace.
		$path = JPATH_TEST_STUBS . '/discover2';
		JLoader::registerNamespace('discover', $path);
		$namespaces = JLoader::getNamespaces();

		$this->assertCount(2, $namespaces['discover']);
		$this->assertContains($path, $namespaces['discover']);
	}

	/**
	 * Tests the JLoader::registerNamespace method when reseting the paths.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRegisterNamespaceResetPath()
	{
		// Insert a first path.
		$path = JPATH_TEST_STUBS . '/discover1';
		JLoader::registerNamespace('discover', $path);

		// Reset the path with a new path.
		$path = JPATH_TEST_STUBS . '/discover2';
		JLoader::registerNamespace('discover', $path, true);

		$namespaces = JLoader::getNamespaces();
		$this->assertCount(1, $namespaces['discover']);
		$this->assertContains($path, $namespaces['discover']);
	}

	/**
	 * Tests the exception thrown by the JLoader::registerNamespace method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException  RuntimeException
	 */
	public function testRegisterNamespaceException()
	{
		JLoader::registerNamespace('Color', 'dummy');
	}

	/**
	 * Tests the JLoader::registerPrefix method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testRegisterPrefix()
	{
		// Clear the prefixes array for this test
		TestReflection::setValue('JLoader', 'prefixes', array());

		// Add the libraries/joomla and libraries/legacy folders to the array
		JLoader::registerPrefix('J', JPATH_PLATFORM . '/joomla');
		JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');

		// Get the current prefixes array
		$prefixes = TestReflection::getValue('JLoader', 'prefixes');

		$this->assertEquals(
			$prefixes['J'][0],
			JPATH_PLATFORM . '/joomla',
			'Assert that paths are added in FIFO order by default'
		);

		// Add the libraries/cms folder to the front of the array
		JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

		// Get the current prefixes array
		$prefixes = TestReflection::getValue('JLoader', 'prefixes');

		$this->assertEquals(
			$prefixes['J'][0],
			JPATH_PLATFORM . '/cms',
			'Assert that the libraries/cms folder is prepended to the front of the array'
		);
	}

	/**
	 * Tests the JLoader::registerAlias method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRegisterAlias()
	{
		// Clear the prefixes array for this test
		TestReflection::setValue('JLoader', 'classAliases', array());
		TestReflection::setValue('JLoader', 'classAliasesInverse', array());

		JLoader::registerAlias('foo', 'bar');

		// Get the current prefixes array
		$aliases = TestReflection::getValue('JLoader', 'classAliases');
		$aliasesInverse = TestReflection::getValue('JLoader', 'classAliasesInverse');

		$this->assertEquals(
			$aliases['foo'],
			'bar',
			'Assert the alias is set in the classAlias array.'
		);

		$this->assertArrayHasKey(
			'bar',
			$aliasesInverse,
			'Assert the real class is set in the classAliasInverse array.'
		);

		$this->assertEquals(
			array('foo'),
			$aliasesInverse['bar'],
			'Assert the alias is set in the classAliasInverse array for the real class.'
		);

		JLoader::registerAlias('baz', 'bar');

		$aliasesInverse = TestReflection::getValue('JLoader', 'classAliasesInverse');

		$this->assertEquals(
			array('foo', 'baz'),
			$aliasesInverse['bar'],
			'Assert you can assign multiple aliases for each real class.'
		);

		$this->assertEquals(
			JLoader::registerAlias('foo', 'bar'),
			false,
			'Assert adding an existing alias will return false.'
		);
	}

	/**
	 * Tests the exception thrown by the JLoader::registerPrefix method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @expectedException RuntimeException
	 */
	public function testRegisterPrefixException()
	{
		JLoader::registerPrefix('P', __DIR__ . '/doesnotexist');
	}

	/**
	 * Tests the JLoader::setup method with the default parameters.
	 * We expect the class map, prefix loaders and the J prefix to be registered correctly.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetupDefaultParameters()
	{
		// Reset the prefixes.
		TestReflection::setValue('JLoader', 'prefixes', array());

		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the autoloader with the default parameters.
		JLoader::setup();

		// Get the list of autoload functions.
		$newLoaders = spl_autoload_functions();

		$foundLoad = false;
		$foundAutoload = false;
		$foundLoadByPsr0 = false;
		$foundLoadByAlias = false;

		// We search the list of autoload functions to see if our methods are there.
		foreach ($newLoaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'load')
				{
					$foundLoad = true;
				}

				if ($loader[1] === '_autoload')
				{
					$foundAutoload = true;
				}

				if ($loader[1] === 'loadByPsr0')
				{
					$foundLoadByPsr0 = true;
				}

				if ($loader[1] === 'loadByAlias')
				{
					$foundLoadByAlias = true;
				}
			}
		}

		// Assert the class map loader is found.
		$this->assertTrue($foundLoad);

		// Assert the J prefix has been registered.
		$prefixes = TestReflection::getValue('JLoader', 'prefixes');
		$this->assertArrayHasKey('J', $prefixes);

		// Assert the prefix loader is found.
		$this->assertTrue($foundAutoload);

		// Assert the PSR-0 loader is found.
		$this->assertTrue($foundLoadByPsr0);

		// Assert the Alias loader is found.
		$this->assertTrue($foundLoadByAlias);
	}

	/**
	 * Tests the JLoader::setup method with $enableClasses = false.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetupWithoutClasses()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Set up the auto loader with $enableClasses = false.
		JLoader::setup(false, true, false);

		// Get the list of autoload functions.
		$loaders = spl_autoload_functions();

		$foundLoad = false;

		// We search the list of autoload functions to see if our methods are there.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'load')
				{
					$foundLoad = true;
				}
			}
		}

		// We don't expect to find it.
		$this->assertFalse($foundLoad);
	}

	/**
	 * Tests the JLoader::setup method with $enablePrefixes = false.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetupWithoutPrefixes()
	{
		// Reset the prefixes.
		TestReflection::setValue('JLoader', 'prefixes', array());

		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $enablePrefixes = false.
		JLoader::setup(false, false, true);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$foundAutoLoad = false;

		// We search the list of autoload functions to see if our methods are there.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === '_autoload')
				{
					$foundAutoLoad = true;
				}
			}
		}

		// We don't expect to find it.
		$this->assertFalse($foundAutoLoad);

		// Assert the J prefix hasn't been registered.
		$prefixes = TestReflection::getValue('JLoader', 'prefixes');
		$this->assertFalse(isset($prefixes['J']));
	}

	/**
	 * Tests the JLoader::setup method.
	 * We test the registration of the PSR-0 loader.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetupPsr0()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $enablePsr = true.
		JLoader::setup(true, false, false);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$foundLoadPsr0 = false;
		$foundLoadAlias = false;

		// We search the list of autoload functions to see if our method is here.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'loadByPsr0')
				{
					$foundLoadPsr0 = true;
				}

				if ($loader[1] === 'loadByAlias')
				{
					$foundLoadAlias = true;
				}
			}
		}

		// We expect to find it.
		$this->assertTrue($foundLoadPsr0);

		// We expect to find it.
		$this->assertTrue($foundLoadAlias);
	}

	/**
	 * A function to unregister the Joomla auto loaders.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function unregisterLoaders()
	{
		// Get all auto load functions.
		$loaders = spl_autoload_functions();

		// Unregister all Joomla loader functions if registered.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader'
				&& ($loader[1] === 'load'
				|| $loader[1] === '_autoload'
				|| $loader[1] === 'loadByPsr0'
				|| $loader[1] === 'loadByAlias'))
			{
				spl_autoload_unregister($loader);
			}
		}
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		$this->bogusPath = JPATH_TEST_STUBS . '';
		$this->bogusFullPath = JPATH_TEST_STUBS . '/bogusload.php';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->bogusPath, $this->bogusFullPath);
	}
}
