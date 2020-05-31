<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JLoader.
 *
 * @package  Joomla.UnitTest
 * @since    1.7.0
 */
class JLoaderTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Container for JLoader static values during tests.
	 *
	 * @var    array
	 * @since  3.1.4
	 */
	protected static $cache = array();

	/**
	 * JLoader is an abstract class of static functions and variables, so will test without instantiation
	 *
	 * @var    object
	 * @since  1.7.0
	 */
	protected $object;

	/**
	 * The path to the bogus object for loader testing.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $bogusPath;

	/**
	 * The full path (including filename) to the bogus object.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $bogusFullPath;

	/**
	 * Cache the JLoader settings while we are resetting things for testing.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public static function setUpBeforeClass()
	{
		self::$cache['classes']    = TestReflection::getValue('JLoader', 'classes');
		self::$cache['imported']   = TestReflection::getValue('JLoader', 'imported');
		self::$cache['prefixes']   = TestReflection::getValue('JLoader', 'prefixes');
		self::$cache['namespaces'] = TestReflection::getValue('JLoader', 'namespaces');
		self::$cache['classAliases'] = TestReflection::getValue('JLoader', 'classAliases');
		self::$cache['classAliasesInverse'] = TestReflection::getValue('JLoader', 'classAliasesInverse');
	}

	/**
	 * Restore the JLoader cache settings after testing the class.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public static function tearDownAfterClass()
	{
		JLoader::setup();
		TestReflection::setValue('JLoader', 'classes', self::$cache['classes']);
		TestReflection::setValue('JLoader', 'imported', self::$cache['imported']);
		TestReflection::setValue('JLoader', 'prefixes', self::$cache['prefixes']);
		TestReflection::setValue('JLoader', 'namespaces', self::$cache['namespaces']);
		TestReflection::setValue('JLoader', 'classAliases', self::$cache['classAliases']);
		TestReflection::setValue('JLoader', 'classAliasesInverse', self::$cache['classAliasesInverse']);
	}

	/**
	 * The test cases for importing classes
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	public function casesImport()
	{
		return array(
			'fred.factory' => array('fred.factory', null, null, false, 'fred.factory does not exist', true),
			'bogus' => array('bogusload', JPATH_TEST_STUBS, '', true, 'bogusload.php should load properly', false),
			'class.loader' => array('cms.class.loader', null, '', true, 'class loader should load properly', true));
	}

	/**
	 * The test cases for jimport-ing classes
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	public function casesJimport()
	{
		return array(
			'fred.factory' => array('fred.factory', false, 'fred.factory does not exist'),
			'classloader' => array('cms.class.loader', true, 'JClassLoader should load properly'));
	}

	/**
	 * Tests the JLoader::discover method.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * Tests the JLoader::register method with an override of an alias.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function testLoadOverrideAliasClass()
	{
		// Normally register the class
		JLoader::register('AliasNewClass', JPATH_TEST_STUBS . '/loaderoverride/aliasnewclass.php');

		// Register the alias
		JLoader::registerAlias('AliasOldClass', 'AliasNewClass');

		// Register an override for the alias class
		JLoader::register('AliasOldClass', JPATH_TEST_STUBS . '/loaderoverride/aliasoverrideclass.php');

		// Check if the classes do exist
		$this->assertTrue(class_exists('AliasNewClass'));
		$this->assertTrue(class_exists('AliasOldClass'));

		$newClass = new AliasNewClass;
		$oldClass = new AliasOldClass;

		// Check if really the override is used
		$this->assertEquals('Alias Override Class', $newClass->getName());
		$this->assertEquals('Alias Override Class', $oldClass->getName());
	}

	/**
	 * Tests the JLoader::register method with an override of the original class.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function testLoadOverrideOriginalClass()
	{
		// Normally register the class
		JLoader::register('OriginalNewClass', JPATH_TEST_STUBS . '/loaderoverride/originalnewclass.php');

		// Register the alias
		JLoader::registerAlias('OriginalOldClass', 'OriginalNewClass');

		// Register an override for the alias class
		JLoader::register('OriginalNewClass', JPATH_TEST_STUBS . '/loaderoverride/originaloverrideclass.php');

		// Check if the classes do exist
		$this->assertTrue(class_exists('OriginalNewClass'));
		$this->assertTrue(class_exists('OriginalOldClass'));

		$newClass = new OriginalNewClass;
		$oldClass = new OriginalOldClass;

		// Check if really the override is used
		$this->assertEquals('Original Override Class', $newClass->getName());
		$this->assertEquals('Original Override Class', $oldClass->getName());
	}

	/**
	 * Tests the JLoader::loadByPsr4 method.
	 *
	 * @return  void
	 *
	 * @since   3.8.3
	 */
	public function testLoadByPsr4()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

		// Register namespace at first. Odd leading and trailing backslashes must be automatically removed from namespace
		JLoader::registerNamespace('\\DummyNamespace\\', JPATH_TEST_STUBS . '/DummyNamespace', $reset = true, $prepend = false, $type = 'psr4');

		$this->assertThat(JLoader::loadByPsr4('DummyNamespace\DummyClass'), $this->isTrue(), 'Tests that the class file was loaded.');
	}

	/**
	 * Tests the JLoader::loadByPsr4 method does not allow loading a file outside the namespace root.
	 *
	 * @return  void
	 *
	 * @since   3.8.9
	 */
	public function testLoadByPsr4DisallowsSnoopingOutsideRoot()
	{
		$this->assertThat(JLoader::loadByPsr4('Joomla\\CMS\\../cms'), $this->isFalse(), 'Tests that the class file was not loaded.');
	}

	/**
	 * Tests the JLoader::loadByPsr0 method does not allow loading a file outside the namespace root.
	 *
	 * @return  void
	 *
	 * @since   3.8.9
	 */
	public function testLoadByPsr0DisallowsSnoopingOutsideRoot()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

		$path = JPATH_TEST_STUBS . '/discover2';
		JLoader::registerNamespace('discover', $path);

		$this->assertThat(JLoader::loadByPsr0('discover3\\../../FormInspectors'), $this->isFalse(), 'Tests that the class file was not loaded.');
	}

	/**
	 * Tests the JLoader::_autoload method does not allow loading a file outside the path root.
	 *
	 * @return  void
	 *
	 * @since   3.8.9
	 */
	public function testAutoloadDisallowsSnoopingOutsideRoot()
	{
		$this->assertThat(JLoader::_autoload('JClass/../../Import'), $this->isFalse(), 'Tests that the class file was not loaded.');

		// This scenario is skipped because there is a conundrum validating we doesn't traverse out of the libraries/cms directory to include libraries/cms.php
		if (false)
		{
			$this->assertThat(JLoader::_autoload('JClass/../../Cms'), $this->isFalse(), 'Tests that the class file was not loaded.');
		}
	}

	/**
	 * Tests the JLoader::registerAlias method if the alias is loaded when the original class is loaded.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function testAliasInstanceOf()
	{
		// Normally register the class
		JLoader::register('JLoaderAliasStub', JPATH_TEST_STUBS . '/loaderoveralias/jloaderaliasstub.php');

		// Register the alias
		JLoader::registerAlias('JLoaderAliasStubAlias', 'JLoaderAliasStub');

		$object = new JLoaderAliasStub;

		$this->assertTrue(
			$object instanceof JLoaderAliasStubAlias
		);
	}

	/**
	 * Tests the JLoader::registerAlias method if the alias works ignoring cases
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function testAliasIgnoreCase()
	{
		// Normally register the class
		JLoader::register('JLoaderAliasStub', JPATH_TEST_STUBS . '/loaderoveralias/jloaderaliasstub.php');

		// Register the alias
		JLoader::registerAlias('CASEinsensitiveALIAS', 'JLoaderAliasStub');

		$this->assertTrue(class_exists('caseINSENSITIVEalias'));
	}

	/**
	 * Tests the JLoader::load method.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   3.1.4
	 */
	public function testRegisterNamespace()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

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
	 * Tests the JLoader::registerNamespace method when resetting the paths.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testRegisterNamespaceResetPath()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

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
	 * @since   3.1.4
	 * @expectedException  RuntimeException
	 */
	public function testRegisterNamespaceException()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

		JLoader::registerNamespace('Color', 'dummy');
	}

	/**
	 * Tests the JLoader::registerNamespace method for namespace trimming of leading and trailing backslashes.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testRegisterNamespaceTrimming()
	{
		// Clear the namespaces array for this test
		TestReflection::setValue('JLoader', 'namespaces', array('psr0' => array(), 'psr4' => array()));

		// Try registering namespace with leading backslash.
		$path = JPATH_TEST_STUBS . '/discover1';
		JLoader::registerNamespace('\\discover1', $path);

		$namespaces = JLoader::getNamespaces();

		$this->assertContains($path, $namespaces['discover1']);

		// Try registering namespace with trailing backslash.
		$path = JPATH_TEST_STUBS . '/discover2';
		JLoader::registerNamespace('discover2\\', $path);

		$namespaces = JLoader::getNamespaces();

		$this->assertContains($path, $namespaces['discover2']);
	}

	/**
	 * Tests the JLoader::registerPrefix method.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
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
	 * @since   3.0.0
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
	 * @since   3.1.4
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
		$foundLoadByPsr4 = false;
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

				if ($loader[1] === 'loadByPsr4')
				{
					$foundLoadByPsr4 = true;
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

		// Assert the PSR-4 loader is found.
		$this->assertTrue($foundLoadByPsr4);

		// Assert the Alias loader is found.
		$this->assertTrue($foundLoadByAlias);
	}

	/**
	 * Tests the JLoader::setup method with $enableClasses = false.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
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
	 * @since   3.1.4
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
	 * @since   3.1.4
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
		$foundLoadPsr4 = false;
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

				if ($loader[1] === 'loadByPsr4')
				{
					$foundLoadPsr4 = true;
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
		$this->assertTrue($foundLoadPsr4);

		// We expect to find it.
		$this->assertTrue($foundLoadAlias);
	}

	/**
	 * A function to unregister the Joomla auto loaders.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
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
				|| $loader[1] === 'loadByPsr4'
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
	 * @since   1.7.0
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
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->bogusPath, $this->bogusFullPath);
	}
}
