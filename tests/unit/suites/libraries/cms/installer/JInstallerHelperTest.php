<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInstallerHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerHelperTest extends TestCase
{
	/**
	 * Tests the getFilenameFromURL method
	 *
	 * @since   3.1
	 *
	 * @return  void
	 */
	public function testGetFilenameFromUrl()
	{
		$this->assertThat(
			JInstallerHelper::getFilenameFromUrl('http://update.joomla.org/core/list.xml'),
			$this->equalTo('list.xml'),
			'JInstallerHelper::getFilenameFromURL should return the last portion of the URL'
		);
	}
}
