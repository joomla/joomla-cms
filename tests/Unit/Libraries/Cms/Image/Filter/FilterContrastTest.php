<?php

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image\Filter;

use Joomla\CMS\Image\Filter\Contrast as FilterContrast;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  4.0.0
 */
class FilterContrastTest extends UnitTestCase
{
    /**
     * Setup for testing.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setup(): void
    {
        parent::setUp();

        // Verify that GD support for PHP is available.
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('No GD support so skipping Image tests.');
        }
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     * @since   4.0.0
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Tests the ImageFilterContrast::execute method.
     *
     * This tests to make sure we can brighten the image.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testExecute()
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor(100, 100);

        // Define red.
        $dark  = imagecolorallocate($imageHandle, 90, 90, 90);
        $light = imagecolorallocate($imageHandle, 120, 120, 120);

        imagefilledrectangle($imageHandle, 0, 0, 50, 99, $dark);
        imagefilledrectangle($imageHandle, 51, 0, 99, 99, $light);

        $filter = new FilterContrast($imageHandle);

        $filter->execute([IMG_FILTER_CONTRAST => -10]);

        $this->assertEquals(
            82,
            imagecolorat($imageHandle, 25, 25) >> 16 & 0xFF
        );
    }

    /**
     * Tests the ImageFilterBrightness::execute method - invalid argument.
     *
     * This tests to make sure an exception is properly thrown.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testExecuteInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor(100, 100);

        // Define red.
        $red = imagecolorallocate($imageHandle, 127, 0, 0);

        // Draw a red rectangle to fill the image.
        imagefilledrectangle($imageHandle, 0, 0, 100, 100, $red);

        $filter = new FilterContrast($imageHandle);

        $filter->execute([]);
    }
}
