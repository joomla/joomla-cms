<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       3.4
 */
class JImageFilterBackgroundfillTest extends TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setUp()
	{
		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping JImage tests.');
		}

		parent::setUp();
	}

	/**
	 * Tests the JImageFilterBackgroundfill::execute method.
	 *
	 * This tests to make sure we can fill background of the image.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 *
	 * @note    Because GD2 uses 7bit alpha channel, results differ slightly 
	 *          compared to 8bit systems like Adobe Photoshop. 
	 *          Example: GD: 171, 45, 45, Photoshop: 172, 45, 45
	 *
	 * @note    To test alpha, use imagecolorsforindex($imageHandle, $color);
	 */
	public function testExecute()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);
		imagealphablending($imageHandle, false);
		imagesavealpha($imageHandle, true);

		// Define semi-transparent gray areas.
		$dark = imagecolorallocatealpha($imageHandle, 90, 90, 90, 63);
		$light = imagecolorallocatealpha($imageHandle, 120, 120, 120, 63);

		imagefilledrectangle($imageHandle, 0, 0, 50, 99, $dark);
		imagefilledrectangle($imageHandle, 51, 0, 99, 99, $light);
		$filter = new JImageFilterBackgroundfill($imageHandle);
		$filter->execute(array('color' => '#ff0000'));

		// Compare left part
		$color = imagecolorat($imageHandle, 25, 25);
		$this->assertEquals(
			array(171, 45, 45),
			array($color >> 16 & 0xFF, $color >> 8 & 0xFF, $color & 0xFF)
		);

		// Compare right part
		$color = imagecolorat($imageHandle, 51, 25);
		$this->assertEquals(
			array(186, 60, 60), // GD
			array($color >> 16 & 0xFF, $color >> 8 & 0xFF, $color & 0xFF)
		);
	}

	/**
	 * Tests the JImageFilterBackgroundFill::execute method - invalid argument.
	 *
	 * This tests to make sure an exception is properly thrown.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testExecuteInvalidArgument()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Define red.
		$red = imagecolorallocate($imageHandle, 127, 0, 0);

		// Draw a red rectangle to fill the image.
		imagefilledrectangle($imageHandle, 0, 0, 100, 100, $red);

		$filter = new JImageFilterBackgroundfill($imageHandle);

		$filter->execute(array());
	}	
}
