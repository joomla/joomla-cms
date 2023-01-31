<?php

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image;

use Joomla\CMS\Image\Image;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  4.0.0
 */
class ImageTest extends UnitTestCase
{
    /**
     * @var    Image  The testing instance.
     *
     * @since  4.0.0
     */
    protected $instance;

    /**
     * @var    string  The testing jpg image unique name.
     *
     * @since  4.0.0
     */
    protected $testFile;

    /**
     * @var    string  The testing gif image unique name.
     *
     * @since  4.0.0
     */
    protected $testFileGif;

    /**
     * @var    string  The testing png image unique name.
     *
     * @since  4.0.0
     */
    protected $testFilePng;

    /**
     * @var    string  The testing bmp image unique name.
     *
     * @since  4.0.0
     */
    protected $testFileBmp;

    /**
     * @var    string  The testing webp image unique name.
     *
     * @since  4.0.0
     */
    protected $testFileWebp;

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
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('No GD support so skipping Image tests.');
        }

        $this->instance = new Image();

        $randFile = __DIR__ . '/tmp/koala-' . rand();

        // 500*341 resolution
        $this->testFile = $randFile . '.jpg';
        copy(__DIR__ . '/stubs/koala.jpg', $this->testFile);

        $this->testFileGif = $randFile . '.gif';
        copy(__DIR__ . '/stubs/koala.gif', $this->testFileGif);

        $this->testFilePng = $randFile . '.png';
        copy(__DIR__ . '/stubs/koala.png', $this->testFilePng);

        $this->testFileBmp = $randFile . '.bmp';
        copy(__DIR__ . '/stubs/koala.bmp', $this->testFileBmp);

        $this->testFileWebp = $randFile . '.webp';
        copy(__DIR__ . '/stubs/koala.webp', $this->testFileWebp);
    }

    /**
     * This method is called after a test is executed.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function tearDown(): void
    {
        unlink($this->testFile);
        unlink($this->testFileGif);
        unlink($this->testFilePng);
        unlink($this->testFileBmp);
        unlink($this->testFileWebp);

        parent::tearDown();
    }

    /**
     * Data for Joomla\CMS\Image\Image::prepareDimensions method.
     *
     * Don't put percentages in here.  We test elsewhere that percentages get sanitized into
     * appropriate integer values based on scale. Here we just want to test the logic that
     * calculates scale dimensions.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getPrepareDimensionsData()
    {
        return [
            // Note: inputHeight, inputWidth, inputScale, imageHeight, imageWidth, expectedHeight, expectedWidth
            [43, 56, Image::SCALE_FILL, 100, 100, 43, 56],
            [33, 56, Image::SCALE_FILL, 10, 10, 33, 56],
            [24, 76, Image::SCALE_INSIDE, 100, 100, 24, 24],
            [44, 80, Image::SCALE_OUTSIDE, 100, 50, 160, 80],
            [24, 80, Image::SCALE_OUTSIDE, 100, 50, 160, 80],
            [33, 50, Image::SCALE_INSIDE, 20, 100, 10, 50],
            [12, 50, Image::SCALE_INSIDE, 20, 100, 10, 50],
        ];
    }

    /**
     * Data for sanitizeDimension methods.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getSanitizeDimensionData()
    {
        return [
            // Note: inputHeight, inputWidth, imageHeight, imageWidth, expectedHeight, expectedWidth
            [42.5, 56.2, 10, 10, 43, 56],
            [33, 56.2, 10, 10, 33, 56],
            ['40%', 56.2, 10, 10, 4, 56],
            [42.5, '5%', 10, 10, 43, 1],
            ['33%', '25%', 10, 10, 3, 3],
            ['40%', null, 10, 10, 4, 4],
        ];
    }

    /**
     * Data for Joomla\CMS\Image\Image::crop method
     *
     * Don't put percentages in here.  We test elsewhere that percentages get
     * sanitized into appropriate integer values based on scale.
     * Here we just want to test the logic that actually crops the image.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getCropData()
    {
        return [
            // Note: startHeight, startWidth, cropHeight, cropWidth, cropTop, cropLeft, transparency
            [100, 100, 10, 10, 25, 25, false],
            [100, 100, 25, 25, 40, 31, true],
            [225, 432, 45, 11, 123, 12, true],
            [100, 100, 10, 10, null, 25, false],
            [100, 100, 10, 10, 25, null, false],
        ];
    }

    /**
     * Data for sanitizeOffset method.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getSanitizeOffsetData()
    {
        return [
            // Note: input, expected
            [42.5, 43],
            [56.2, 56],
        ];
    }

    /**
     * Test the Joomla\CMS\Image\Image::__construct method.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::__construct
     *
     * @since   4.0.0
     */
    public function testConstructor()
    {
        // Create a 10x10 image handle.
        $testImageHandle = imagecreatetruecolor(10, 10);

        // Create a new Image object from the handle.
        $testImage = new Image($testImageHandle);

        // Verify that the handle created is the same one in the Image.
        $this->assertSame($testImageHandle, TestHelper::getValue($testImage, 'handle'));

        // Create a new Image with no handle.
        $testImage2 = new Image();

        // Verify that there is no handle in the Image.
        $this->assertNull(TestHelper::getValue($testImage2, 'handle'));
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure image files are loaded correctly
     *
     * In this case we are taking the simple approach of loading an image file
     * and asserting that the dimensions are correct.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     *
     * @since   4.0.0
     */
    public function testloadFile()
    {
        $this->instance->loadFile($this->testFile);

        // Verify that the cropped image is the correct size.
        $this->assertEquals(341, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->assertEquals($this->testFile, $this->instance->getPath());
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure GIF images are loaded correctly
     *
     * In this case we are taking the simple approach of loading an image file
     * and asserting that the dimensions are correct.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     *
     * @since   4.0.0
     */
    public function testloadFileGif()
    {
        $this->instance->loadFile($this->testFileGif);

        // Verify that the cropped image is the correct size.
        $this->assertEquals(341, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->assertEquals($this->testFileGif, $this->instance->getPath());
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure PNG images are loaded properly.
     *
     * In this case we are taking the simple approach of loading an image file
     * and asserting that the dimensions are correct.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     *
     * @since   4.0.0
     */
    public function testloadFilePng()
    {
        $this->instance->loadFile($this->testFilePng);

        // Verify that the cropped image is the correct size.
        $this->assertEquals(341, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->assertEquals($this->testFilePng, $this->instance->getPath());
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure WebP images are loaded correctly
     *
     * In this case we are taking the simple approach of loading an image file
     * and asserting that the dimensions are correct.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     *
     * @since   4.0.0
     */
    public function testloadFileWebp()
    {
        $this->instance->loadFile($this->testFileWebp);

        // Verify that the cropped image is the correct size.
        $this->assertEquals(341, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->assertEquals($this->testFileWebp, $this->instance->getPath());
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure BMP images are not loaded properly.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     * @since   4.0.0
     */
    public function testloadFileBmp()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->loadFile($this->testFileBmp);
    }

    /**
     * Test the Joomla\CMS\Image\Image::loadFile method
     *
     * Makes sure if a bogus image is given it throws an exception.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::loadFile
     * @since   4.0.0
     */
    public function testloadFileWithInvalidFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->loadFile('bogus_file');
    }

    /**
     * Test the Joomla\CMS\Image\Image::resize method
     *
     * Makes sure images are resized properly.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::resize
     *
     * @since   4.0.0
     */
    public function testResize()
    {
        // Get a new Image inspector.
        $this->instance->loadFile($this->testFile);

        $this->instance->resize(1000, 682, false);

        // Verify that the resized image is the correct size.
        $this->assertEquals(682, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(1000, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->instance->resize(1000, 682, false, Image::SCALE_FIT);

        // Verify that the resized image is the correct size.
        $this->assertEquals(682, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(1000, imagesx(TestHelper::getValue($this->instance, 'handle')));
    }

    /**
     * Test the Joomla\CMS\Image\Image::resize method
     *
     * Make sure images are resized properly and
     * transparency is properly set.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::resize
     *
     * @since   4.0.0
     */
    public function testResizeTransparent()
    {
        // Create a 10x10 image handle.
        $transparentImage = imagecreatetruecolor(10, 10);

        // Set black to be transparent in the image.
        imagecolortransparent($transparentImage, imagecolorallocate($transparentImage, 0, 0, 0));

        $image = new Image($transparentImage);

        $image->resize(5, 5, false);

        // Verify that the resized image is the correct size.
        $this->assertEquals(5, imagesy(TestHelper::getValue($image, 'handle')));
        $this->assertEquals(5, imagesx(TestHelper::getValue($image, 'handle')));

        $this->assertTrue($image->isTransparent());
    }

    /**
     * Test the Joomla\CMS\Image\Image::resize method
     *
     * Make sure images are resized properly - no file loaded.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::resize
     *
     * @since   4.0.0
     */
    public function testResizeNoFile()
    {
        $this->expectException(\LogicException::class);

        $this->instance->resize(1000, 682, false);
    }

    /**
     * Test the Image::resize to make sure images are resized properly.
     *
     * @return  void
     *
     * @since   1.1.3
     */
    public function testCropResize()
    {
        $this->instance->loadFile($this->testFile);

        $this->instance->cropResize(500 * 2, 341 * 2, false);

        // Verify that the cropped resized image is the correct size.
        $this->assertEquals(341 * 2, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500 * 2, imagesx(TestHelper::getValue($this->instance, 'handle')));

        $this->instance->cropResize(500 * 3, 341 * 2, false);

        // Verify that the cropped resized image is the correct size.
        $this->assertEquals(341 * 2, imagesy(TestHelper::getValue($this->instance, 'handle')));
        $this->assertEquals(500 * 3, imagesx(TestHelper::getValue($this->instance, 'handle')));
    }

    /**
     * Test the Image::toFile when there is no image loaded.  This should throw a LogicException
     * since we cannot write an image out to file that we don't even have yet.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFileInvalid()
    {
        $this->expectException(\LogicException::class);

        $outFileGif = __DIR__ . '/tmp/out.gif';

        $this->instance->toFile($outFileGif, IMAGETYPE_GIF);
    }

    /**
     * Test the Joomla\CMS\Image\Image::toFile method
     *
     * Makes sure that a new image is properly written to file.
     *
     * When performing this test using a lossy compression we are not able
     * to open and save the same image and then compare the checksums as the checksums
     * may have changed. Therefore we are limited to comparing the image properties.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFileGif()
    {
        $outFileGif = __DIR__ . '/tmp/out-' . rand() . '.gif';

        $image = new Image($this->testFile);
        $image->toFile($outFileGif, IMAGETYPE_GIF);

        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFileGif);

        // Assert that properties that should be equal are equal.
        $this->assertEquals($a->width, $b->width);
        $this->assertEquals($a->height, $b->height);
        $this->assertEquals($a->attributes, $b->attributes);
        $this->assertEquals($a->bits, $b->bits);
        $this->assertEquals($a->channels, $b->channels);

        // Assert that the properties that should be different are different.
        $this->assertEquals('image/gif', $b->mime);
        $this->assertEquals(IMAGETYPE_GIF, $b->type);

        // Clean up after ourselves.
        unlink($outFileGif);
    }

    /**
     * Test the Joomla\CMS\Image\Image::toFile method
     *
     * Make sure that a new image is properly written to file.
     *
     * When performing this test using a lossy compression we are not able
     * to open and save the same image and then compare the checksums as the checksums
     * may have changed. Therefore we are limited to comparing the image properties.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFilePng()
    {
        $outFilePng = __DIR__ . '/tmp/out-' . rand() . '.png';

        $image = new Image($this->testFile);
        $image->toFile($outFilePng, IMAGETYPE_PNG);

        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFilePng);

        // Assert that properties that should be equal are equal.
        $this->assertEquals($a->width, $b->width);
        $this->assertEquals($a->height, $b->height);
        $this->assertEquals($a->attributes, $b->attributes);
        $this->assertEquals($a->bits, $b->bits);

        // Assert that the properties that should be different are different.
        $this->assertEquals('image/png', $b->mime);
        $this->assertEquals(IMAGETYPE_PNG, $b->type);
        $this->assertNull($b->channels);

        // Clean up after ourselves.
        unlink($outFilePng);
    }

    /**
     * Test the Joomla\CMS\Image\Image::toFile method
     *
     * Makes sure that a new image is properly written to file.
     *
     * When performing this test using a lossy compression we are not able
     * to open and save the same image and then compare the checksums as the checksums
     * may have changed. Therefore we are limited to comparing the image properties.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFileJpg()
    {
        // Write the file out to a JPG.
        $outFileJpg = __DIR__ . '/tmp/out-' . rand() . '.jpg';

        $image = new Image($this->testFile);
        $image->toFile($outFileJpg, IMAGETYPE_JPEG);

        // Get the file properties for both input and output.
        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFileJpg);

        // Assert that properties that should be equal are equal.
        $this->assertEquals($a->width, $b->width);
        $this->assertEquals($a->height, $b->height);
        $this->assertEquals($a->attributes, $b->attributes);
        $this->assertEquals($a->bits, $b->bits);
        $this->assertEquals($a->mime, $b->mime);
        $this->assertEquals($a->type, $b->type);
        $this->assertEquals($a->channels, $b->channels);

        // Clean up after ourselves.
        unlink($outFileJpg);
    }

    /**
     * Test the Joomla\CMS\Image\Image::toFile method
     *
     * Make sure that a new image is properly written to file.
     *
     * When performing this test using a lossy compression we are not able
     * to open and save the same image and then compare the checksums as the checksums
     * may have changed. Therefore we are limited to comparing the image properties.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFileWebp()
    {
        $outFileWebp = __DIR__ . '/tmp/out-' . rand() . '.webp';

        $image = new Image($this->testFile);
        $image->toFile($outFileWebp, IMAGETYPE_WEBP);

        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFileWebp);

        // Assert that properties that should be equal are equal.
        $this->assertEquals($a->width, $b->width);
        $this->assertEquals($a->height, $b->height);
        $this->assertEquals($a->attributes, $b->attributes);
        $this->assertEquals($a->bits, $b->bits);

        // Assert that properties that should be different are different.
        $this->assertEquals('image/webp', $b->mime);
        $this->assertEquals(IMAGETYPE_WEBP, $b->type);
        $this->assertNull($b->channels);

        // Clean up after ourselves.
        unlink($outFileWebp);
    }

    /**
     * Test the Joomla\CMS\Image\Image::toFile method
     *
     * Make sure that a new image is properly written to file.
     *
     * When performing this test using a lossy compression we are not able
     * to open and save the same image and then compare the checksums as the checksums
     * may have changed. Therefore we are limited to comparing the image properties.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::toFile
     *
     * @since   4.0.0
     */
    public function testToFileDefault()
    {
        // Write the file out to a JPG.
        $outFileDefault = __DIR__ . '/tmp/out-' . rand() . '.default';

        $image = new Image($this->testFile);
        $image->toFile($outFileDefault);

        // Get the file properties for both input and output.
        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFileDefault);

        // Assert that properties that should be equal are equal.
        $this->assertEquals($a->width, $b->width);
        $this->assertEquals($a->height, $b->height);
        $this->assertEquals($a->attributes, $b->attributes);
        $this->assertEquals($a->bits, $b->bits);
        $this->assertEquals($a->mime, $b->mime);
        $this->assertEquals($a->type, $b->type);
        $this->assertEquals($a->channels, $b->channels);

        // Clean up after ourselves.
        unlink($outFileDefault);
    }

    /**
     * Test the Joomla\CMS\Image\Image::getFilterInstance method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getFilterInstance
     *
     * @since   4.0.0
     */
    public function testGetFilterInstance()
    {
        // Create a new Image object.
        $image = new Image(imagecreatetruecolor(1, 1));

        // Get the filter instance.
        $filter = TestHelper::invoke($image, 'getFilterInstance', 'brightness');

        $this->assertInstanceOf('\\Joomla\\CMS\\Image\\Filter\\Brightness', $filter);
    }

    /**
     * Test the Joomla\CMS\Image\Image::getHeight method
     *
     * Make sure it gives the correct property from the source image.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getHeight
     *
     * @since   4.0.0
     */
    public function testGetHeight()
    {
        // Create a 108x42 image handle and add no transparency.
        $imageHandle = imagecreatetruecolor(108, 42);

        // Create a new Image object from the image handle.
        $image = new Image($imageHandle);

        $this->assertEquals(
            42,
            $image->getHeight()
        );
    }

    /**
     * Test the Joomla\CMS\Image\Image::getHeight method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getHeight
     *
     * @since   4.0.0
     */
    public function testGetHeightWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $this->instance->getHeight();
    }

    /**
     * Test the Joomla\CMS\Image\Image::getWidth method
     *
     * Make sure it gives the correct property from the source image
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getWidth
     *
     * @since   4.0.0
     */
    public function testGetWidth()
    {
        // Create a 108x42 image handle and add no transparency.
        $imageHandle = imagecreatetruecolor(108, 42);

        // Create a new Image object from the image handle.
        $image = new Image($imageHandle);

        $this->assertEquals(
            108,
            $image->getWidth()
        );
    }

    /**
     * Test the Joomla\CMS\Image\Image::getWidth method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getWidth
     *
     * @since   4.0.0
     */
    public function testGetWidthWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $this->instance->getWidth();
    }

    /**
     * Test the Joomla\CMS\Image\Image::getImageFileProperties method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::getImageFileProperties
     *
     * @since   4.0.0
     */
    public function testGetImageFilePropertiesWithInvalidFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        Image::getImageFileProperties(__DIR__ . '/suite/joomla/image/stubs/bogus.image');
    }

    /**
     * Test the Image::generateThumbs method without a loaded image.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::generateThumbs
     *
     * @since   1.1.3
     */
    public function testGenerateThumbsWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $thumbs = $this->instance->generateThumbs('50x38');
    }

    /**
     * Test the Image::generateThumbs method with invalid size.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::generateThumbs
     *
     * @since   1.1.3
     */
    public function testGenerateThumbsWithInvalidSize()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->loadFile($this->testFile);

        $thumbs = $this->instance->generateThumbs('50*38');
    }

    /**
     * Test the Image::generateThumbs method.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::generateThumbs
     *
     * @since   1.1.3
     */
    public function testGenerateThumbs()
    {
        $this->instance->loadFile($this->testFile);

        $thumbs = $this->instance->generateThumbs('50x38');

        // Verify that the resized image is the correct size.
        $this->assertEquals(
            34,
            imagesy(TestHelper::getValue($thumbs[0], 'handle'))
        );
        $this->assertEquals(
            50,
            imagesx(TestHelper::getValue($thumbs[0], 'handle'))
        );

        $thumbs = $this->instance->generateThumbs('50x38', Image::CROP);

        // Verify that the resized image is the correct size.
        $this->assertEquals(
            38,
            imagesy(TestHelper::getValue($thumbs[0], 'handle'))
        );
        $this->assertEquals(
            50,
            imagesx(TestHelper::getValue($thumbs[0], 'handle'))
        );

        $thumbs = $this->instance->generateThumbs('50x38', Image::CROP_RESIZE);

        // Verify that the resized image is the correct size.
        $this->assertEquals(
            38,
            imagesy(TestHelper::getValue($thumbs[0], 'handle'))
        );
        $this->assertEquals(
            50,
            imagesx(TestHelper::getValue($thumbs[0], 'handle'))
        );
    }

    /**
     * Test the Image::createThumbs method without a loaded image.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::createThumbs
     *
     * @since   1.1.3
     */
    public function testCreateThumbsWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $thumbs = $this->instance->createThumbs('50x38');
    }

    /**
     * Test the Image::generateThumbs method with invalid folder.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::createThumbs
     *
     * @since   1.1.3
     */
    public function testGenerateThumbsWithInvalidFolder()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->loadFile($this->testFile);
        $this->instance->createThumbs('50x38', Image::SCALE_INSIDE, '/foo/bar');
    }

    /**
     * Test the Image::createThumbs method.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::createThumbs
     *
     * @since   1.1.3
     */
    public function testCreateThumbs()
    {
        $this->instance->loadFile($this->testFile);

        $thumbs     = $this->instance->createThumbs('50x38', Image::CROP);
        $outFileGif = TestHelper::getValue($thumbs[0], 'path');

        $a = Image::getImageFileProperties($this->testFile);
        $b = Image::getImageFileProperties($outFileGif);

        // Assert that properties that should be equal are equal.
        $this->assertEquals(50, $b->width);
        $this->assertEquals(38, $b->height);
        $this->assertEquals($a->bits, $b->bits);
        $this->assertEquals($a->channels, $b->channels);
        $this->assertEquals($a->mime, $b->mime);
        $this->assertEquals($a->type, $b->type);
        $this->assertEquals($a->channels, $b->channels);

        unlink($outFileGif);
    }

    /**
     * Test the Image::isTransparent method without a loaded image.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::isTransparent
     *
     * @since   4.0.0
     */
    public function testIsTransparentWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $this->instance->isTransparent();
    }

    /**
     * Test the Joomla\CMS\Image\Image::isTransparent method
     *
     * Make sure it gives the correct result if the image has an alpha channel.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::isTransparent
     *
     * @since   4.0.0
     */
    public function testTransparentIsTransparent()
    {
        // Create a 10x10 image handle.
        $transparentImage = imagecreatetruecolor(10, 10);

        // Set black to be transparent in the image.
        imagecolortransparent($transparentImage, imagecolorallocate($transparentImage, 0, 0, 0));

        // Create a new Image object from the image handle.
        $transparent = new Image($transparentImage);

        // Assert that the image has transparency.
        $this->assertTrue(($transparent->isTransparent()));
    }

    /**
     * Test the Joomla\CMS\Image\Image::isTransparent method
     *
     * Make sure it gives the correct result if the image does not have an alpha channel.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::isTransparent
     *
     * @since   4.0.0
     */
    public function testOpaqueIsNotTransparent()
    {
        // Create a 10x10 image handle and add no transparency.
        $opaqueImage = imagecreatetruecolor(10, 10);

        // Create a new Image object from the image handle.
        $opaque = new Image($opaqueImage);

        // Assert that the image does not have transparency.
        $this->assertFalse(($opaque->isTransparent()));
    }

    /**
     * Test the Joomla\CMS\Image\Image::crop method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::crop
     *
     * @since   4.0.0
     */
    public function testCropWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        // Create a new Image object without loading an image.
        $image = new Image();

        $image->crop(10, 10, 5, 5);
    }

    /**
     * Tests the Joomla\CMS\Image\Image::crop() method
     *
     * To test this we create an image that contains a red rectangle of a certain size [Rectangle1].
     *
     * Inside of that rectangle [Rectangle1] we draw a white rectangle [Rectangle2] that is
     * exactly two pixels smaller in width and height than its parent rectangle [Rectangle1].
     * Then we crop the image to the exact coordinates of Rectangle1 and verify both it's
     * corners and the corners inside of it.
     *
     * @param   mixed    $startHeight  The original image height.
     * @param   mixed    $startWidth   The original image width.
     * @param   integer  $cropHeight   The height of the cropped image.
     * @param   integer  $cropWidth    The width of the cropped image.
     * @param   integer  $cropTop      The pixel offset from the top for the cropped image.
     * @param   integer  $cropLeft     The pixel offset from the left for the cropped image.
     * @param   boolean  $transparent  True to add transparency to the image.
     *
     * @return  void
     *
     * @dataProvider getCropData
     *
     * @covers  \Joomla\CMS\Image\Image::crop
     *
     * @since   4.0.0
     */
    public function testCrop($startHeight, $startWidth, $cropHeight, $cropWidth, $cropTop, $cropLeft, $transparent = false)
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor($startWidth, $startHeight);

        // If the transparent flag is true set black to transparent.
        if ($transparent) {
            imagecolortransparent($imageHandle, imagecolorallocate($imageHandle, 0, 0, 0));
        }

        // Define red and white.
        $red   = imagecolorallocate($imageHandle, 255, 0, 0);
        $white = imagecolorallocate($imageHandle, 255, 255, 255);

        $actualCropTop = $cropTop;

        if (is_null($cropTop)) {
            $cropTop = round(($startHeight - $cropHeight) / 2);
        }

        $actualCropLeft = $cropLeft;

        if (is_null($cropLeft)) {
            $cropLeft = round(($startWidth - $cropWidth) / 2);
        }

        // Draw a red rectangle in the crop area.
        imagefilledrectangle($imageHandle, $cropLeft, $cropTop, ($cropLeft + $cropWidth), ($cropTop + $cropHeight), $red);

        // Draw a white rectangle one pixel inside the crop area.
        imagefilledrectangle($imageHandle, ($cropLeft + 1), ($cropTop + 1), ($cropLeft + $cropWidth - 2), ($cropTop + $cropHeight - 2), $white);

        // Create a new Image from the image handle.
        $image = new Image($imageHandle);

        // Crop the image to specifications.
        $image->crop($cropWidth, $cropHeight, $actualCropLeft, $actualCropTop, false);

        // Verify that the cropped image is the correct size.
        $this->assertEquals(
            $cropHeight,
            imagesy(TestHelper::getValue($image, 'handle'))
        );
        $this->assertEquals(
            $cropWidth,
            imagesx(TestHelper::getValue($image, 'handle'))
        );

        // Validate the correct pixels for the corners.
        // Top/Left
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), 0, 0)
        );
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), 1, 1)
        );

        // Top/Right
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), 0, ($cropHeight - 1))
        );
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), 1, ($cropHeight - 2))
        );

        // Bottom/Left
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), ($cropWidth - 1), 0)
        );
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), ($cropWidth - 2), 1)
        );

        // Bottom/Right
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), ($cropWidth - 1), ($cropHeight - 1))
        );
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), ($cropWidth - 2), ($cropHeight - 2))
        );
    }

    /**
     * Test the Joomla\CMS\Image\Image::rotate method without a loaded image.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::rotate
     *
     * @since   4.0.0
     */
    public function testRotateWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        $this->instance->rotate(90);
    }

    /**
     * Tests the Joomla\CMS\Image\Image::rotate() method
     *
     * Create an image that contains a red horizontal line in the middle of the image,
     * and a white vertical line in the middle of the image.  Once the image is rotated 90 degrees
     * we test the end points of the lines to ensure that the colors have swapped.
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::rotate
     *
     * @since   4.0.0
     */
    public function testRotate()
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor(101, 101);

        // Define red and white.
        $red   = imagecolorallocate($imageHandle, 255, 0, 0);
        $white = imagecolorallocate($imageHandle, 255, 255, 255);

        // Draw a red horizontal line in the middle of the image.
        imageline($imageHandle, 5, 50, 95, 50, $red);

        // Draw a white vertical line in the middle of the image.
        imageline($imageHandle, 50, 5, 50, 95, $white);

        // Create a new Image from the image handle.
        $image = new Image($imageHandle);

        // Crop the image to specifications.
        $image->rotate(90, -1, false);

        // Validate the correct pixels for the ends of the lines.
        // Red line.
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), 50, 5)
        );
        $this->assertEquals(
            $red,
            imagecolorat(TestHelper::getValue($image, 'handle'), 50, 95)
        );

        // White line.
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), 5, 50)
        );
        $this->assertEquals(
            $white,
            imagecolorat(TestHelper::getValue($image, 'handle'), 95, 50)
        );
    }

    /**
     * Test the Joomla\CMS\Image\Image::filter
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::filter
     *
     * @since   4.0.0
     */
    public function testFilter()
    {
        $handle = imagecreatetruecolor(1, 1);

        // Create the mock filter.
        $mockFilter = $this->getMockForAbstractClass('\\Joomla\\CMS\\Image\\ImageFilter', [$handle], 'ImageFilterMock', true, false, true);

        // Setup the mock method call expectation.
        $mockFilter->expects($this->once())
            ->method('execute');

        // Create a new Image mock
        $mockImage = $this->getMockForAbstractClass('\\Joomla\\CMS\\Image\\Image', [$handle], 'ImageMock', true, false, true, ['getFilterInstance']);
        $mockImage->expects($this->once())
            ->method('getFilterInstance')
            ->willReturn($mockFilter);

        // Execute the filter.
        $mockImage->filter('mock');
    }

    /**
     * Test the Joomla\CMS\Image\Image::filter method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::filter
     *
     * @since   4.0.0
     */
    public function testFilterWithoutLoadedImage()
    {
        $this->expectException(\LogicException::class);

        // Create a new Image object without loading an image.
        $image = new Image();

        $image->filter('negate');
    }

    /**
     * Test the Joomla\CMS\Image\Image::filter method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::filter
     *
     * @since   4.0.0
     */
    public function testFilterWithInvalidFilterType()
    {
        $this->expectException(\RuntimeException::class);

        $image = new Image(imagecreatetruecolor(10, 10));

        $image->filter('foobar');
    }

    /**
     * Tests the Joomla\CMS\Image\Image::prepareDimensions method
     *
     * @param   mixed    $inputHeight     The height input.
     * @param   mixed    $inputWidth      The width input.
     * @param   integer  $inputScale      The scaling type.
     * @param   integer  $imageHeight     The original image height.
     * @param   integer  $imageWidth      The original image width.
     * @param   integer  $expectedHeight  The expected result image height.
     * @param   integer  $expectedWidth   The expected result image width.
     *
     * @return  void
     *
     * @dataProvider getPrepareDimensionsData
     *
     * @covers  \Joomla\CMS\Image\Image::prepareDimensions
     *
     * @since   4.0.0
     */
    public function testPrepareDimensions($inputHeight, $inputWidth, $inputScale, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

        // Create a new Image from the image handle.
        $image = new Image($imageHandle);

        $dimensions = TestHelper::invoke($image, 'prepareDimensions', $inputWidth, $inputHeight, $inputScale);

        // Validate the correct response.
        $this->assertEquals($expectedHeight, $dimensions->height);
        $this->assertEquals($expectedWidth, $dimensions->width);
    }

    /**
     * Tests the Joomla\CMS\Image\Image::prepareDimensions method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::prepareDimensions
     *
     * @since   4.0.0
     */
    public function testPrepareDimensionsWithInvalidScale()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor(100, 100);

        $image = new Image($imageHandle);

        $dimensions = TestHelper::invoke($image, 'prepareDimensions', 123, 456, 42);
    }

    /**
     * Tests the Joomla\CMS\Image\Image::sanitizeHeight method
     *
     * @param   mixed    $inputHeight     The height input.
     * @param   mixed    $inputWidth      The width input.
     * @param   integer  $imageHeight     The original image height.
     * @param   integer  $imageWidth      The original image width.
     * @param   integer  $expectedHeight  The expected result image height.
     * @param   integer  $expectedWidth   The expected result image width.
     *
     * @return  void
     *
     * @dataProvider getSanitizeDimensionData
     *
     * @covers  \Joomla\CMS\Image\Image::sanitizeHeight
     *
     * @since   4.0.0
     */
    public function testSanitizeHeight($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

        $image = new Image($imageHandle);

        // Validate the correct response.
        $this->assertEquals(
            $expectedHeight,
            TestHelper::invoke($image, 'sanitizeHeight', $inputHeight, $inputWidth)
        );
    }

    /**
     * Tests the Joomla\CMS\Image\Image::sanitizeWidth method
     *
     * @param   mixed    $inputHeight     The height input.
     * @param   mixed    $inputWidth      The width input.
     * @param   integer  $imageHeight     The original image height.
     * @param   integer  $imageWidth      The original image width.
     * @param   integer  $expectedHeight  The expected result image height.
     * @param   integer  $expectedWidth   The expected result image width.
     *
     * @return  void
     *
     * @dataProvider getSanitizeDimensionData
     *
     * @covers  \Joomla\CMS\Image\Image::sanitizeWidth
     *
     * @since   4.0.0
     */
    public function testSanitizeWidth($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
    {
        // Create a image handle of the correct size.
        $imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

        $image = new Image($imageHandle);

        // Validate the correct response.
        $this->assertEquals(
            $expectedWidth,
            TestHelper::invoke($image, 'sanitizeWidth', $inputWidth, $inputHeight)
        );
    }

    /**
     * Tests the Joomla\CMS\Image\Image::sanitizeOffset method
     *
     * @param   mixed    $input     The input offset.
     * @param   integer  $expected  The expected result offset.
     *
     * @return  void
     *
     * @dataProvider getSanitizeOffsetData
     *
     * @covers  \Joomla\CMS\Image\Image::sanitizeOffset
     *
     * @since   4.0.0
     */
    public function testSanitizeOffset($input, $expected)
    {
        // Validate the correct response.
        $this->assertEquals(
            $expected,
            TestHelper::invoke($this->instance, 'sanitizeOffset', $input)
        );
    }

    /**
     * Tests the Joomla\CMS\Image\Image::destroy method
     *
     * @return  void
     *
     * @covers  \Joomla\CMS\Image\Image::destroy
     *
     * @since   4.0.0
     */
    public function testDestroy()
    {
        // Create an image handle
        $imageHandle = imagecreatetruecolor(100, 100);

        // Pass created handle to Image
        $image = new Image($imageHandle);

        // Destroying the image should return boolean true
        $this->assertTrue($image->destroy());
    }
}
