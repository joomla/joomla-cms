<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperMedia.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 * @since       3.2
 */
class JHelperMediaTest extends TestCaseDatabase
{
	/**
	 * Object under test
	 *
	 * @var    JHelperMedia
	 * @since  3.2
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

		$this->object = new JHelperMedia;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * isImage data
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function isImageProvider()
	{
		return array(
			array('Image file' => 'mypicture.jpg', 1),
			array('Invalid type' => 'mypicture.php', 0),
			array('No extension' => 'mypicture', 0),
			array('Empty string' => '', 0)
		);
	}

	/**
	 * Tests the isImage method
	 *
	 * @param   string  $fileName  The filename
	 * @param   string  $expected  Expected result
	 *
	 * @return  void
	 *
	 * @dataProvider  isImageProvider
	 * @since         3.2
	 */
	public function testIsImage($fileName, $expected)
	{
		$isImage = $this->object->isImage($fileName);
		$this->assertEquals($isImage, $expected);
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
	 * Tests the countFiles method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testCountFiles()
	{
		$countFiles = $this->object->countFiles(JPATH_LIBRARIES . '/phputf8');
		$this->assertSame(array(2, 3), $countFiles);
	}

	/**
	 * canUpload data
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function canUploadProvider()
	{
		return array(
			array('Valid image file' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), true),
			array('File too big' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 10485770), false),
			array('Not an image' => array('name' => 'mypicture.php', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Ends with .' => array('name' => 'mypicture.png.', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Name contains bad characters' => array('name' => 'my<body>picture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Name contains bad extension' => array('name' => 'myscript.php.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Name contains a space' => array('name' => 'my script.php.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Empty name' => array('name' => '', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('Unknown format' => array('name' => 'myfile.xyz', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
			array('File above php limit' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 20485770), false),
			array('File above max configured but below php limit' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 10685770), false),
			);
	}

	/**
	 * Tests the canUpload method
	 *
	 * @param   array    $file      File information
	 * @param   boolean  $expected  Expected result
	 *
	 * @return  void
	 *
	 * @dataProvider  canUploadProvider
	 * @since         3.2
	 */
	public function testCanUpload($file, $expected)
	{
		$canUpload = $this->object->canUpload($file);
		$this->assertEquals($canUpload, $expected);
	}

	/**
	 * imageResize data
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function imageResizeProvider()
	{
		return array(
				array('Bigger Height' => 300, 200, 150, array(150, 100)),
				array('Bigger Width' => 200, 300, 150, array(100, 150)),
				array('Square' => 300, 300, 150, array(150, 150)),
				array('0 Height' => 300, 0, 150, array(150, 0)),
				array('0 Width' => 0, 300, 150, array(0, 150)),
				array('0 Target' => 300, 200, 0, array(0, 0)),
		);
	}

	/**
	 * Tests the imageResize method
	 *
	 * @param   string  $fileName  The filename
	 * @param   string  $expected  Expected result
	 *
	 * @return  void
	 *
	 * @dataProvider  imageResizeProvider
	 * @since         3.2
	 */
	public function testImageResize($width, $height, $target, $expected)
	{
		$newSize = $this->object->imageResize($width, $height, $target);
		$this->assertEquals($newSize, $expected);
	}
}
