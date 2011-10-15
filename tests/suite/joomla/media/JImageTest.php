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
			array(42.5, 56.2, 10, 10, 43, 56),
			array(33, 56.2, 10, 10, 33, 56),
			array('40%', 56.2, 10, 10, 4, 56),
			array(42.5, '5%', 10, 10, 43, 1),
			array('33%', '25%', 10, 10, 3, 3),
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
	 * Test the JImage::resize method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testResize()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the JImage::rotate method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRotate()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the JImage::crop method to make sure it behaves correctly
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCrop()
	{
		$this->markTestIncomplete();
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
