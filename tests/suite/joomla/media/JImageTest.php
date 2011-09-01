<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/media/image.php';

/**
 *  Test class for JImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 *
 * @since       11.3
 */
class JImageTest extends PHPUnit_Framework_TestCase
{
	protected $img;
	const IFILE = "tests/suite/joomla/media/TestImages/koala.jpg";
	const OFILE = "tests/tmp/out.jpg";

	/**
	* Set up the test framework
	*
	* @return  void
	*
	* @since   11.3
	*/
	public function setUp()
	{
		$this->img = new JImage(self::IFILE);
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
		$this->img->toFile(self::OFILE);
		$a = JImage::getImageFileProperties(self::IFILE);
		$b = JImage::getImageFileProperties($this->img->getPath(self::OFILE));

		// Make sure the properties are the same for both the source and target image
		foreach (array_keys(get_object_vars($a)) as $property)
		{
			$this->assertTrue(($a->$property == $b->$property), 'Line: '.__LINE__);
		}
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
		$this->assertTrue(($this->img->getHeight() == 341), 'Line: '.__LINE__);
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
		$this->assertTrue(($this->img->getWidth() == 500), 'Line: '.__LINE__);
	}

	/**
	 * Test the JImage::isTransparent method to make sure it gives the correct
	 * result if the image has an alpha channel
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testIsTransparent()
	{
		$this->markTestIncomplete();
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
}
