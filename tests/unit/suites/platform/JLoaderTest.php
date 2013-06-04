<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @covers  JLoader::discover
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
	 * @covers  JLoader::getClassList
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
	 * @covers  JLoader::load
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
	 * Test the JLoader::loadByNamespaceLowerCase method
	 * with lower case namespace and path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceLowerCase
	 */
	public function testLoadByNamespaceLowerCase()
	{
		// Register the 'animal' lower case namespace and lower case path.
		$path = JPATH_TEST_STUBS . '/animal1';
		JLoader::registerNamespace('animal', $path);

		// Register a second lower case path for that namespace.
		$path = JPATH_TEST_STUBS . '/animal2';
		JLoader::registerNamespace('animal', $path);

		// Check we can load a class from the first path.
		$this->assertTrue(JLoader::loadByNamespaceLowerCase('animal\\Cat'));

		// Check we can load a class from the second path.
		$this->assertTrue(JLoader::loadByNamespaceLowerCase('animal\\Dog'));
	}

	/**
	 * Test the JLoader::loadByNamespaceLowerCase method
	 * with camel case namespace and lower case path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceLowerCase
	 */
	public function testLoadByNamespaceLowerCaseCamelCaseNamespace()
	{
		// Register a camel cased namespace but lower case path.
		$path = JPATH_TEST_STUBS . '/chess';
		JLoader::registerNamespace('Chess', $path);

		// Check we can load it by using his camel cased name.
		$this->assertTrue(JLoader::loadByNamespaceLowerCase('Chess\\Piece\\Pawn'));
	}

	/**
	 * Tests the JLoader::loadByNamespaceNaturalCase method
	 * with lower case namespace and lower case path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceNaturalCase
	 */
	public function testLoadByNamespaceNaturalCaseLowCase()
	{
		// Test with a lower case path and lower case namespace.
		$path = JPATH_TEST_STUBS . '/animal1';
		JLoader::registerNamespace('animal', $path);

		$this->assertTrue(JLoader::loadByNamespaceLowerCase('animal\\Cat'));
	}

	/**
	 * Tests the JLoader::loadByNamespaceNaturalCase method
	 * with a camel case namespace and camel case path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceNaturalCase
	 */
	public function testLoadByNamespaceNaturalCaseCamelCase()
	{
		// Register the Color namespace and its path (camel case).
		$path = JPATH_TEST_STUBS . '/Color';
		JLoader::registerNamespace('Color', $path);

		// Register a second path for that namespace (camel case).
		$path = JPATH_TEST_STUBS . '/Color2';
		JLoader::registerNamespace('Color', $path);

		// Check we can load a class from the first path.
		$this->assertTrue(JLoader::loadByNamespaceNaturalCase('Color\\Rgb\\Red'));

		// Check we can load a class from the second path.
		$this->assertTrue(JLoader::loadByNamespaceNaturalCase('Color\\Blue'));
	}

	/**
	 * Tests the JLoader::loadByNamespaceMixedCase method
	 * with a lower case namespace and path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceMixedCase
	 */
	public function testLoadByNamespaceMixedCaseLow()
	{
		// Register the 'animal' lower case namespace and lower case path.
		$path = JPATH_TEST_STUBS . '/animal1';
		JLoader::registerNamespace('animal', $path);

		// Register a second lower case path for that namespace.
		$path = JPATH_TEST_STUBS . '/animal2';
		JLoader::registerNamespace('animal', $path);

		// Check we can load a class from the first path.
		$this->assertTrue(JLoader::loadByNamespaceMixedCase('animal\\Cat'));

		// Check we can load a class from the second path.
		$this->assertTrue(JLoader::loadByNamespaceMixedCase('animal\\Dog'));
	}

	/**
	 * Tests the JLoader::loadByNamespaceMixedCase method
	 * with a camel case namespace and path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceMixedCase
	 */
	public function testLoadByNamespaceMixedCaseCamelCase()
	{
		// Register the Color namespace and its path (camel case).
		$path = JPATH_TEST_STUBS . '/Color';
		JLoader::registerNamespace('Color', $path);

		// Register a second path for that namespace (camel case).
		$path = JPATH_TEST_STUBS . '/Color2';
		JLoader::registerNamespace('Color', $path);

		// Check we can load a class from the first path.
		$this->assertTrue(JLoader::loadByNamespaceMixedCase('Color\\Rgb\\Red'));

		// Check we can load a class from the second path.
		$this->assertTrue(JLoader::loadByNamespaceMixedCase('Color\\Blue'));
	}

	/**
	 * Tests the JLoader::loadByNamespaceMixedCase method
	 * with a camel case namespace and low case path.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceMixedCase
	 */
	public function testLoadByNamespaceMixedCaseCamelCaseNamespaceLowCasePath()
	{
		// Register a camel cased namespace but lower case path.
		$path = JPATH_TEST_STUBS . '/chess';
		JLoader::registerNamespace('Chess', $path);

		// Check we can load it by using his camel cased name.
		$this->assertTrue(JLoader::loadByNamespaceLowerCase('Chess\\Piece\\Pawn'));
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
	 * @covers  JLoader::import
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
	 * @covers  JLoader::register
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
	 * @covers  JLoader::registerNamespace
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
	 * @covers  JLoader::registerNamespace
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
	 * @covers  JLoader::registerNamespace
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
	 * @covers  JLoader::registerPrefix
	 * @todo    Implement testRegisterPrefix().
	 */
	public function testRegisterPrefix()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Tests the exception thrown by the JLoader::registerPrefix method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JLoader::registerPrefix
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
	 * @covers  JLoader::setup
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
		$foundLoadByNamespaceLowerCase = false;
		$loadByNamespaceNaturalCase = false;
		$loadByNamespaceMixedCase = false;

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

				if ($loader[1] === 'loadByNamespaceLowerCase')
				{
					$foundLoadByNamespaceLowerCase = true;
				}

				if ($loader[1] === 'loadByNamespaceNaturalCase')
				{
					$loadByNamespaceNaturalCase = true;
				}

				if ($loader[1] === 'loadByNamespaceMixedCase')
				{
					$loadByNamespaceMixedCase = true;
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

		// Assert the namespace loaders are not found.
		$this->assertFalse($foundLoadByNamespaceLowerCase);
		$this->assertFalse($loadByNamespaceNaturalCase);
		$this->assertFalse($loadByNamespaceMixedCase);
	}

	/**
	 * Tests the JLoader::setup method with $enableClasses = false.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::setup
	 */
	public function testSetupWithoutClasses()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Set up the auto loader with $enableClasses = false.
		JLoader::setup(1, true, true, false);

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
	 * @covers  JLoader::setup
	 */
	public function testSetupWithoutPrefixes()
	{
		// Reset the prefixes.
		TestReflection::setValue('JLoader', 'prefixes', array());

		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $enablePrefixes = false.
		JLoader::setup(1, true, false, true);

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
	 * We test the registration of the lower case namespace loader.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::setup
	 */
	public function testSetupNamespacesLowerCase()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $caseStrategy = 1 (lower case) and enableNamespace = true.
		JLoader::setup(JLoader::LOWER_CASE, true, false, false);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$foundLoadByNamespaceLowerCase = false;

		// We search the list of autoload functions to see if our method is here.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'loadByNamespaceLowerCase')
				{
					$foundLoadByNamespaceLowerCase = true;
				}
			}
		}

		// We expect to find it.
		$this->assertTrue($foundLoadByNamespaceLowerCase);
	}

	/**
	 * Tests the JLoader::setup method.
	 * We test the registration of the Natural case namespace loader.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::setup
	 */
	public function testSetupNamespacesNaturalCase()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $caseStrategy = 2 (natural case) and enableNamespace = true.
		JLoader::setup(JLoader::NATURAL_CASE, true, false, false);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$loadByNamespaceNaturalCase = false;

		// We search the list of autoload functions to see if our method is here.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'loadByNamespaceNaturalCase')
				{
					$loadByNamespaceNaturalCase = true;
				}
			}
		}

		// We expect to find it.
		$this->assertTrue($loadByNamespaceNaturalCase);
	}

	/**
	 * Tests the JLoader::setup method.
	 * We test the registration of the Mixed case namespace loader.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::setup
	 */
	public function testSetupNamespacesMixedCase()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with $caseStrategy = 3 (mixed case) and enableNamespace = true.
		JLoader::setup(JLoader::MIXED_CASE, true, false, false);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$loadByNamespaceMixedCase = false;

		// We search the list of autoload functions to see if our method is here.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'loadByNamespaceMixedCase')
				{
					$loadByNamespaceMixedCase = true;
				}
			}
		}

		// We expect to find it.
		$this->assertTrue($loadByNamespaceMixedCase);
	}

	/**
	 * Tests the JLoader::setup method.
	 * We test the registration of the namespace loader with an invalid case strategy.
	 * We expect the lower case namespace loader to be registered by default.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::setup
	 */
	public function testSetupNamespacesInvalidCase()
	{
		// We unregister all loader functions if registered.
		$this->unregisterLoaders();

		// Setup the loader with and invalid case strategy and enableNamespace = true.
		JLoader::setup('invalid', true, false, false);

		// Get the autoload functions
		$loaders = spl_autoload_functions();

		$foundLoadByNamespaceLowerCase = false;
		$loadByNamespaceNaturalCase = false;
		$loadByNamespaceMixedCase = false;

		// We search the list of autoload functions to see if our methods are here.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader')
			{
				if ($loader[1] === 'loadByNamespaceLowerCase')
				{
					$foundLoadByNamespaceLowerCase = true;
				}

				if ($loader[1] === 'loadByNamespaceNaturalCase')
				{
					$loadByNamespaceNaturalCase = true;
				}

				if ($loader[1] === 'loadByNamespaceMixedCase')
				{
					$loadByNamespaceMixedCase = true;
				}
			}
		}

		// We expect to find only the lower case loader registered.
		$this->assertTrue($foundLoadByNamespaceLowerCase);
		$this->assertFalse($loadByNamespaceNaturalCase);
		$this->assertFalse($loadByNamespaceMixedCase);
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
				|| $loader[1] === 'loadByNamespaceLowerCase'
				|| $loader[1] === 'loadByNamespaceNaturalCase'
				|| $loader[1] === 'loadByNamespaceMixedCase'))
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
}
