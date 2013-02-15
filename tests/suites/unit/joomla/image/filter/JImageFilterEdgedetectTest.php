<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/image/filter.php';
require_once JPATH_PLATFORM . '/joomla/image/filter/edgedetect.php';

/**
 * Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       11.4
 */
class JImageFilterEdgedetectTest extends TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function setUp()
	{
		parent::setUp();

		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping JImage tests.');
		}
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.4
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Tests the JImageFilterContrast::execute method.
	 *
	 * This tests to make sure we can brighten the image.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testExecute()
	{
		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Define red.
		$dark = imagecolorallocate($imageHandle, 90, 90, 90);
		$light = imagecolorallocate($imageHandle, 120, 120, 120);

		imagefilledrectangle($imageHandle, 0, 0, 50, 99, $dark);
		imagefilledrectangle($imageHandle, 51, 0, 99, 99, $light);

		$filter = new JImageFilterEdgedetect($imageHandle);

		$filter->execute(array());

		$this->assertEquals(
			187,
			imagecolorat($imageHandle, 51, 25) >> 16 & 0xFF
		);
	}
}
