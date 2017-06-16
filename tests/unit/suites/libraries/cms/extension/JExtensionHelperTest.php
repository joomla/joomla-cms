<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  ExtensionHelper
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JExtensionHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  ExtensionHelper
 * @since       __DEPLOY_VERSION__
 */
class JExtensionHelperTest extends TestCase
{
	/**
	 * Tests the getCoreExtensions method
	 *
	 * @return  void
	 *
	 * @covers  JExtensionHelper::getCoreExtensions
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetCoreExtensions()
	{
		$extensions = JExtensionHelper::getCoreExtensions();

		$this->assertTrue(is_array($extensions), 'The function should return an array.');

		$this->assertFalse(
			in_array(array('blabla', 'com_admin', '', 1), $extensions),
			'Wrong type should not be in the array'
		);

		$this->assertFalse(
			in_array(array('component', 'com_blabla', '', 1), $extensions),
			'Wrong element name should not be in the array'
		);

		$this->assertFalse(
			in_array(array('component', 'com_admin', '', 0), $extensions),
			'Wrong client_id should not be in the array'
		);

		$this->assertFalse(
			in_array(array('component', 'com_admin', 'blabla', 1), $extensions),
			'Wrong folder should not be in the array'
		);

		$this->assertTrue(
			in_array(array('component', 'com_admin', '', 1), $extensions),
			'Component com_admin should be in the array'
		);

		$this->assertTrue(
			in_array(array('package', 'pkg_en-GB', '', 0), $extensions),
			'Package pkg_en-GB should be in the array'
		);
	}

	/**
	 * Tests the checkIfCoreExtension method
	 *
	 * @return  void
	 *
	 * @covers  JExtensionHelper::checkIfCoreExtension
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCheckIfCoreExtension()
	{
		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('blabla', 'com_mailto'),
			'Wrong type with 2 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('blabla', 'com_mailto', 0),
			'Wrong type with 3 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('blabla', 'com_mailto', 0, ''),
			'Wrong type with 4 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_blabla'),
			'Wrong element name with 2 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_blabla', 0),
			'Wrong element name with 3 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_blabla', 0, ''),
			'Wrong element name with 4 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_admin'),
			'Wrong client_id default value with 2 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_admin', 0),
			'Wrong client_id with 3 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('component', 'com_admin', 0, ''),
			'Wrong client_id with 4 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('plugin', 'languagefilter'),
			'Wrong folder default value with 2 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('plugin', 'languagefilter', 0),
			'Wrong folder default value with 3 params should return false'
		);

		$this->assertFalse(
			JExtensionHelper::checkIfCoreExtension('plugin', 'languagefilter', 0, 'blabla'),
			'Wrong folder with 4 params should return false'
		);

		$this->assertTrue(
			JExtensionHelper::checkIfCoreExtension('component', 'com_admin', 1),
			'com_admin 3 params correct should return true'
		);

		$this->assertTrue(
			JExtensionHelper::checkIfCoreExtension('component', 'com_admin', 1, ''),
			'com_admin 4 params correct should return true'
		);

		$this->assertTrue(
			JExtensionHelper::checkIfCoreExtension('package', 'pkg_en-GB'),
			'Package en-GB 2 params correct should return true'
		);

		$this->assertTrue(
			JExtensionHelper::checkIfCoreExtension('package', 'pkg_en-GB', 0),
			'Package en-GB 3 params correct should return true'
		);

		$this->assertTrue(
			JExtensionHelper::checkIfCoreExtension('package', 'pkg_en-GB', 0, ''),
			'Package en-GB 4 params correct should return true'
		);
	}
}
