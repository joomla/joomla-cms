<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * Value for test host.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_HTTP_HOST = 'example.com';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_REQUEST_URI = '/index.php';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $backupServer;

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

		JFactory::$application = $this->getMockCmsApp();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['SCRIPT_NAME'] = self::TEST_REQUEST_URI;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
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
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
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
		$this->assertEquals(
			JHtml::_('inspector.method1', 'argument1', 'argument2'),
			'JHtmlInspector::method1',
			'JHtmlInspector::method1 could not be called.'
		);

		$this->assertEquals(
			JHtmlInspector::$arguments[0],
			array('argument1', 'argument2'),
			'The arguments where not correctly passed to JHtmlInspector::method1.'
		);
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

		$this->assertFalse(
			JHtml::_('empty.anything')
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

		$this->assertFalse(
			JHtml::_('nofile.anything')
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

		$this->assertFalse(
			JHtml::_('inspector.nomethod')
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
		// Build the mock object.
		$registered = $this->getMockBuilder('MyHtmlClass')
					->setMethods(array('mockFunction'))
					->getMock();

		// Test that we can register the method
		$this->assertTrue(
			JHtml::register('prefix.register.testfunction', array($registered, 'mockFunction')),
			'The class method did not register properly.'
		);

		// Test that calling _ actually calls the function
		$registered->expects($this->once())
			->method('mockFunction');

		JHtml::_('prefix.register.testfunction');
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
		// Build the mock object to Register a method so we can unregister it.
		$registered = $this->getMockBuilder('MyHtmlClass')
					->setMethods(array('mockFunction'))
					->getMock();

		JHtml::register('prefix.unregister.testfunction', array($registered, 'mockFunction'));

		$this->assertTrue(
			JHtml::unregister('prefix.unregister.testfunction'),
			'The method was not unregistered.'
		);

		$this->assertFalse(
			JHtml::unregister('prefix.unregister.testkeynotthere'),
			'Unregistering a missing method should fail.'
		);
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
		// Build the mock object.
		$registered = $this->getMockBuilder('MyHtmlClass')
					->setMethods(array('mockFunction'))
					->getMock();

		// Test that we can register the method.
		JHtml::register('prefix.isregistered.method', array($registered, 'mockFunction'));

		$this->assertTrue(
			JHtml::isRegistered('prefix.isregistered.method'),
			'Calling isRegistered on a valid method should pass.'
		);

		$this->assertFalse(
			JHtml::isRegistered('prefix.isregistered.nomethod'),
			'Calling isRegistered on a missing method should fail.'
		);
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
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>'
			),

			// Standard link with array attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>'
			)
		);
	}

	/**
	 * Tests the link method.
	 *
	 * @param   string $url       The href for the anchor tag.
	 * @param   string $text      The text for the anchor tag.
	 * @param   mixed  $attribs   A string or array of link attributes.
	 * @param   string $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestLink
	 */
	public function testLink($url, $text, $attribs, $expected)
	{
		$this->assertEquals(
			JHtml::link($url, $text, $attribs),
			$expected
		);
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
		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'image1.jpg';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . mt_rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// We create the file that JHtml::image will look for.
		if (!is_dir(JPATH_THEMES . '/' . $template . '/images/' . $urlpath))
		{
			mkdir(JPATH_THEMES . '/' . $template . '/images/' . $urlpath, 0777, true);
		}
		file_put_contents(JPATH_THEMES . '/' . $template . '/images/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the image is in the templates directory.
		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/templates/' . $template . '/images/' . $urlpath . $urlfilename . '" alt="My Alt Text">',
			'JHtml::image failed when we should get it from the templates directory'
		);

		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			JUri::base(true) . '/templates/' . $template . '/images/' . $urlpath . $urlfilename,
			'JHtml::image failed in URL only mode when it should come from the templates directory'
		);

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
		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/media/' . $urlpath . 'images/' . $urlfilename . '" alt="My Alt Text">',
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			JUri::base(true) . '/media/' . $urlpath . 'images/' . $urlfilename,
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		unlink(JPATH_ROOT . '/media/' . $urlpath . 'images/' . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $urlpath . 'images');
		rmdir(JPATH_ROOT . '/media/' . $urlpath);

		// We create the file that JHtml::image will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/system/images/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/system/images/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/images/' . $urlfilename, 'test');

		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/media/system/images/' . $urlfilename . '" alt="My Alt Text">',
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			JUri::base(true) . '/media/system/images/' . $urlfilename,
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		unlink(JPATH_ROOT . '/media/system/images/' . $urlfilename);

		$this->assertEquals(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="" alt="My Alt Text">',
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertNull(
			JHtml::image($urlpath . $urlfilename, 'My Alt Text', null, true, true),
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'image1.jpg';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename, 'test');

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename .
			'" alt="My Alt Text">',
			'JHtml::image failed when we should get it from the media directory, with the plugin fix'
		);

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true, true),
			JUri::base(true) . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename,
			'JHtml::image failed when we should get it from the media directory, with the plugin fix path only mode'
		);

		// We remove the file from the media directory.
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/images');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename, 'test');

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename .
			'" alt="My Alt Text">'
		);

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true, true),
			JUri::base(true) . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename
		);

		unlink(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/images');
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename, 'test');

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="' . JUri::base(true) . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename . '" alt="My Alt Text">'
		);

		$this->assertEquals(
			JHtml::image(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename,
				'My Alt Text', null, true, true
			),
			JUri::base(true) . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename
		);

		unlink(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/images/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/images/' . $element);

		$this->assertEquals(
			JHtml::image($extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text', null, true),
			'<img src="" alt="My Alt Text">'
		);

		$this->assertNull(
			JHtml::image(
				$extension . '/' . $element . '/' . $urlpath . $urlfilename, 'My Alt Text',
				null, true, true
			)
		);

		$this->assertEquals(
			JHtml::image(
				'http://www.example.com/test/image.jpg', 'My Alt Text', array(
					'width' => 150,
					'height' => 150
				)
			),
			'<img src="http://www.example.com/test/image.jpg" alt="My Alt Text" width="150" height="150">',
			'JHtml::image with an absolute path'
		);

		mkdir(JPATH_ROOT . '/test', 0777, true);
		file_put_contents(JPATH_ROOT . '/test/image.jpg', 'test');
		$this->assertEquals(
			JHtml::image('test/image.jpg', 'My Alt Text', array('width' => 150, 'height' => 150), false),
			'<img src="' . JUri::root(true) . '/test/image.jpg" alt="My Alt Text" width="150" height="150">',
			'JHtml::image with an absolute path, URL does not start with http'
		);

		unlink(JPATH_ROOT . '/test/image.jpg');
		rmdir(JPATH_ROOT . '/test');

		$this->assertEquals(
			JHtml::image('test/image.jpg', 'My Alt Text', array('width' => 150, 'height' => 150), false),
			'<img src="" alt="My Alt Text" width="150" height="150">',
			'JHtml::image with an absolute path, URL does not start with http'
		);
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
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>'
			),

			// Iframe with array attribs failed.
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'',
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>'
			)
		);
	}

	/**
	 * Tests the iframe method.
	 *
	 * @param   string $url       iframe URL
	 * @param   string $name      URL name
	 * @param   string $attribs   iframe attribs
	 * @param   string $noFrames  replacement for no frames
	 * @param   string $expected  expected value
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestIFrame
	 */
	public function testIframe($url, $name, $attribs, $noFrames, $expected)
	{
		$this->assertEquals(
			JHtml::iframe($url, $name, $attribs, $noFrames),
			$expected
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
		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'script1.js';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . mt_rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// We create the file that JHtml::image will look for
		mkdir(JPATH_THEMES . '/' . $template . '/js/' . $urlpath, 0777, true);
		file_put_contents(JPATH_THEMES . '/' . $template . '/js/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the js is in the templates directory.
		JHtml::script($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/templates/' . $template . '/js/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the templates directory'
		);

		$this->assertEquals(
			JHtml::script($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/templates/' . $template . '/js/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the templates directory'
		);

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
		JHtml::script($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/' . $urlpath . 'js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $urlpath . 'js/' . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

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
		JHtml::script($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/js/' . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $urlfilename);

		// We do a test for the case that the js is in the media directory.
		JHtml::script($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEmpty(
			JHtml::script($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'script1.js';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath, 0777, true);

		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename, 'test');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));
		$this->assertArrayHasKey(
			'/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename, JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		// We remove the file from the media directory
		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/js');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename, 'test');
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/js');
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename, 'test');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));
		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename, JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element);

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEmpty(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		mkdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0.js', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0_0.js', 'test');
		JBrowser::getInstance()->setBrowser('mybrowser');

		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			array(
				JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
				JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser.js',
				JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0.js',
				JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1_mybrowser_0_0.js'
			),
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectBrowser' => false)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

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
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1.js',
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'detectDebug' => false));

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectDebug' => false)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_scripts = array();
		JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'detectDebug' => false));

		$this->assertArrayHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::script failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::script($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectDebug' => false)),
			JUri::base(true) . '/media/system/js/' . $element . '/' . $urlpath . 'script1.js',
			'Line:' . __LINE__ . ' JHtml::script failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_scripts = array();
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath . 'script1-uncompressed.js');
		rmdir(JPATH_ROOT . '/media/system/js/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/js/' . $element);
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
		// These are some paths to pass to JHtml for testing purposes.
		$urlpath = 'test1/';
		$urlfilename = 'style1.css';

		// We generate a random template name so that we don't collide or hit anything.
		$template = 'mytemplate' . mt_rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate.
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// We create the file that JHtml::image will look for.
		mkdir(JPATH_THEMES . '/' . $template . '/css/' . $urlpath, 0777, true);
		file_put_contents(JPATH_THEMES . '/' . $template . '/css/' . $urlpath . $urlfilename, 'test');

		// We do a test for the case that the css is in the templates directory
		JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true));
		$this->assertArrayHasKey(
			'/templates/' . $template . '/css/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the templates directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/templates/' . $template . '/css/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the templates directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_THEMES . '/' . $template . '/css/' . $urlpath . $urlfilename);
		rmdir(JPATH_THEMES . '/' . $template . '/css/' . $urlpath);
		rmdir(JPATH_THEMES . '/' . $template . '/css');
		rmdir(JPATH_THEMES . '/' . $template);

		// We create the file that JHtml::stylesheet will look for
		if (!is_dir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename, 'test');

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true));
		$this->assertArrayHasKey(
			'/media/' . $urlpath . 'css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $urlpath . 'css/' . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/' . $urlpath . 'css/' . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $urlpath . 'css');
		rmdir(JPATH_ROOT . '/media/' . $urlpath);

		// We create the file that JHtml::stylesheet will look for.
		if (!is_dir(dirname(JPATH_ROOT . '/media/system/css/' . $urlfilename)))
		{
			mkdir(dirname(JPATH_ROOT . '/media/system/css/' . $urlfilename), 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $urlfilename, 'test');

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/css/' . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $urlfilename);

		// We do a test for the case that the css is in the media directory.
		JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEmpty(
			JHtml::stylesheet($urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'style1.css';

		mkdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename, 'test');
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		// We remove the file from the media directory
		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element . '/css');
		rmdir(JPATH_ROOT . '/media/' . $extension . '/' . $element);
		rmdir(JPATH_ROOT . '/media/' . $extension);

		mkdir(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename, 'test');
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/' . $extension . '/css/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

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

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEmpty(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		if (!is_dir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath))
		{
			mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		}
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0.css', 'test');
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0_0.css', 'test');
		JBrowser::getInstance()->setBrowser('mybrowser');

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));
		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			array(
				JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
				JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser.css',
				JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0.css',
				JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1_mybrowser_0_0.css'
			),
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectBrowser' => false)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

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
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1.css',
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 1);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'detectDebug' => false));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectDebug' => false)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::getConfig()->set('debug', 0);
		JFactory::$document->_styleSheets = array();
		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'detectDebug' => false));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertArrayNotHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css',
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true, 'pathOnly' => true, 'detectDebug' => false)),
			JUri::base(true) . '/media/system/css/' . $element . '/' . $urlpath . 'style1.css',
			'Line:' . __LINE__ . ' JHtml::stylesheet failed in URL only mode when it should come from the media directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . 'style1-uncompressed.css');
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);

		mkdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename, 'test');

		JHtml::stylesheet($extension . '/' . $element . '/' . $urlpath . $urlfilename, array('relative' => true), array('media' => 'print, screen'));

		$this->assertArrayHasKey(
			'/media/system/css/' . $element . '/' . $urlpath . $urlfilename,
			JFactory::$document->_styleSheets,
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		$this->assertEquals(
			JFactory::$document->_styleSheets['/media/system/css/' . $element . '/' . $urlpath . $urlfilename],
			array(
				'media'   => 'print, screen',
				'type'    => 'text/css',
				'options' => array(
					'relative'      => true,
					'pathOnly'      => false,
					'detectBrowser' => true,
					'detectDebug'   => true,
				),
			),
			'Line:' . __LINE__ . ' JHtml::stylesheet failed when we should get it from the media directory'
		);

		JFactory::$document->_styleSheets = array();
		unlink(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath . $urlfilename);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element . '/' . $urlpath);
		rmdir(JPATH_ROOT . '/media/system/css/' . $element);
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
		// We generate a random template name so that we don't collide or hit anything
		$template = 'mytemplate' . mt_rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// Testing classical cases
		$this->assertEquals(
			JHtml::tooltip('Content'),
			'<span class="hasTooltip" title="Content"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"></span>',
			'Basic tooltip failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title'),
			'<span class="hasTooltip" title="' .
			'&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content' .
			'"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>',
			'Tooltip with title and content failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title', null, 'Text'),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content">Text</span>',
			'Tooltip with title and content and text failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title', null, 'Text', 'http://www.monsite.com'),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content"><a href="http://www.monsite.com">Text</a></span>',
			'Tooltip with title and content and text and href failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title', 'tooltip.png', null, null, 'MyAlt'),
			'<span class="hasTooltip" title="' .
			'&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content' .
			'"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="MyAlt"></span>',
			'Tooltip with title and content and alt failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title', 'tooltip.png', null, null, 'MyAlt', 'hasTooltip2'),
			'<span class="hasTooltip2" title="' .
			'&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content' .
			'"><img src="' . JUri::base(true) .
			'/media/system/images/tooltip.png" alt="MyAlt"></span>',
			'Tooltip with title and content and alt and class failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', 'Title', null, 'Text', null, null, 'hasTip'),
			'<span class="hasTip" title="Title::Content">Text</span>',
			'Tooltip with hasTip class failed'
		);

		// Testing where title is an array
		$this->assertEquals(
			JHtml::tooltip('Content', array('title' => 'Title')),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"></span>',
			'Tooltip with title and content failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', array('title' => 'Title', 'text' => 'Text')),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content">Text</span>',
			'Tooltip with title and content and text failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', array('title' => 'Title', 'text' => 'Text', 'href' => 'http://www.monsite.com')),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content"><a href="http://www.monsite.com">Text</a></span>',
			'Tooltip with title and content and text and href failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', array('title' => 'Title', 'alt' => 'MyAlt')),
			'<span class="hasTooltip" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="MyAlt"></span>',
			'Tooltip with title and content and alt failed'
		);

		$this->assertEquals(
			JHtml::tooltip('Content', array('title' => 'Title', 'class' => 'hasTooltip2')),
			'<span class="hasTooltip2" title="&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"></span>',
			'Tooltip with title and content and class failed'
		);
		$this->assertEquals(
			JHtml::tooltip('Content', array()),
			'<span class="hasTooltip" title="Content"><img src="' .
			JUri::base(true) . '/media/system/images/tooltip.png" alt="Tooltip"></span>',
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
		$cfg = $this->getMockConfig();
		$map = array(
			array('live_site', 'http://example.com'),
			array('offset', 'Europe/Kiev')
		);
		$cfg->expects($this->any())
			->method('get')
			->willReturnMap($map);

		JFactory::$session = $this->getMockSession();
		JFactory::$config = $cfg;

		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue('atomic'));

		// Two sets of test data
		$test_data = array(
			'date' => '2010-05-28 00:00:00', 'friendly_date' => 'Friday, 28 May 2010',
			'name' => 'cal1_name', 'id' => 'cal1_id', 'format' => '%Y-%m-%d',
			'attribs' => array(), 'formattedDate' => '2010-05-28'
		);

		$test_data_ro = array_merge($test_data, array('attribs' => array('readonly' => 'readonly')));

		foreach (array($test_data, $test_data_ro) as $data)
		{
			// Reset the document
			JFactory::$document = $this->getMockDocument();

			$input = JHtml::calendar($data['date'], $data['name'], $data['id'], $data['format'], $data['attribs']);
			$this->assertGreaterThan(
				0,
				strlen($input),
				'Line:' . __LINE__ . ' The calendar method should return something without error.'
			);
			$readonly = isset($data['attribs']['readonly']) ? 'readonly="readonly"' : '';

			$xml = new SimpleXMLElement('<field name="' . $data['name'] . '" type="calendar" id="' . $data['id'] . '"
			format="' . $data['format'] . '" title="' . $data['friendly_date'] . '" value="' . $data['formattedDate'] . '" ' . $readonly . ' />');

			$this->assertEquals(
				'calendar',
				(string) $xml->attributes()->type,
				'Line:' . __LINE__ . ' The calendar input should have `type == "calendar"`'
			);

			$this->assertEquals(
				$data['friendly_date'],
				(string) $xml->attributes()->title,
				'Line:' . __LINE__ . ' The calendar input should have `title == "' . $data['friendly_date'] . '"`'
			);

			$this->assertEquals(
				$data['name'],
				(string) $xml->attributes()->name,
				'Line:' . __LINE__ . ' The calendar input should have `name == "' . $data['name'] . '"`'
			);

			$this->assertEquals(
				$data['id'],
				(string) $xml->attributes()->id,
				'Line:' . __LINE__ . ' The calendar input should have `id == "' . $data['id'] . '"`'
			);

			$this->assertEquals(
				$data['formattedDate'],
				(string) $xml->attributes()->value,
				'Line:' . __LINE__ . ' The calendar input should have `value == "' . $data['formattedDate'] . '"`'
			);

			if (isset($data['attribs']['readonly']) && $data['attribs']['readonly'] === 'readonly')
			{
				$this->assertEquals(
					$data['attribs']['readonly'],
					$xml->attributes()->readonly,
					'Line:' . __LINE__ . ' The calendar input should have readonly attribute'
				);
			}

			$this->assertArrayHasKey(
				'/media/system/js/fields/calendar-locales/en.js',
				JFactory::getDocument()->_scripts,
				'Line:' . __LINE__ . ' JS file "calendar-locales/en.js" should be loaded'
			);

			$this->assertArrayHasKey(
				'/media/system/js/fields/calendar-locales/date/gregorian/date-helper.min.js',
				JFactory::getDocument()->_scripts,
				'Line:' . __LINE__ . ' JS file "date.js" should be loaded'
			);

			$this->assertArrayHasKey(
				'/media/system/js/fields/calendar.min.js',
				JFactory::getDocument()->_scripts,
				'Line:' . __LINE__ . ' JS file "calendar.min.js" should be loaded'
			);
		}
	}

	/**
	 * Gets the data for testing the JHtml::tooltipText method.
	 *
	 * @return  array
	 *
	 * @since   3.4.4
	 */
	public function dataTestTooltipText()
	{
		return array(
			array(
				'Title::Content',
				'',
				'&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content',
				'A string with "::" should be converted',
			),
			array(
				'Title:Content',
				'',
				'Title:Content',
				'A string without "::" should not be converted',
			),
			array(
				'Title',
				'Content',
				'&lt;strong&gt;Title&lt;/strong&gt;&lt;br /&gt;Content',
				'A title and content should be combined',
			),
			array(
				'',
				'Content',
				'Content',
				'If no title is given, return content string',
			),
		);
	}

	/**
	 * Tests JHtml::tooltipText().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @dataProvider dataTestTooltipText
	 */
	public function testTooltipText($title, $content, $expected, $failureText)
	{
		$this->assertEquals(
			JHtml::tooltipText($title, $content),
			$expected,
			$failureText
		);
	}
}
