<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstallerManifestPackage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerManifestPackageTest extends TestCase
{
	/**
	 * @var JInstallerManifestPackage
	 */
	protected $object;

	/**
	 * Test...
	 *
	 * @covers  JInstallerManifestPackage::__construct
	 * @covers  JInstallerManifest::loadManifestFromXML
	 * @covers  JInstallerManifestPackage::loadManifestFromData
	 *
	 * @return void
	 */
	public function testLoadManifestFromData()
	{
		$this->object = new JInstallerManifestPackage(dirname(__DIR__) . '/data/pkg_joomla.xml');

		$this->assertEquals(
			'PKG_JOOMLA',
			$this->object->name
		);

		$this->assertEquals(
			'joomla',
			$this->object->packagename
		);

		$this->assertEquals(
			'joomla',
			$this->object->packageName
		);

		$this->assertEquals(
			'2.5.0',
			$this->object->version
		);

		$this->assertEquals(
			'PKG_JOOMLA_XML_DESCRIPTION',
			$this->object->description
		);

		$this->assertEquals(
			'Joomla!',
			$this->object->packager
		);

		$this->assertEquals(
			'https://www.joomla.org',
			$this->object->packagerurl
		);

		$this->assertEquals(
			'https://www.joomla.org',
			$this->object->packagerURL
		);

		$this->assertEquals(
			'http://update.joomla.org/packages/joomla',
			$this->object->update
		);

		$this->assertEquals(
			'',
			$this->object->scriptfile
		);

		$this->assertEquals(
			'',
			$this->object->scriptFile
		);

		$this->assertEquals(
			array(),
			$this->object->filelist
		);

		$this->assertEquals(
			array(),
			$this->object->fileList
		);
	}
}
