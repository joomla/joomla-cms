<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
			JInstallerHelper::getFilenameFromUrl('https://update.joomla.org/core/list.xml'),
			$this->equalTo('list.xml'),
			'JInstallerHelper::getFilenameFromURL should return the last portion of the URL'
		);
	}
}
