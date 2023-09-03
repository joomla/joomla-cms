<?php

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image\Filter;

use Joomla\CMS\Image\Filter\Backgroundfill as FilterBackgroundfill;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  4.0.0
 */
class FilterBackgroundfillTest extends UnitTestCase
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
     * Tests the ImageFilterBackgroundfill::execute method.
     *
     * This tests to make sure we can brighten the image.
     *
     * @return  void
     *
     * @covers   Joomla\CMS\Image\Filter\Backgroundfill::execute
     * @since  4.0.0
     *
     * @note     Because GD2 uses 7bit alpha channel, results differ slightly
     *           compared to 8bit systems like Adobe Photoshop.
     *           Example: GD: 171, 45, 45, Photoshop: 172, 45, 45
     *
     * @note     To test alpha, use imagecolorsforindex($imageHandle, $color);
     */
    public function testExecute()
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor(100, 100);
        imagealphablending($imageHandle, false);
        imagesavealpha($imageHandle, true);

        // Define semi-transparent gray areas.
        $dark  = imagecolorallocatealpha($imageHandle, 90, 90, 90, 63);
        $light = imagecolorallocatealpha($imageHandle, 120, 120, 120, 63);

        imagefilledrectangle($imageHandle, 0, 0, 50, 99, $dark);
        imagefilledrectangle($imageHandle, 51, 0, 99, 99, $light);
        $filter = new FilterBackgroundfill($imageHandle);
        $filter->execute(['color' => '#ff0000']);

        // Compare left part
        $color = imagecolorat($imageHandle, 25, 25);
        $this->assertEquals(
            [171, 45, 45],
            [$color >> 16 & 0xFF, $color >> 8 & 0xFF, $color & 0xFF]
        );

        // Compare right part
        $color = imagecolorat($imageHandle, 51, 25);
        $this->assertEquals(
            [186, 60, 60], // GD
            [$color >> 16 & 0xFF, $color >> 8 & 0xFF, $color & 0xFF]
        );
    }

    /**
     * Tests the ImageFilterBackgroundFill::execute method - invalid argument.
     *
     * This tests to make sure an exception is properly thrown.
     *
     * @return  void
     *
     * @covers  Joomla\CMS\Image\Filter\Backgroundfill::execute
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

        $filter = new FilterBackgroundfill($imageHandle);

        $filter->execute([]);
    }

    /**
     * Test data for the ImageFilterBackgroundFill::sanitizeColor method.
     *
     * @return  array
     *
     * @since   1.1.3
     */
    public function dataSanitizeColor()
    {
        return [
            [0, 0, 0, 0, 0],
            ["#000000", 0, 0, 0, 0],
            ["#FF0000", 255, 0, 0, 0],
            ["#FFFF00", 255, 255, 0, 0],
            ["#FFFFFF", 255, 255, 255, 0],
            ["#FFFFFFFF", 255, 255, 255, 0],
            ["#000000FF", 0, 0, 0, 0],
            ["#00000000", 0, 0, 0, 127],
            ["#000000AA", 0, 0, 0, 42],
            ["#000000AA", 0, 0, 0, 42],
            [
                [
                    'red'   => -5,
                    'green' => 0,
                    'blue'  => 300,
                    'alpha' => 300,
                ],
                0, 0, 255, 127,
            ],
        ];
    }

    /**
     * Tests the ImageFilterBackgroundFill::sanitizeColor method.
     *
     * @param   mixed  $color  Color in format of string '#aarrggbb' or as a array
     * @param   int    $red    Sanitized red color
     * @param   int    $green  Sanitized green color
     * @param   int    $blue   Sanitized blue color
     * @param   int    $alpha  Sanitized alpha color
     *
     * @return  void
     *
     * @covers         Joomla\CMS\Image\Filter\Backgroundfill::sanitizeColor
     * @dataProvider   dataSanitizeColor
     * @since          1.1.3
     */
    public function testSanitizeColor($color, $red, $green, $blue, $alpha)
    {
        $imageHandle = imagecreatetruecolor(100, 100);
        $filter      = new FilterBackgroundfill($imageHandle);

        $this->assertEquals(
            [
                'red'   => $red,
                'green' => $green,
                'blue'  => $blue,
                'alpha' => $alpha,
            ],
            TestHelper::invoke($filter, 'sanitizeColor', $color)
        );
    }
}
