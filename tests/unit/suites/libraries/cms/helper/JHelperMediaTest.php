<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperMedia.
 */
class JHelperMediaTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JHelperMedia
	 * @since  3.1
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		$this->object = new JHelperMedia;
	}

	/**
	 * isImage data
	 *
	 * @return array
	 */
	public function isImageProvider()
	{
		return
		array(
				array('Image file' => 'mypicture.jpg', 'true'),
				array('Invalid type' => 'mypicture.php', 'false'),
				array('No extension' => 'mypicture', 'false'),
				array('Empty string' => '', 'false'),
		);
	}

	/**
	 * Tests the isImage method
	 *
	 * @dataProvider  isImageProvider
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsImage($fileName, $expected)
	{
		if ($expected == 'false')
		{
			// Test fail conditions.
			$this->assertThat(
					$this->object->isImage($fileName),
					$this->isFalse(),
					'Line:' . __LINE__ . ' The method should return' . $expected . '.'
			);
		}
		if ($expected == 'true')
		{
			// Test pass conditions.
			$this->assertThat(
					$this->object->isImage($fileName),
					$this->isTrue(),
					'Line:' . __LINE__ . ' The rule should return' . $expected . '.'
			);
		}
	}

	/**
	 * Tests the getTypeIcon method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTypeIcon()
	{
		$name = $this->object->getTypeIcon('myfile.pdf');
		$this->assertEquals($name, 'pdf');
	}

	/**
	 * canUpload data
	 *
	 * @return array
	 */
	public function canUploadProvider()
	{
		return
		array(
				array('Image file' => 'mypicture.jpg', 'true'),
				array('Invalid type' => 'mypicture.php', 'false'),
				array('No extension' => 'mypicture', 'false'),
				array('Empty string' => '', 'false'),
		);
	}


	/**
	 * Tests the canUpload method
	 *
	 * @dataProvider  canUploadProvider
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testCanUpload()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the countFiles method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testCountFiles()
	{
		$this->markTestSkipped('Test not implemented.');
	}

}