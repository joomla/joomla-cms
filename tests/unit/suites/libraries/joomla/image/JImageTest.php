<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JImageInspector.php';
require_once __DIR__ . '/stubs/JImageFilterInspector.php';

/**
 * Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       11.3
 */
class JImageTest extends TestCase
{
	/**
	 * Test the JImage::getFilterInstance method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetFilterInstance()
	{
		// Create a new JImageInspector object.
		$image = new JImageInspector(imagecreatetruecolor(1, 1));

		// Get the filter instance.
		$filter = $image->getFilterInstance('inspector');

		$this->assertInstanceOf('JImageFilterInspector', $filter);
	}
}
