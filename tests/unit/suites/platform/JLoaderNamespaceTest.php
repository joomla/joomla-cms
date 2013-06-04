<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Suites\Unit;

use JLoader;
use animal\Dog;
use Color\Blue;

/**
 * This is a complementary class to JLoaderTest for the namespace loaders.
 * To check the classes are correctly loaded from a given namespace with use or without use.
 *
 * @package  Joomla.UnitTest
 * @since    12.3
 */
class JLoaderNamespaceTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Container for JLoader static values during tests.
	 *
	 * @var    array
	 * @since  12.3
	 */
	protected static $cache = array();

	/**
	 * Cache the JLoader settings while we are resetting things for testing.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public static function setUpBeforeClass()
	{
		self::$cache['classes']    = \TestReflection::getValue('JLoader', 'classes');
		self::$cache['imported']   = \TestReflection::getValue('JLoader', 'imported');
		self::$cache['prefixes']   = \TestReflection::getValue('JLoader', 'prefixes');
		self::$cache['namespaces'] = \TestReflection::getValue('JLoader', 'namespaces');
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
		\TestReflection::setValue('JLoader', 'classes', self::$cache['classes']);
		\TestReflection::setValue('JLoader', 'imported', self::$cache['imported']);
		\TestReflection::setValue('JLoader', 'prefixes', self::$cache['prefixes']);
		\TestReflection::setValue('JLoader', 'namespaces', self::$cache['namespaces']);
	}

	/**
	 * Test the JLoader::loadByNamespaceLowerCase method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceLowerCase
	 */
	public function testLoadByNamespaceLowerCase()
	{
		// Make sure the loaders are unregistered.
		$this->unregisterLoaders();

		// Set up the loader with the lower case strategy.
		JLoader::setup(JLoader::LOWER_CASE, true, false, false);

		// Register the animal namespace where we can find the Cat class.
		$path = JPATH_TEST_STUBS . '/animal1';
		JLoader::registerNamespace('animal', $path);

		// Test with full namespace.
		$cat = new \animal\Cat;
		$this->assertEquals($cat->say(), 'hello');

		// Register the second namespace where we can find the Dog class.
		$path = JPATH_TEST_STUBS . '/animal2';
		JLoader::registerNamespace('animal', $path);

		// Test with use.
		$dog = new Dog;
		$this->assertEquals($dog->say(), 'hello');
	}

	/**
	 * Test the JLoader::loadByNamespaceLowerCase method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceLowerCase
	 */
	public function testLoadByNamespaceNaturalCase()
	{
		// Make sure the loaders are unregistered.
		$this->unregisterLoaders();

		// Set up the loader with the natural case strategy.
		JLoader::setup(JLoader::NATURAL_CASE, true, false, false);

		// Register the Color namespace where we can find the Red class.
		$path = JPATH_TEST_STUBS . '/Color';
		JLoader::registerNamespace('Color', $path);

		// Test with full namespace.
		$red = new \Color\Rgb\Red;
		$this->assertEquals($red->color(), 'red');

		// Register a second path for that namespace where we can find
		// the Blue class.
		$path = JPATH_TEST_STUBS . '/Color2';
		JLoader::registerNamespace('Color', $path);

		// Test with use.
		$blue = new Blue;
		$this->assertEquals($blue->color(), 'blue');
	}

	/**
	 * Test the JLoader::loadByNamespaceMixedCase method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @covers  JLoader::loadByNamespaceMixedCase
	 */
	public function testLoadByNamespaceMixedCase()
	{
		// Make sure the loaders are unregistered.
		$this->unregisterLoaders();

		// Set up the loader with the mixed case strategy.
		JLoader::setup(JLoader::MIXED_CASE, true, false, false);

		// Register the animal namespace where we can find the Cat class.
		$path = JPATH_TEST_STUBS . '/animal1';
		JLoader::registerNamespace('animal', $path);

		// Test with full namespace.
		$cat = new \animal\Cat;
		$this->assertEquals($cat->say(), 'hello');

		// Register a second path for that namespace where we can find
		// the Blue class.
		$path = JPATH_TEST_STUBS . '/Color2';
		JLoader::registerNamespace('Color', $path);

		// Test with use.
		$blue = new Blue;
		$this->assertEquals($blue->color(), 'blue');
	}

	/**
	 * This function unregisters the namespace loaders and reset the namespaces stack of JLoader.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function unregisterLoaders()
	{
		// Make sure no namespaces are registered.
		\TestReflection::setValue('JLoader', 'namespaces', array());

		// Get all auto load functions.
		$loaders = spl_autoload_functions();

		// Unregister the namespace auto loaders if any.
		foreach ($loaders as $loader)
		{
			if (is_array($loader) && $loader[0] === 'JLoader'
				&& ($loader[1] === 'loadByNamespaceLowerCase'
				|| $loader[1] === 'loadByNamespaceNaturalCase'
				|| $loader[1] === 'loadByNamespaceMixedCase'))
			{
				spl_autoload_unregister($loader);
			}
		}
	}
}
