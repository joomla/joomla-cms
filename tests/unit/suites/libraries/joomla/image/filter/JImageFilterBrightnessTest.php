<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       2.5.0
 */
class JImageFilterBrightnessTest extends TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
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
	 * Tests the JImageFilterBrightness::execute method.
	 *
	 * This tests to make sure we can brighten the image.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function testExecute()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Define red.
		$red = imagecolorallocate($imageHandle, 127, 0, 0);

		// Draw a red rectangle to fill the image.
		imagefilledrectangle($imageHandle, 0, 0, 100, 100, $red);

		$filter = new JImageFilterBrightness($imageHandle);

		$filter->execute(array(IMG_FILTER_BRIGHTNESS => 10));

		$this->assertEquals(
			137,
			imagecolorat($imageHandle, 50, 50) >> 16 & 0xFF
		);
	}

	/**
	 * Tests the JImageFilterBrightness::execute method - invalid argument.
	 *
	 * This tests to make sure an exception is properly thrown.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
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

		$filter = new JImageFilterBrightness($imageHandle);

		$filter->execute(array());
	}
}
