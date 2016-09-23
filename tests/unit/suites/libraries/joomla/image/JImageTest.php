<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function setUp()
	{
		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping JImage tests.');
		}

		parent::setUp();

		$this->testFile = __DIR__ . '/stubs/koala.jpg';

		$this->testFileGif = __DIR__ . '/stubs/koala.gif';

		$this->testFilePng = __DIR__ . '/stubs/koala.png';

		$this->testFileBmp = __DIR__ . '/stubs/koala.bmp';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->testFile);
		unset($this->testFileGif);
		unset($this->testFilePng);
		unset($this->testFileBmp);
		parent::tearDown();
	}

	/**
	 * Data for prepareDimensions method.  Don't put percentages in here.  We test elsewhere that
	 * percentages get sanitized into appropriate integer values based on scale.  Here we just want
	 * to test the logic that calculates scale dimensions.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getPrepareDimensionsData()
	{
		return array(
			// Note: inputHeight, inputWidth, inputScale, imageHeight, imageWidth, expectedHeight, expectedWidth
			array(43, 56, JImage::SCALE_FILL, 100, 100, 43, 56),
			array(33, 56, JImage::SCALE_FILL, 10, 10, 33, 56),
			array(24, 76, JImage::SCALE_INSIDE, 100, 100, 24, 24),
			array(44, 80, JImage::SCALE_OUTSIDE, 100, 50, 160, 80),
			array(24, 80, JImage::SCALE_OUTSIDE, 100, 50, 160, 80),
			array(33, 50, JImage::SCALE_INSIDE, 20, 100, 10, 50),
			array(12, 50, JImage::SCALE_INSIDE, 20, 100, 10, 50)
		);
	}

	/**
	 * Data for sanitizeDimension methods.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getSanitizeDimensionData()
	{
		return array(
			// Note: inputHeight, inputWidth, imageHeight, imageWidth, expectedHeight, expectedWidth
			array(42.5, 56.2, 10, 10, 43, 56),
			array(33, 56.2, 10, 10, 33, 56),
			array('40%', 56.2, 10, 10, 4, 56),
			array(42.5, '5%', 10, 10, 43, 1),
			array('33%', '25%', 10, 10, 3, 3),
			array('40%', null, 10, 10, 4, 4)
		);
	}

	/**
	 * Data for crop method.  Don't put percentages in here.  We test elsewhere that percentages get
	 * sanitized into appropriate integer values based on scale.  Here we just want to test the logic
	 * that actually crops the image.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getCropData()
	{
		return array(
			// Note: startHeight, startWidth, cropHeight, cropWidth, cropTop, cropLeft, transparency
			array(100, 100, 10, 10, 25, 25, false),
			array(100, 100, 25, 25, 40, 31, true),
			array(225, 432, 45, 11, 123, 12, true)
		);
	}

	/**
	 * Data for sanitizeOffset method.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getSanitizeOffsetData()
	{
		return array(
			// Note: input, expected
			array(42.5, 43),
			array(56.2, 56)
		);
	}

	/**
	 * Tests the JImage::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testConstructor()
	{
		// Create a 10x10 image handle.
		$testImageHandle = imagecreatetruecolor(10, 10);

		// Create a new JImageInspector object from the handle.
		$testImage = new JImageInspector($testImageHandle);

		// Verify that the handle created is the same one in the JImageInspector.
		$this->assertSame($testImageHandle, $testImage->getClassProperty('handle'));

		// Create a new JImageInspector with no handle.
		$testImage2 = new JImageInspector;

		// Verify that there is no handle in the JImageInspector.
		$this->assertNull($testImage2->getClassProperty('handle'));
	}

	/**
	 * Test the JImage::loadFile to makes sure images are loaded properly.  In this case we
	 * are taking the simple approach of loading an image file and asserting that the dimensions
	 * are correct.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testloadFile()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile($this->testFile);

		// Verify that the cropped image is the correct size.
		$this->assertEquals(341, imagesy($image->getClassProperty('handle')));
		$this->assertEquals(500, imagesx($image->getClassProperty('handle')));

		$this->assertEquals($this->testFile, $image->getPath());
	}

	/**
	 * Test the JImage::loadFile to makes sure GIF images are loaded properly.  In this case we
	 * are taking the simple approach of loading an image file and asserting that the dimensions
	 * are correct.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testloadFileGif()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile($this->testFileGif);

		// Verify that the cropped image is the correct size.
		$this->assertEquals(341, imagesy($image->getClassProperty('handle')));
		$this->assertEquals(500, imagesx($image->getClassProperty('handle')));

		$this->assertEquals($this->testFileGif, $image->getPath());
	}

	/**
	 * Test the JImage::loadFile to makes sure PNG images are loaded properly.  In this case we
	 * are taking the simple approach of loading an image file and asserting that the dimensions
	 * are correct.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testloadFilePng()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile($this->testFilePng);

		// Verify that the cropped image is the correct size.
		$this->assertEquals(341, imagesy($image->getClassProperty('handle')));
		$this->assertEquals(500, imagesx($image->getClassProperty('handle')));

		$this->assertEquals($this->testFilePng, $image->getPath());
	}

	/**
	 * Test the JImage::loadFile to makes sure XCF images are not loaded properly.  In this case we
	 * are taking the simple approach of loading an image file and asserting that the dimensions
	 * are correct.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testloadFileBmp()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile($this->testFileBmp);
	}

	/**
	 * Test the JImage::loadFile to makes sure if a bogus image is given it throws an exception.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since   11.3
	 */
	public function testloadFileWithInvalidFile()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile('bogus_file');
	}

	/**
	 * Test the JImage::resize to make sure images are resized properly.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testResize()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;
		$image->loadFile($this->testFile);

		$image->resize(1000, 682, false);

		// Verify that the resizeded image is the correct size.
		$this->assertEquals(682, imagesy($image->getClassProperty('handle')));
		$this->assertEquals(1000, imagesx($image->getClassProperty('handle')));
	}

	/**
	 * Test the JImage::resize to make sure images are resized properly and
	 * transparency is properly set.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testResizeTransparent()
	{
		// Create a 10x10 image handle.
		$transparentImage = imagecreatetruecolor(10, 10);

		// Set black to be transparent in the image.
		imagecolortransparent($transparentImage, imagecolorallocate($transparentImage, 0, 0, 0));

		$image = new JImageInspector($transparentImage);

		$image->resize(5, 5, false);

		// Verify that the resizeed image is the correct size.
		$this->assertEquals(5, imagesy($image->getClassProperty('handle')));
		$this->assertEquals(5, imagesx($image->getClassProperty('handle')));

		$this->assertTrue($image->isTransparent());
	}

	/**
	 * Test the JImage::resize to make sure images are resized properly - no file loaded.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 *
	 * @expectedException  LogicException
	 */
	public function testResizeNoFile()
	{
		// Get a new JImage inspector.
		$image = new JImageInspector;

		$image->resize(1000, 682, false);
	}

	/**
	 * Test the JImage::toFile when there is no image loaded.  This should throw a LogicException
	 * since we cannot write an image out to file that we don't even have yet.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testToFileInvalid()
	{
		$outFileGif = JPATH_TESTS . '/tmp/out.gif';

		$image = new JImageInspector;
		$image->toFile($outFileGif, IMAGETYPE_GIF);
	}

	/**
	 * Test the JImage::toFile to make sure that a new image is properly written
	 * to file, when performing this test using a lossy compression we are not able
	 * to open and save the same image and then compare the checksums as the checksums
	 * may have changed. Therefore we are limited to comparing the image properties.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testToFileGif()
	{
		$outFileGif = JPATH_TESTS . '/tmp/out.gif';

		$image = new JImageInspector($this->testFile);
		$image->toFile($outFileGif, IMAGETYPE_GIF);

		$a = JImage::getImageFileProperties($this->testFile);
		$b = JImage::getImageFileProperties($outFileGif);

		// Assert that properties that should be equal are equal.
		$this->assertTrue($a->width == $b->width);
		$this->assertTrue($a->height == $b->height);
		$this->assertTrue($a->attributes == $b->attributes);
		$this->assertTrue($a->bits == $b->bits);
		$this->assertTrue($a->channels == $b->channels);

		// Assert that the properties that should be different are different.
		$this->assertTrue($b->mime == 'image/gif');
		$this->assertTrue($b->type == IMAGETYPE_GIF);

		// Clean up after ourselves.
		unlink($outFileGif);
	}

	/**
	 * Test the JImage::toFile to make sure that a new image is properly written
	 * to file, when performing this test using a lossy compression we are not able
	 * to open and save the same image and then compare the checksums as the checksums
	 * may have changed. Therefore we are limited to comparing the image properties.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testToFilePng()
	{
		$outFilePng = JPATH_TESTS . '/tmp/out.png';

		$image = new JImageInspector($this->testFile);
		$image->toFile($outFilePng, IMAGETYPE_PNG);

		$a = JImage::getImageFileProperties($this->testFile);
		$b = JImage::getImageFileProperties($outFilePng);

		// Assert that properties that should be equal are equal.
		$this->assertTrue($a->width == $b->width);
		$this->assertTrue($a->height == $b->height);
		$this->assertTrue($a->attributes == $b->attributes);
		$this->assertTrue($a->bits == $b->bits);

		// Assert that the properties that should be different are different.
		$this->assertTrue($b->mime == 'image/png');
		$this->assertTrue($b->type == IMAGETYPE_PNG);
		$this->assertTrue($b->channels == null);

		// Clean up after ourselves.
		unlink($outFilePng);
	}

	/**
	 * Test the JImage::toFile to make sure that a new image is properly written
	 * to file, when performing this test using a lossy compression we are not able
	 * to open and save the same image and then compare the checksums as the checksums
	 * may have changed. Therefore we are limited to comparing the image properties.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testToFileJpg()
	{
		// Write the file out to a JPG.
		$outFileJpg = JPATH_TESTS . '/tmp/out.jpg';

		$image = new JImageInspector($this->testFile);
		$image->toFile($outFileJpg, IMAGETYPE_JPEG);

		// Get the file properties for both input and output.
		$a = JImage::getImageFileProperties($this->testFile);
		$b = JImage::getImageFileProperties($outFileJpg);

		// Assert that properties that should be equal are equal.
		$this->assertTrue($a->width == $b->width);
		$this->assertTrue($a->height == $b->height);
		$this->assertTrue($a->attributes == $b->attributes);
		$this->assertTrue($a->bits == $b->bits);
		$this->assertTrue($a->mime == $b->mime);
		$this->assertTrue($a->type == $b->type);
		$this->assertTrue($a->channels == $b->channels);

		// Clean up after ourselves.
		unlink($outFileJpg);
	}

	/**
	 * Test the JImage::toFile to make sure that a new image is properly written
	 * to file, when performing this test using a lossy compression we are not able
	 * to open and save the same image and then compare the checksums as the checksums
	 * may have changed. Therefore we are limited to comparing the image properties.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testToFileDefault()
	{
		// Write the file out to a JPG.
		$outFileDefault = JPATH_TESTS . '/tmp/out.default';

		$image = new JImageInspector($this->testFile);
		$image->toFile($outFileDefault);

		// Get the file properties for both input and output.
		$a = JImage::getImageFileProperties($this->testFile);
		$b = JImage::getImageFileProperties($outFileDefault);

		// Assert that properties that should be equal are equal.
		$this->assertTrue($a->width == $b->width);
		$this->assertTrue($a->height == $b->height);
		$this->assertTrue($a->attributes == $b->attributes);
		$this->assertTrue($a->bits == $b->bits);
		$this->assertTrue($a->mime == $b->mime);
		$this->assertTrue($a->type == $b->type);
		$this->assertTrue($a->channels == $b->channels);

		// Clean up after ourselves.
		unlink($outFileDefault);
	}

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

	/**
	 * Test the JImage::getHeight method to make sure it gives the correct
	 * property from the source image.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetHeight()
	{
		// Create a 108x42 image handle and add no transparency.
		$imageHandle = imagecreatetruecolor(108, 42);

		// Create a new JImageInspector object from the image handle.
		$image = new JImageInspector($imageHandle);

		$this->assertTrue(($image->getHeight() == 42), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JImage::getHeight method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testGetHeightWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->getHeight();
	}

	/**
	 * Test the JImage::getWidth method to make sure it gives the correct
	 * property from the source image
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetWidth()
	{
		// Create a 108x42 image handle and add no transparency.
		$imageHandle = imagecreatetruecolor(108, 42);

		// Create a new JImageInspector object from the image handle.
		$image = new JImageInspector($imageHandle);

		$this->assertTrue(($image->getWidth() == 108), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JImage::getWidth method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testGetWidthWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->getWidth();
	}

	/**
	 * Test the JImage::getImageFileProperties method without a valid image file.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since   11.3
	 */
	public function testGetImageFilePropertiesWithInvalidFile()
	{
		JImage::getImageFileProperties(JPATH_TESTS . '/suite/joomla/image/stubs/bogus.image');
	}

	/**
	 * Test the JImage::isTransparent method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testIsTransparentWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->isTransparent();
	}

	/**
	 * Test the JImage::isTransparent method to make sure it gives the correct
	 * result if the image has an alpha channel.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTransparentIsTransparent()
	{
		// Create a 10x10 image handle.
		$transparentImage = imagecreatetruecolor(10, 10);

		// Set black to be transparent in the image.
		imagecolortransparent($transparentImage, imagecolorallocate($transparentImage, 0, 0, 0));

		// Create a new JImageInspector object from the image handle.
		$transparent = new JImageInspector($transparentImage);

		// Assert that the image has transparency.
		$this->assertTrue(($transparent->isTransparent()));
	}

	/**
	 * Test the JImage::isTransparent method to make sure it gives the correct
	 * result if the image does not haave an alpha channel.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOpaqueIsNotTransparent()
	{
		// Create a 10x10 image handle and add no transparency.
		$opaqueImage = imagecreatetruecolor(10, 10);

		// Create a new JImageInspector object from the image handle.
		$opaque = new JImageInspector($opaqueImage);

		// Assert that the image does not have transparency.
		$this->assertFalse(($opaque->isTransparent()));
	}

	/**
	 * Test the JImage::crop method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testCropWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->crop(10, 10, 5, 5);
	}

	/**
	 * Tests the JImage::crop() method.  To test this we create an image that contains a red rectangle
	 * of a certain size [Rectangle1].  Inside of that rectangle [Rectangle1] we draw a white
	 * rectangle [Rectangle2] that is exactly two pixels smaller in width and height than its parent
	 * rectangle [Rectangle1].  Then we crop the image to the exact coordinates of Rectangle1 and
	 * verify both it's corners and the corners inside of it.
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
	 * @since   11.3
	 */
	public function testCrop($startHeight, $startWidth, $cropHeight, $cropWidth, $cropTop, $cropLeft, $transparent = false)
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor($startWidth, $startHeight);

		// If the transparent flag is true set black to transparent.
		if ($transparent)
		{
			imagecolortransparent($imageHandle, imagecolorallocate($imageHandle, 0, 0, 0));
		}

		// Define red and white.
		$red = imagecolorallocate($imageHandle, 255, 0, 0);
		$white = imagecolorallocate($imageHandle, 255, 255, 255);

		// Draw a red rectangle in the crop area.
		imagefilledrectangle($imageHandle, $cropLeft, $cropTop, ($cropLeft + $cropWidth), ($cropTop + $cropHeight), $red);

		// Draw a white rectangle one pixel inside the crop area.
		imagefilledrectangle($imageHandle, ($cropLeft + 1), ($cropTop + 1), ($cropLeft + $cropWidth - 2), ($cropTop + $cropHeight - 2), $white);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Crop the image to specifications.
		$image->crop($cropWidth, $cropHeight, $cropLeft, $cropTop, false);

		// Verify that the cropped image is the correct size.
		$this->assertEquals($cropHeight, imagesy($image->getClassProperty('handle')));
		$this->assertEquals($cropWidth, imagesx($image->getClassProperty('handle')));

		// Validate the correct pixels for the corners.
		// Top/Left
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), 0, 0));
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), 1, 1));

		// Top/Right
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), 0, ($cropHeight - 1)));
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), 1, ($cropHeight - 2)));

		// Bottom/Left
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), ($cropWidth - 1), 0));
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), ($cropWidth - 2), 1));

		// Bottom/Right
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), ($cropWidth - 1), ($cropHeight - 1)));
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), ($cropWidth - 2), ($cropHeight - 2)));
	}

	/**
	 * Test the JImage::rotate method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testRotateWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->rotate(90);
	}

	/**
	 * Tests the JImage::rotate() method.  To test this we create an image that contains a red
	 * horizontal line in the middle of the image, and a white vertical line in the middle of the
	 * image.  Once the image is rotated 90 degrees we test the end points of the lines to ensure that
	 * the colors have swapped.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRotate()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(101, 101);

		// Define red and white.
		$red = imagecolorallocate($imageHandle, 255, 0, 0);
		$white = imagecolorallocate($imageHandle, 255, 255, 255);

		// Draw a red horizontal line in the middle of the image.
		imageline($imageHandle, 5, 50, 95, 50, $red);

		// Draw a white vertical line in the middle of the image.
		imageline($imageHandle, 50, 5, 50, 95, $white);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Crop the image to specifications.
		$image->rotate(90, -1, false);

		// Validate the correct pixels for the ends of the lines.
		// Red line.
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), 50, 5));
		$this->assertEquals($red, imagecolorat($image->getClassProperty('handle'), 50, 95));

		// White line.
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), 5, 50));
		$this->assertEquals($white, imagecolorat($image->getClassProperty('handle'), 95, 50));
	}

	/**
	 * Test the JImage::filter method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testFilter()
	{
		$handle = imagecreatetruecolor(1, 1);

		// Create the mock filter.
		$mockFilter = $this->getMockForAbstractClass('JImageFilter', array($handle), 'JImageFilterMock', true, false, true);

		// Setup the mock method call expectation.
		$mockFilter->expects($this->once())
			->method('execute');

		// Create a new JImageInspector object.
		$image = new JImageInspector($handle);
		$image->mockFilter = $mockFilter;

		// Execute the filter.
		$image->filter('mock');
	}

	/**
	 * Test the JImage::filter method without a loaded image.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 * @since   11.3
	 */
	public function testFilterWithoutLoadedImage()
	{
		// Create a new JImage object without loading an image.
		$image = new JImage;

		$image->filter('negate');
	}

	/**
	 * Test the JImage::filter method with a bogus filer type so that we expect an exception.
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since   11.3
	 */
	public function testFilterWithInvalidFilterType()
	{
		// Create a new JImageInspector object.
		$image = new JImageInspector(imagecreatetruecolor(10, 10));

		$image->filter('foobar');
	}

	/**
	 * Tests the JImage::prepareDimensions method.
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
	 * @since   11.3
	 */
	public function testPrepareDimensions($inputHeight, $inputWidth, $inputScale, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		$dimensions = $image->prepareDimensions($inputWidth, $inputHeight, $inputScale);

		// Validate the correct response.
		$this->assertEquals($expectedHeight, $dimensions->height);
		$this->assertEquals($expectedWidth, $dimensions->width);
	}

	/**
	 * Tests the JImage::prepareDimensions method with a bogus scale so that an exception is thrown.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since   11.3
	 */
	public function testPrepareDimensionsWithInvalidScale()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		$image->prepareDimensions(123, 456, 42);
	}

	/**
	 * Tests the JImage::sanitizeHeight method.
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
	 * @since   11.3
	 */
	public function testSanitizeHeight($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Validate the correct response.
		$this->assertEquals($expectedHeight, $image->sanitizeHeight($inputHeight, $inputWidth));
	}

	/**
	 * Tests the JImage::sanitizeWidth method.
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
	 * @since   11.3
	 */
	public function testSanitizeWidth($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Validate the correct response.
		$this->assertEquals($expectedWidth, $image->sanitizeWidth($inputWidth, $inputHeight));
	}

	/**
	 * Tests the JImage::sanitizeOffset method.
	 *
	 * @param   mixed    $input     The input offset.
	 * @param   integer  $expected  The expected result offest.
	 *
	 * @return  void
	 *
	 * @dataProvider getSanitizeOffsetData
	 * @since   11.3
	 */
	public function testSanitizeOffset($input, $expected)
	{
		// Create a new JImageInspector.
		$image = new JImageInspector;

		// Validate the correct response.
		$this->assertEquals($expected, $image->sanitizeOffset($input));
	}

	/**
	 * Tests the JImage::destory method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDestroy()
	{
		// Create an image handle
		$imageHandle = imagecreatetruecolor(100, 100);

		// Pass created handle to JImage
		$image = new JImage($imageHandle);

		// Destroying the image should return boolean true
		$this->assertTrue($image->destroy());
	}
}
