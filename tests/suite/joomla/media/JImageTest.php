<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/media/image.php';
require_once JPATH_TESTS . '/suite/joomla/media/TestStubs/JImageInspector.php';

/**
 * Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 * @since       11.3
 */
class JImageTest extends JoomlaTestCase
{
	/**
	 * @var    JImageInspector  An inspector of JImageInspector for testing.
	 * @since  11.3
	 */
	protected $inspector;

	protected $inFile = '/suite/joomla/media/TestImages/koala.jpg';
	protected $outFile = '/suite/joomla/media/TestImages/out.jpg';

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function setUp()
	{
		parent::setUp();

		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping JImage tests.');
		}

		// Get a new JImage inspector.
		$this->inspector = new JImageInspector(JPATH_TESTS . $this->inFile);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.3
	 */
	protected function tearDown()
	{
		// Reset the JImage inspector.
		$this->inspector = null;

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
			// inputHeight, inputWidth, inputScale, imageHeight, imageWidth, expectedHeight, expectedWidth
			array(43, 56, JImage::SCALE_FILL,    100, 100, 43, 56),
			array(33, 56, JImage::SCALE_FILL,    10, 10, 33, 56),
 			array(24, 76, JImage::SCALE_INSIDE,  100, 100, 24,  24),
 			array(44, 80, JImage::SCALE_OUTSIDE, 100, 50, 160, 80),
 			array(24, 80, JImage::SCALE_OUTSIDE, 100, 50, 160, 80),
			array(33, 50, JImage::SCALE_INSIDE,  20, 100, 10,  50),
			array(12, 50, JImage::SCALE_INSIDE,  20, 100, 10,  50),
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
			// inputHeight, inputWidth, imageHeight, imageWidth, expectedHeight, expectedWidth
			array(42.5,  56.2,  10, 10, 43, 56),
			array(33,    56.2,  10, 10, 33, 56),
			array('40%', 56.2,  10, 10, 4,  56),
			array(42.5,  '5%',  10, 10, 43, 1),
			array('33%', '25%', 10, 10, 3,  3),
			array('40%', null,  10, 10, 4,  4),
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
			// startHeight, startWidth, cropHeight, cropWidth, cropTop, cropLeft, transparency
 			array(100, 100, 10, 10, 25,  25, false),
 			array(100, 100, 25, 25, 40,  31, true),
			array(225, 432, 45, 11, 123, 12, true),
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
			// input, expected
			array(42.5, 43),
			array(56.2, 56),
		);
	}

	/**
	 * Tests the JImage::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
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
	 * Test the JImage::loadFromFile to makes sure images are loaded properly
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadFromFile()
	{
		$this->markTestIncomplete();
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
	public function testToFile()
	{
		$this->inspector->toFile(JPATH_TESTS . $this->outFile);

		$a = JImage::getImageFileProperties(JPATH_TESTS . $this->inFile);
		$b = JImage::getImageFileProperties($this->inspector->getPath(JPATH_TESTS . $this->outFile));

		// Make sure the properties are the same for both the source and target image.
		foreach (array_keys(get_object_vars($a)) as $property)
		{
			$this->assertTrue(($a->$property == $b->$property), 'Line: ' . __LINE__);
		}

		// Clean up after ourselves.
		unlink(JPATH_TESTS . $this->outFile);
	}

	/**
	 * Test the JImage::getHeight method to make sure it gives the correct
	 * property from the source image
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetHeight()
	{
		$this->assertTrue(($this->inspector->getHeight() == 341), 'Line: ' . __LINE__);
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
		$this->assertTrue(($this->inspector->getWidth() == 500), 'Line: ' . __LINE__);
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
	 * Tests the JImage::crop method.  To test this we create an image that contains a red rectangle
	 * of a certain size [Rectangle1].  Inside of that rectangle [Rectangle1] we draw a white
	 * rectangle [Rectangle2] that is exactly two pixels smaller in width and height than its parent
	 * rectangle [Rectangle1].  Then we crop the image to the exact coordinates of Rectangle1 and
	 * verify both it's corners and the corners inside of it.
	 *
	 * @param   mixed    $startHeight  The name of the configuration file.
	 * @param   mixed    $startWidth   The result is expected to be a class.
	 * @param   integer  $cropHeight   The expected result as an array.
	 * @param   integer  $cropWidth    The expected result as an array.
	 * @param   integer  $cropTop      The expected result as an array.
	 * @param   integer  $cropLeft     The expected result as an array.
	 * @param   boolean  $transparent  True to add transparency to the image.
	 *
	 * @return  void
	 *
	 * @dataProvider getCropData
	 * @since   11.3
	 */
	public function testCrop($startHeight, $startWidth, $cropHeight, $cropWidth, $cropTop, $cropLeft, $transparent = false)
	{
		// Create a image handle of the correct size.
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
		imagefilledrectangle($imageHandle,
			$cropLeft,
			$cropTop,
			($cropLeft + $cropWidth),
			($cropTop + $cropHeight),
			$red
		);

		// Draw a white rectangle one pixel inside the crop area.
		imagefilledrectangle($imageHandle,
			($cropLeft + 1),
			($cropTop + 1),
			($cropLeft + $cropWidth - 2),
			($cropTop + $cropHeight - 2),
			$white
		);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		$image->toFile(JPATH_TESTS . '/suite/joomla/media/TestImages/before.png', IMAGETYPE_PNG);

		// Crop the image to specifications.
		$image->crop($cropWidth, $cropHeight, $cropLeft, $cropTop, false);

		$image->toFile(JPATH_TESTS . '/suite/joomla/media/TestImages/after.png', IMAGETYPE_PNG);

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
	 * Test the JImage::filter method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testFilter()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JImage::prepareDimensions method.
	 *
	 * @param   mixed    $inputHeight     The name of the configuration file.
	 * @param   mixed    $inputWidth      The result is expected to be a class.
	 * @param   integer  $inputScale      The expected result as an array.
	 * @param   integer  $imageHeight     The expected result as an array.
	 * @param   integer  $imageWidth      The expected result as an array.
	 * @param   integer  $expectedHeight  The expected result as an array.
	 * @param   integer  $expectedWidth   The expected result as an array.
	 *
	 * @return  void
	 *
	 * @dataProvider getPrepareDimensionsData
	 * @since   11.3
	 */
	public function testPrepareDimensions($inputHeight, $inputWidth, $inputScale, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create a image handle of the correct size.
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
	* @expectedException  JMediaException
	* @since   11.3
	*/
	public function testPrepareDimensionsWithInvalidScale()
	{
		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		$dimensions = $image->prepareDimensions(123, 456, 42);
	}

	/**
	 * Tests the JImage::sanitizeHeight method.
	 *
	 * @param   mixed    $inputHeight     The name of the configuration file.
	 * @param   mixed    $inputWidth      The result is expected to be a class.
	 * @param   integer  $imageHeight     The expected result as an array.
	 * @param   integer  $imageWidth      The expected result as an array.
	 * @param   integer  $expectedHeight  The expected result as an array.
	 * @param   integer  $expectedWidth   The expected result as an array.
	 *
	 * @return  void
	 *
	 * @dataProvider getSanitizeDimensionData
	 * @since   11.3
	 */
	public function testSanitizeHeight($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Validate the correct response.
		$this->assertEquals($expectedHeight, $image->sanitizeHeight($inputHeight, $inputWidth));
	}

	/**
	 * Tests the JImage::sanitizeWidth method.
	 *
	 * @param   mixed    $inputHeight     The name of the configuration file.
	 * @param   mixed    $inputWidth      The result is expected to be a class.
	 * @param   integer  $imageHeight     The expected result as an array.
	 * @param   integer  $imageWidth      The expected result as an array.
	 * @param   integer  $expectedHeight  The expected result as an array.
	 * @param   integer  $expectedWidth   The expected result as an array.
	 *
	 * @return  void
	 *
	 * @dataProvider getSanitizeDimensionData
	 * @since   11.3
	 */
	public function testSanitizeWidth($inputHeight, $inputWidth, $imageHeight, $imageWidth, $expectedHeight, $expectedWidth)
	{
		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor($imageWidth, $imageHeight);

		// Create a new JImageInspector from the image handle.
		$image = new JImageInspector($imageHandle);

		// Validate the correct response.
		$this->assertEquals($expectedWidth, $image->sanitizeWidth($inputWidth, $inputHeight));
	}

	/**
	 * Tests the JImage::sanitizeOffset method.
	 *
	 * @param   mixed    $input     The name of the configuration file.
	 * @param   integer  $expected  The expected result as an array.
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
}
