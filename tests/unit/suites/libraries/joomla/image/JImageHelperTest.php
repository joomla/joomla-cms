<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JImageHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Image
 * @since       3.4
 */
class JImageHelperTest extends TestCase
{
	/**
	 * Test the JImageHelper::fromBase64 method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testFromBase64()
	{
		$base64data = file_get_contents(__DIR__ . '/stubs/koala.txt');

		// Test the default actions, this will not write the file to the system
		$this->assertTrue(JImageHelper::fromBase64($base64data), 'fromBase64 should read the image data by default with no save action.');

		// Test to ensure the file is correctly written to the filesystem
		$this->assertTrue(
			JImageHelper::fromBase64($base64data, true, 'test-koala.png', __DIR__ . '/stubs/'),
			'fromBase64 should correctly write the file to the filesystem.'
		);

		// Validate the image as a PNG
		$imageProperties = JImageHelper::getImageFileProperties(__DIR__ . '/stubs/test-koala.png');

		$this->assertEquals('image/png', $imageProperties->mime, 'The base64 data should create a PNG image.');

		// Cleanup the environment
		unlink(__DIR__ . '/stubs/test-koala.png');
	}

	/**
	 * Test the JImageHelper::fromBase64 method with no filename specified
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              3.4
	 */
	public function testFromBase64WithNoFilename()
	{
		$base64data = file_get_contents(__DIR__ . '/stubs/koala.txt');

		JImageHelper::fromBase64($base64data, true);
	}

	/**
	 * Test the JImageHelper::fromBase64 method with no filepath specified
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              3.4
	 */
	public function testFromBase64WithNoFilepath()
	{
		$base64data = file_get_contents(__DIR__ . '/stubs/koala.txt');

		JImageHelper::fromBase64($base64data, true, 'test-koala.png');
	}

	/**
	 * Test the JImageHelper::fromBase64 method with a non-existing path
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              3.4
	 */
	public function testFromBase64WithNonexistingPath()
	{
		$base64data = file_get_contents(__DIR__ . '/stubs/koala.txt');

		JImageHelper::fromBase64($base64data, true, 'test-koala.png', __DIR__ . '/stubs/testfolder/');
	}

	/**
	 * Test the JImageHelper::toBase64 method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testToBase64()
	{
		$base64image = JImageHelper::toBase64(__DIR__ . '/stubs/koala.png');

		$this->assertContains('data:image/png', $base64image, 'Tests the output image is the same type as input');

		$this->assertContains(
			'/+hjseLW6kV3QhE2y0sCDqWZibp0fkc3/v3vud73znO9exuH1ERDHGEEIIgUKAGIGIZxn8Lhxa69e+LqX03htjvLUQIyCiEAq5WW7ms',
			$base64image,
			'Tests a substring of the base64 encoding for expected output.'
		);
	}

	/**
	 * Test the JImageHelper::toBase64 method with a non-existent file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testToBase64WithNonExistingFile()
	{
		$base64image = JImageHelper::toBase64(__DIR__ . '/stubs/noway.png');

		$this->assertEquals('', $base64image, 'Tests that toBase64 returns an empty string if the file does not exist.');
	}

	/**
	 * Test the JImageHelper::toBase64 method with a non-image file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testToBase64WithNonImageFile()
	{
		$base64image = JImageHelper::toBase64(__DIR__ . '/stubs/JImageInspector.png');

		$this->assertEquals('', $base64image, 'Tests that toBase64 returns an empty string if the file is not an image.');
	}
}
