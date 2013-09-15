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
class JHelperMediaTest extends TestCaseDatabase
{
	/**
	 * The mock database object
	 *
	 * @var  JDatabaseDriver
	 */
	protected $db;

	/**
	 * The factory database object
	 *
	 * @var  JDatabaseDriver
	 */
	protected $factoryDb;

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
		parent::setUp();
		JFactory::$application = $this->getMockApplication();

		$this->object = new JHelperMedia;

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
	 * @return array
	 */
	public function isImageProvider()
	{
		return
		array(
				array('Image file' => 'mypicture.jpg', 1),
				array('Invalid type' => 'mypicture.php', 0),
				array('No extension' => 'mypicture', 0),
				array('Empty string' => '', 0),
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
	 * canUpload data
	 *
	 * @return array
	 */
	public function canUploadProvider()
	{
		return
		array(
				array('Valid image file' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), true),
				array('File too big' => array('name' => 'mypicture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 10485770), false),
				array('Not an image' => array('name' => 'mypicture.php', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
				array('Ends with .' => array('name' => 'mypicture.png.', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
				array('Name contains bad characters' => array('name' => 'my<body>picture.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
				array('Name contains bad extension' => array('name' => 'myscript.php.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
				array('Name contains a space' => array('name' => 'my script.php.jpg', 'type' => 'image/jpeg', 'tmp_name' => JPATH_TESTS . '/suites/libraries/joomla/image/stubs/koala.jpg', 'error' => 0, 'size' => 8), false),
		);
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
	 * Tests the canUpload method
	 *
	 * @dataProvider  canUploadProvider
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testCanUpload($file, $expected)
	{
		$canUpload = $this->object->canUpload($file);
		$this->assertEquals($canUpload, $expected);
	}
}
