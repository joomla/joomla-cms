<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
include_once JPATH_BASE . '/modules/mod_random_image/helper.php';
include_once __DIR__ . '/fixtures/mockParams.php';
include_once __DIR__ . '/fixtures/testglue.php';
include_once __DIR__ . '/fixtures/testtemplateglue.php';
/**
 *	modRandomImageHelperTest
 *
 *	Proposed phpunit testing approach for the module Random Image
 */
class modRandomImageHelperTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	module	the module under test
	 */
    protected $module;
	/**
	 * @var	folder	the test folder name
	 */
    protected $folder = 'tests/unit/suites/modules/mod_random_image/fixtures/images';
	/**
	 * @var	params	the mocked params array for the module
	 */
    protected $params;
    /**
     * @var mock_glue	The mock object for the CMS glue
     */
    protected $mock_glue;
	/**
	 *	setUp
	 *
	 *	Creates the module under test and loads the mock objects into it
	 */
    protected function setUp()
    {
    	$this->params = new mockParams();
    	
    	$this->params->params['width'] = null;
		$this->params->params['height'] = null;
		$this->params->params['link'] = '/';
		$this->params->params['folder'] = $this->folder;
		$this->params->params['type'] = 'jpg';
		$this->params->params['moduleclass_sfx'] = null;
		$this->params->params['layout'] = null;

    	$this->module = new modRandomImageHelper($this->params, "ModRandomImageGlue",
    		"ModRandomImageGlue", "ModRandomImageGlue", "ModRandomImageTemplateGlue",
    		"ModRandomImageGlue");
    }
	/**
	 *	testCreatedModule
	 *
	 *	Tests the just-created module to ensure it's set up properly.
	 */
    public function testCreatedModule()
    {
		$this->assertAttributeEquals($this->params, 'params', $this->module);
	}
	/**
	 *	casesRandomImage
	 *
	 *	Provides test cases for getRandomImage
	 */
	public function casesRandomImage()
	{
		return array(
			array( null, null, array(100,100), array(61,73) ),
			array( null, 200, array(100,100), array(61,73) ),
			array( null, 400, array(100,100), array(61,73) ),
			array( 400, null, array(400,400), array(246,292) ),
			array( 400, 200, array(273,324), array(200,200) ),
			array( 400, 400, array(400,400), array(246,292) ),
			array( 800, null, array(416,450), array(304,277) ),
			array( 800, 200, array(273,324), array(200,200) ),
			array( 800, 400, array(416,450), array(304,277) ),
		);
	}
	/**
	 *	testGetRandomImage
	 *
	 * @dataProvider casesRandomImage
	 */
	public function testGetRandomImage($testWidth, $testHeight, 
		$expectedWidths, $expectedHeights)
	{
		$this->params->params['width'] = $testWidth;
		$this->params->params['height'] = $testHeight;
	    $myImages = array(
   			(object)array("name" => 'EQ.jpg', "folder" => 'tests/unit/suites/modules/mod_random_image/fixtures/images', 
   				'width' => 416, 'height' => 304),
   			(object)array("name" => 'hobok.jpg', "folder" => 'tests/unit/suites/modules/mod_random_image/fixtures/images', 
   				'width' => 450, 'height' => 277),
   		);
		$image = $this->module->getRandomImage($myImages);

		$this->assertContains( $image, $myImages); /* tests for object in array sent */
		$this->assertContains( (int)$image->width, $expectedWidths, "Incorrect Width");
		$this->assertContains( (int)$image->height, $expectedHeights, "Incorrect Height");
	}
	/**
	 *	testGetImage
	 */
	public function testGetImages()
	{
   		$images = $this->module->getImages($this->folder, $this->params->params['type'] = 'jpg');
   		$expected = array(
   			(object)array("name" => 'EQ.jpg', "folder" => 'tests/unit/suites/modules/mod_random_image/fixtures/images'),
   			(object)array("name" => 'hobok.jpg', "folder" => 'tests/unit/suites/modules/mod_random_image/fixtures/images'),
   		);
   		sort($images);
   		sort($expected);
   		
   		$this->assertEquals($expected, $images);
	}
	/**
	 *	casesFolder
	 *
	 *	Provides test cases for getFolder
	 */
	public function casesFolder()
	{
		return array(
			array( "http://www.testingsite.com",
					"http://www.testingsite.com" . __DIR__ . "/fixtures/images",
					0,
					"tests/unit/suites/modules/mod_random_image/fixtures/images"
			),
			array( "", "images", null, "images" ),
		);
	}
	/**
	 *	testGetFolder
	 *
	 *	@dataProvider	casesFolder
	 */
	public function testGetFolder($site, $folder, $searchReturn, $expected)
	{
		ModRandomImageGlue::$strposCalls = 0;
		ModRandomImageGlue::$baseCalls = 0;
		ModRandomImageGlue::$strposReturn = $searchReturn;
		ModRandomImageGlue::$baseReturn = $site;
		
		$actual = $this->module->getFolder($folder);
		
		$this->assertEquals(1, ModRandomImageGlue::$baseCalls,
			"Base was called incorrect number of times");
		$this->assertEquals(2, ModRandomImageGlue::$strposCalls,
			"strpos was called incorrect number of times");
		$this->assertEquals($expected, $actual);
	}
	/**
	 *	casesOutput
	 */
	public function casesOutput()
	{
		return array(
			array($this->folder, '<div class="random-image">', "</div>\n",
				'<img src="/images/test.jpg" alt="test.jpg">',
				__DIR__ . '/fixtures/tmpl/default.php',
				1, 1),
			array($this->folder, '<div class="random-image">', "</div>\n",
				'<img src="/images/test.jpg" alt="test.jpg">',
				JPATH_ROOT . '/modules/mod_random_image/tmpl/default.php',
				1, 1),
			array("Fred", "No", "Images", " Im", __DIR__ . '/fixtures/tmpl/default.php',
				0, 0),
		);
	}
	/**
	 *	testCreateOutput
	 *
	 *	@dataProvider	casesOutput
	 */
	public function testCreateOutput($folder, $startsWith, $endsWith, $image, $layout,
			$layoutCalls, $sendCalls) {
		$this->params->params['folder'] = $folder;
		ModRandomImageGlue::$getLayoutPathCalls = 0;
		ModRandomImageTemplateGlue::$_Calls = 0;
		ModRandomImageGlue::$getLayoutPathReturn = $layout;
		ModRandomImageTemplateGlue::$_Return = $image;
		ModRandomImageGlue::$_Return = "No Images";

    	ob_start();
		$this->module->createOutput('default');
    	$view_output = ob_get_contents();
    	ob_end_clean();

		$this->assertEquals($layoutCalls, ModRandomImageGlue::$getLayoutPathCalls,
			"getLayoutPath was called incorrect number of times");
		$this->assertEquals($layoutCalls, ModRandomImageTemplateGlue::$_Calls,
			"sendHTML was called incorrect number of times");
		$this->assertStringStartsWith($startsWith, $view_output);
		$this->assertStringEndsWith($endsWith, $view_output);
		$this->assertTrue(!!strpos($view_output, $image));
	}
}
?>
