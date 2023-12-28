<?php

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image;

use Joomla\CMS\Image\Filter\Brightness as FilterBrightness;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  4.0.0
 */
class ImageFilterTest extends UnitTestCase
{
    /**
     * Setup for testing.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Verify that GD support for PHP is available.
        if (!\extension_loaded('gd')) {
            $this->markTestSkipped('No GD support so skipping Image tests.');
        }
    }

    /**
     * Tests the Image::__construct method - with an invalid argument.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testConstructorInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        $filter = new FilterBrightness('test');
    }

    /**
     * Tests the Image::__construct method.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function testConstructor()
    {
        // Create an image handle of the correct size.
        $imageHandle = imagecreatetruecolor(100, 100);

        $filter = new FilterBrightness($imageHandle);

        $this->assertEquals(
            $imageHandle,
            TestHelper::getValue($filter, 'handle')
        );
    }
}
