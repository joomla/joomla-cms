<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstallerExtension.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerExtensionTest extends TestCase
{
	/**
	 * Tests the class constructor with a package extension
	 *
	 * @since   3.1
	 *
	 * @return  void
	 */
	public function test__constructPackage()
	{
		$xml = simplexml_load_file(__DIR__ . '/data/pkg_joomla.xml');

		$this->assertThat(
			new JInstallerExtension($xml),
			$this->isInstanceOf('JInstallerExtension'),
			'Instantiating JInstallerExtension failed'
		);
	}

	/**
	 * Tests the class constructor with a module extension
	 *
	 * @since   3.1
	 *
	 * @return  void
	 */
	public function test__constructModule()
	{
		$xml = simplexml_load_file(__DIR__ . '/data/mod_finder.xml');

		$this->assertThat(
			new JInstallerExtension($xml),
			$this->isInstanceOf('JInstallerExtension'),
			'Instantiating JInstallerExtension failed'
		);
	}
}
