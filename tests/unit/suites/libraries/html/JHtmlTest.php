<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Tests for the JHtml class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlTest extends TestCase
{
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

		$this->saveFactoryState();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test the _ method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function test_()
	{
		// Add the include path to html test files.
		JHtml::addIncludePath(array(__DIR__ . '/testfiles'));

		// Test the class method was called and the arguments passed correctly.
		$this->assertThat(
			JHtml::_('inspector.method1', 'argument1', 'argument2'),
			$this->equalTo('JHtmlInspector::method1'),
			'JHtmlInspector::method1 could not be called.');

		$this->assertThat(
			JHtmlInspector::$arguments[0],
			$this->equalTo(array('argument1', 'argument2')),
			'The arguments where not correctly passed to JHtmlInspector::method1.');
	}

	/**
	 * Test the _ method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function test_WithMissingClass()
	{
		// Add the include path to html test files.
		JHtml::addIncludePath(array(__DIR__ . '/testfiles'));

		// Test a class that doesn't exist.
		$this->setExpectedException('InvalidArgumentException');

		$this->assertThat(
			JHtml::_('empty.anything'),
			$this->isFalse()
		);
	}

	/**
	 * Test the _ method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function test_WithMissingFile()
	{
		// Add the include path to html test files.
		JHtml::addIncludePath(array(__DIR__ . '/testfiles'));

		// Test a file that doesn't exist.
		$this->setExpectedException('InvalidArgumentException');

		$this->assertThat(
			JHtml::_('nofile.anything'),
			$this->isFalse()
		);
	}

	/**
	 * Test the _ method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function test_WithMissingMethod()
	{
		// Add the include path to html test files.
		JHtml::addIncludePath(array(__DIR__ . '/testfiles'));

		// Test a method that doesn't exist.
		$this->setExpectedException('InvalidArgumentException');

		$this->assertThat(
			JHtml::_('inspector.nomethod'),
			$this->isFalse()
		);
	}

	/**
	 * Test the register method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testRegister()
	{
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		// Test that we can register the method
		$this->assertThat(
			JHtml::register('prefix.register.testfunction', array($registered, 'mockFunction')),
			$this->isTrue(),
			'The class method did not register properly.');

		// Test that calling _ actually calls the function
		$registered->expects($this->once())
			->method('mockFunction');

		JHtml::_('prefix.register.testfunction');

		$this->assertThat(
			JHtml::register('prefix.register.missingtestfunction', array($registered, 'missingFunction')),
			$this->isFalse(),
			'Registering a missing method should fail.');
	}

	/**
	 * Test the unregister method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUnregister()
	{
		// Register a method so we can unregister it
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		JHtml::register('prefix.unregister.testfunction', array($registered, 'mockFunction'));

		$this->assertThat(
			JHtml::unregister('prefix.unregister.testfunction'),
			$this->isTrue(),
			'The method was not unregistered.');

		$this->assertThat(
			JHtml::unregister('prefix.unregister.testkeynotthere'),
			$this->isFalse(),
			'Unregistering a missing method should fail.');
	}

	/**
	 * Tests the isRegistered method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testIsRegistered()
	{
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		// Test that we can register the method.
		JHtml::register('prefix.isregistered.method', array($registered, 'mockFunction'));

		$this->assertThat(
			JHtml::isRegistered('prefix.isregistered.method'),
			$this->isTrue(),
			'Calling isRegistered on a valid method should pass.');

		$this->assertThat(
			JHtml::isRegistered('prefix.isregistered.nomethod'),
			$this->isFalse(),
			'Calling isRegistered on a missing method should fail.');
	}

	/**
	 * Gets the data for testing the JHtml::link method.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestLink()
	{
		// The array includes $url, $text, $attribs, $expected, $msg
		return array(
			// Standard link with string attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				'title="My Link Title"',
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>'),

			// Standard link with array attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>'));
	}

	/**
	 * Tests the link method.
	 *
	 * @param   string  $url       The href for the anchor tag.
	 * @param   string  $text      The text for the anchor tag.
	 * @param   mixed   $attribs   A string or array of link attributes.
	 * @param   string  $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestLink
	 */
	public function testLink($url, $text, $attribs, $expected)
	{
		$this->assertThat(JHtml::link($url, $text, $attribs), $this->equalTo($expected));
	}

	/**
	 * Tests the image method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testImage()
	{
		if (!is_array($_SERVER))
		{
			$_SERVER = array();
		}

		// We save the state of $_SERVER for later and set it to appropriate values.
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'image1.jpg';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		JFactory::$application = $mock;

		// We create the file that JHtml::image will look for.
		if (!is_dir(JPATH_THEMES . '/' . $template . '/images/' . $urlpath))
		{
			mkdir(JPATH_THEMES . '/' . $template . '/images/' . $urlpath, 0777, true);
		}
		file_put_contents(JPATH_THEMES . '/' . $template . '/images/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the image is in the templates directory.
		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo(
				'<img src="' . JUri::base(true) . '/templates/' . $template . '/images/' . $urlpath . $urlfilename . '" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the templates directory');

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JUri::base(true) . '/templates/' . $template . '/images/' . $urlpath . $urlfilename),
			'JHtml::image failed in URL only mode when it should come from the templates directory');

		unlink(JPATH_THEMES . '/' . $template . '/images/' . $urlpath . $urlfilename);
		rmdir(JPATH_THEMES . '/' . $template . '/images/' . $urlpath);
		rmdir(JPATH_THEMES . '/' . $template . '/images');
		rmdir(JPATH_THEMES . '/' . $template);

		// We create the file that JHtml::image will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'images/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'images/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/' . $urlpath . 'images/' . $urlfilename, 'test');

		// We do a test for the case that the image is in the media directory.
		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="' . JUri::base(true) . '/media/' . $urlpath . 'images/' . $urlfilename . '" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $urlpath . 'images/' . $urlfilename),
			'JHtml::image failed when we should get it from the media directory in path only mode');

		unlink(JPATH_ROOT . '/media/' . $urlpath . 'images/' . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $urlpath . 'images');
		rmdir(JPATH_ROOT . '/media/' . $urlpath);

		// We create the file that JHtml::image will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/system/images/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/system/images/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/images/' . $urlfilename, 'test');

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="' . JUri::base(true) . '/media/system/images/' . $urlfilename . '" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JUri::base(true) . '/media/system/images/' . $urlfilename),
			'JHtml::image failed when we should get it from the media directory in path only mode');

		unlink(JPATH_ROOT . '/media/system/images/' . $urlfilename);

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(null),
			'JHtml::image failed when we should get it from the media directory in path only mode');

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'image1.jpg';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo(
				'<img src="' . JUri::base(true) . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename .
					'" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory, with the plugin fix');

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename),
			'JHtml::image failed when we should get it from the media directory, with the plugin fix path only mode');

		// We remove the file from the media directory.
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo(
				'<img src="' . JUri::base(true) . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename .
					'" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename)
		);

		unlink(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images');
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo(
				'<img src="' . JUri::base(true) . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename . '" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename,
				'My Alt Text', null, true, true
			),
			$this->equalTo(JUri::base(true) . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename)
		);

		unlink(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/images/' . $element);

		$this->assertThat(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text',
				null, true, true
			),
			$this->equalTo(null)
		);

		$this->assertThat(
			JHtml::image('http://www.example.com/test/image.jpg', 'My Alt Text', array('width' => 150,
					'height' => 150)
			),
			$this->equalTo('<img src="http://www.example.com/test/image.jpg" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path');

		mkdir(JPATH_ROOT . '/test', 0777, true);
		file_put_contents(JPATH_ROOT . '/test/image.jpg', 'test');
		$this->assertThat(
			JHtml::image('test/image.jpg', 'My Alt Text', array('width' => 150, 'height' => 150), false),
			$this->equalTo('<img src="' . JUri::root(true) . '/test/image.jpg" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path, URL does not start with http');

		unlink(JPATH_ROOT . '/test/image.jpg');
		rmdir(JPATH_ROOT . '/test');

		$this->assertThat(
			JHtml::image('test/image.jpg', 'My Alt Text', array('width' => 150, 'height' => 150), false),
			$this->equalTo('<img src="" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path, URL does not start with http');

		$_SERVER['HTTP_HOST'] = $http_host;
		$_SERVER['SCRIPT_NAME'] = $script_name;
	}

	/**
	 * Gets the data for testing the JHtml::iframe method.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestIFrame()
	{
		return array(
			// Iframe with text attribs, no noframes text failed.
			array(
				'http://www.example.com',
				'Link Text',
				'title="My Link Title"',
				'',
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>'),

			// Iframe with array attribs failed.
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'',
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>'));
	}

	/**
	 * Tests the iframe method.
	 *
	 * @param   string  $url       iframe URL
	 * @param   string  $name      URL name
	 * @param   string  $attribs   iframe attribs
	 * @param   string  $noFrames  replacement for no frames
	 * @param   string  $expected  expected value
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestIFrame
	 */
	public function testIframe($url, $name, $attribs, $noFrames, $expected)
	{
		$this->assertThat(
			JHtml::iframe($url, $name, $attribs, $noFrames),
			$this->equalTo($expected)
		);
	}

	/**
	 * Tests the script method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @covers  JHtml::script
	 */
	public function testScript()
	{
		if (!is_array($_SERVER))
		{
			$_SERVER = array();
		}

		// We save the state of $_SERVER for later and set it to appropriate values
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'script1.js';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		// We create the file that JHtml::image will look for
		mkdir(JPATH_THEMES . '/' . $template . '/js/' . $urlpath, 0777, true);
		file_put_contents(JPATH_THEMES . '/' . $template . '/js/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the js is in the templates directory.
		JHtml::script($urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/templates/' . $template . '/js/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the templates directory');

		$this->assertThat(
			JHtml::script($urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/templates/' . $template . '/js/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the templates directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_THEMES . '/' . $template . '/js/' . $urlpath . $urlfilename);
		rmdir(JPATH_THEMES . '/' . $template . '/js/' . $urlpath);
		rmdir(JPATH_THEMES . '/' . $template . '/js');
		rmdir(JPATH_THEMES . '/' . $template);

		// We create the file that JHtml::script will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'js/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'js/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/' . $urlpath . 'js/' . $urlfilename, 'test');

		// We do a test for the case that the js is in the media directory.
		JHtml::script($urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/' . $urlpath . 'js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $urlpath . 'js/' . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/' . $urlpath . 'js/' . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $urlpath . 'js');
		rmdir(JPATH_ROOT . '/media/' . $urlpath);

		// We create the file that JHtml::script will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/system/js/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/system/js/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $urlfilename, 'test');

		// We do a test for the case that the js is in the media directory.
		JHtml::script($urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/system/js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $urlfilename);

		// We do a test for the case that the js is in the media directory.
		JHtml::script($urlpath . $urlfilename, false, true);

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($urlpath . $urlfilename, false, true, true),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'script1.js';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath, 0777, true);

		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename, 'test');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);
		$this->assertArrayHasKey(
			'/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename, JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		// We remove the file from the media directory
		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename, 'test');
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js');
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename, 'test');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);
		$this->assertArrayHasKey('/media/system/js/' . $element . '/' . $urlpath . $urlfilename, JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element);

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		mkdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0.js', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0_0.js', 'test');
		JBrowser::getInstance()->setBrowser('mybrowser');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(
				array(
					JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
					JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js',
					JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0.js',
					JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0_0.js')
			),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true, false),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js');
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0.js');
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0_0.js');
		rmdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element);

		mkdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js', 'test');

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $element . '/' . $urlpath . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, true),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1.js'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, false, true, false);

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true,
				true, true, false
			),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true, false, true, false);

		$this->assertArrayHasKey('/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_scripts,
			$this->logicalNot($this->arrayHasKey('/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::script(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename,
				false, true, true, true, false
			),
			$this->equalTo(JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1.js'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js');
		rmdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element);

		$_SERVER['HTTP_HOST'] = $http_host;
		$_SERVER['SCRIPT_NAME'] = $script_name;
	}

	/**
	 * Test...
	 *
	 * @return  void
	 *
	 * @todo    Implement testSetFormatOptions().
	 */
	public function testSetFormatOptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the stylesheet method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStylesheet()
	{
		if (!is_array($_SERVER))
		{
			$_SERVER = array();
		}

		// We save the state of $_SERVER for later and set it to appropriate values.
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'style1.css';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		// We create the file that JHtml::image will look for.
		mkdir(JPATH_THEMES . '/' . $template . '/css/' . $urlpath, 0777, true);
		file_put_contents(JPATH_THEMES . '/' . $template . '/css/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the css is in the templates directory
		JHtml::stylesheet($urlpath . $urlfilename, array(), true);
		$this->assertArrayHasKey(
			'/templates/' . $template . '/css/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the templates directory');

		$this->assertThat(
			JHtml::stylesheet($urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/templates/' . $template . '/css/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the templates directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_THEMES . '/' . $template . '/css/' . $urlpath . $urlfilename);
		rmdir(JPATH_THEMES . '/' . $template . '/css/' . $urlpath);
		rmdir(JPATH_THEMES . '/' . $template . '/css');
		rmdir(JPATH_THEMES . '/' . $template);

		// We create the file that JHtml::script will look for
		if (!is_dir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename, 'test');

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array(), true);
		$this->assertArrayHasKey(
			'/media/' . $urlpath . 'css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $urlpath . 'css/' . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $urlpath . 'css');
		rmdir(JPATH_ROOT . '/media/' . $urlpath);

		// We create the file that JHtml::script will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/system/css/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/system/css/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $urlfilename, 'test');

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array(), true);

		$this->assertArrayHasKey(
			'/media/system/css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $urlfilename);

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array(), true);

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($urlpath . $urlfilename, array(), true, true),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'style1.css';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename, 'test');
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);

		$this->assertArrayHasKey(
			'/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		// We remove the file from the media directory
		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename, 'test');
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);

		$this->assertArrayHasKey(
			'/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/css/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/css');
		rmdir(JPATH_ROOT . '/media/' . $extension);

		if (!is_dir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath))
		{
			mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		if (!is_dir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath))
		{
			mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0.css', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0_0.css', 'test');
		JBrowser::getInstance()->setBrowser('mybrowser');

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);
		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(
				array(
					JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
					JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css',
					JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0.css',
					JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0_0.css')
			),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true, false),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css');
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0.css');
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0_0.css');
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css', 'test');

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $element . '/' . $urlpath . $urlfilename)),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, false, true);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1.css'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, false, true, false);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true, true, false),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, false, true, false);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JFactory::$document->_styleSheets,
			$this->logicalNot($this->arrayHasKey('/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css')),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertThat(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array(), true, true, true, false),
			$this->equalTo(JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1.css'),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css');
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('media' => 'print, screen'), true);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		$this->assertEquals(
			JFactory::$document->_styleSheets['/media/system/css/' . $element . '/' . $urlpath . $urlfilename]['attribs'],
			array('media' => 'print, screen'),
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory');

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		$_SERVER['HTTP_HOST'] = $http_host;
		$_SERVER['SCRIPT_NAME'] = $script_name;
	}

	/**
	 * Test...
	 *
	 * @return  void
	 *
	 * @todo    Implement testDate().
	 */
	public function testDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the tooltip method
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTooltip()
	{
		if (!is_array($_SERVER))
		{
			$_SERVER = array();
		}

		// We save the state of $_SERVER for later and set it to appropriate values
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// We generate a random template name so that we don't collide or hit anything
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		JFactory::$application = $mock;

		// Testing classical cases
		$this->assertThat(
			JHtml::tooltip('Content'),
			$this->equalTo('<span class="hasTip" title="Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"  /></span>'),
			'Basic tooltip failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', 'Title'),
			$this->equalTo('<span class="hasTip" title="Title::Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"  /></span>'),
			'Tooltip with title and content failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', 'Title', null, 'Text'),
			$this->equalTo('<span class="hasTip" title="Title::Content">Text</span>'),
			'Tooltip with title and content and text failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', 'Title', null, 'Text', 'http://www.monsite.com'),
			$this->equalTo('<span class="hasTip" title="Title::Content"><a href="http://www.monsite.com">Text</a></span>'),
			'Tooltip with title and content and text and href failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', 'Title', 'tooltip.png', null, null, 'MyAlt'),
			$this->equalTo('<span class="hasTip" title="Title::Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="MyAlt"  /></span>'),
			'Tooltip with title and content and alt failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', 'Title', 'tooltip.png', null, null, 'MyAlt', 'hasTip2'),
			$this->equalTo('<span class="hasTip2" title="Title::Content"><img src="' . JUri::base(true) .
				'/media/system/images/tooltip.png" alt="MyAlt"  /></span>'),
			'Tooltip with title and content and alt and class failed'
		);

		// Testing where title is an array
		$this->assertThat(
			JHtml::tooltip('Content', array('title' => 'Title')),
			$this->equalTo('<span class="hasTip" title="Title::Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"  /></span>'),
			'Tooltip with title and content failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', array('title' => 'Title', 'text' => 'Text')),
			$this->equalTo('<span class="hasTip" title="Title::Content">Text</span>'),
			'Tooltip with title and content and text failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', array('title' => 'Title', 'text' => 'Text', 'href' => 'http://www.monsite.com')),
			$this->equalTo('<span class="hasTip" title="Title::Content"><a href="http://www.monsite.com">Text</a></span>'),
			'Tooltip with title and content and text and href failed'
		);

		$this->assertThat(
			JHtml::tooltip('Content', array('title' => 'Title', 'alt' => 'MyAlt')),
			$this->equalTo('<span class="hasTip" title="Title::Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="MyAlt"  /></span>'),
			'Tooltip with title and content and alt failed'
		);
		$this->assertThat(
			JHtml::tooltip('Content', array('title' => 'Title', 'class' => 'hasTip2')),
			$this->equalTo('<span class="hasTip2" title="Title::Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"  /></span>'),
			'Tooltip with title and content and class failed'
		);
		$this->assertThat(
			JHtml::tooltip('Content', array()),
			$this->equalTo('<span class="hasTip" title="Content"><img src="' .
				JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"  /></span>'),
			'Basic tooltip (array version) failed'
		);
	}

	/**
	 * Tests JHtml::calendar() method with and without 'readonly' attribute.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCalendar()
	{
		// @TODO - Test currently failing, fix this later
		$this->markTestSkipped('Skipping failing test');

		$cfg = new JObject;
		JFactory::$session = $this->getMockSession();
		JFactory::$application = $this->getMockApplication();
		JFactory::$config = $cfg;

		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue('atomic'));

		$cfg->live_site = 'http://example.com';
		$cfg->offset = 'Europe/Kiev';
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser';

		// Two sets of test data
		$test_data = array('date' => '2010-05-28 00:00:00', 'friendly_date' => 'Friday, 28 May 2010',
			'name' => 'cal1_name', 'id' => 'cal1_id', 'format' => '%Y-%m-%d',
			'attribs' => array()
		);

		$test_data_ro = array_merge($test_data, array('attribs' => array('readonly' => 'readonly')));

		foreach (array($test_data, $test_data_ro) as $data)
		{
			// Reset the document
			JFactory::$document = JDocument::getInstance('html', array('unique_key' => serialize($data)));

			$input = JHtml::calendar($data['date'], $data['name'], $data['id'], $data['format'], $data['attribs']);
			$this->assertThat(
				strlen($input),
				$this->greaterThan(0),
				'Line:' . __LINE__ . ' The calendar method should return something without error.'
			);

			$xml = new SimpleXMLElement('<calendar>' . $input . '</calendar>');
			$this->assertEquals(
				'text',
				(string) $xml->input['type'],
				'Line:' . __LINE__ . ' The calendar input should have `type == "text"`'
			);

			// @todo We can't test this yet due to dependency on language strings

			/* $this->assertEquals(
				$data['friendly_date'],
				(string) $xml->input['title'],
				'Line:'.__LINE__.' The calendar input should have `title == "' . $data['friendly_date'] . '"`'
			); */

			// @todo No clue why these 2 don't work

			/*$this->assertEquals(
				$data['name'],
				(string) $xml->input['name'],
				'Line:'.__LINE__.' The calendar input should have `name == "' . $data['name'] . '"`'
			);

			$this->assertEquals(
				$data['id'],
				(string) $xml->input['id'],
				'Line:'.__LINE__.' The calendar input should have `id == "' . $data['id'] . '"`'
			);*/

			$this->assertEquals(
				$data['date'],
				(string) $xml->input['value'],
				'Line:' . __LINE__ . ' The calendar input should have `value == "' . $data['date'] . '"`'
			);

			$head_data = JFactory::getDocument()->getHeadData();

			if (isset($data['attribs']['readonly']) && $data['attribs']['readonly'] === 'readonly')
			{
				$this->assertEquals(
					$data['attribs']['readonly'],
					(string) $xml->input['readonly'],
					'Line:' . __LINE__ . ' The readonly calendar input should have `readonly == "' . $data['attribs']['readonly'] . '"`'
				);

				$this->assertFalse(
					isset($xml->img),
					'Line:' . __LINE__ . ' The readonly calendar input shouldn\'t have a calendar image'
				);

				$this->assertArrayNotHasKey(
					'/media/system/js/calendar.js',
					$head_data['scripts'],
					'Line:' . __LINE__ . ' JS file "calendar.js" shouldn\'t be loaded'
				);

				$this->assertArrayNotHasKey(
					'/media/system/js/calendar-setup.js',
					$head_data['scripts'],
					'Line:' . __LINE__ . ' JS file "calendar-setup.js" shouldn\'t be loaded'
				);

				$this->assertArrayNotHasKey(
					'text/javascript',
					$head_data['script'],
					'Line:' . __LINE__ . ' Inline JS for the calendar shouldn\'t be loaded'
				);
			}
			else
			{
				$this->assertFalse(
					isset($xml->input['readonly']),
					'Line:' . __LINE__ . ' The calendar input shouldn\'t have readonly attribute'
				);

				$this->assertTrue(
					isset($xml->img),
					'Line:' . __LINE__ . ' The calendar input should have a calendar image'
				);

				$this->assertEquals(
					$data['id'] . '_img',
					(string) $xml->img['id'],
					'Line:' . __LINE__ . ' The calendar image should have `id == "' . $data['id'] . '_img' . '"`'
				);

				$this->assertEquals(
					'calendar',
					(string) $xml->img['class'],
					'Line:' . __LINE__ . ' The calendar image should have `class == "calendar"`'
				);

				$this->assertFileExists(
					JPATH_ROOT . $xml->img['src'],
					'Line:' . __LINE__ . ' The calendar image source should point to an existent file'
				);

				/* $this->assertArrayHasKey(
					'/media/system/js/calendar.js',
					$head_data['scripts'],
					'Line:'.__LINE__.' JS file "calendar.js" should be loaded'
				);

				$this->assertArrayHasKey(
					'/media/system/js/calendar-setup.js',
					$head_data['scripts'],
					'Line:'.__LINE__.' JS file "calendar-setup.js" should be loaded'
				);*/

				$this->assertContains(
					'DHTML Date\\/Time Selector',
					$head_data['script']['text/javascript'],
					'Line:' . __LINE__ . ' Inline JS for the calendar should be loaded'
				);
			}
		}
	}

	/**
	 * Test...
	 *
	 * @return  void
	 *
	 * @todo    Implement testAddIncludePath().
	 */
	public function testAddIncludePath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
