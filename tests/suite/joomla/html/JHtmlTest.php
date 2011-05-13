<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/filesystem/path.php';
require_once JPATH_PLATFORM.'/joomla/html/html.php';
require_once JPATH_PLATFORM.'/joomla/utilities/arrayhelper.php';

/**
 * Tests for the JHtml class.
 *
 * @since  11.1
 */
class JHtmlTest extends JoomlaTestCase
{
	/**
	 * @var JHtml
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->saveFactoryState();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test the _ method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test_()
	{
		// Add the include path to html test files.
		JHtml::addIncludePath(array(dirname(__FILE__).'/html/testfiles'));

		// Test the class method was called and the arguments passed correctly.
		$this->assertThat(
			JHtml::_('inspector.method1', 'argument1', 'argument2'),
			$this->equalTo('JHtmlInspector::method1'),
			'JHtmlInspector::method1 could not be called.'
		);

		$this->assertThat(
			JHtmlInspector::$arguments[0],
			$this->equalTo(array('argument1', 'argument2')),
			'The arguments where not correctly passed to JHtmlInspector::method1.'
		);

		// Test error cases.

		// Prepare the error handlers.
		$this->saveErrorHandlers();
		$errorCallback = $this->getMock('errorCallback', array('error1', 'error2', 'error3'));

		$errorCallback->expects($this->once())
			->method('error1');

		$errorCallback->expects($this->once())
			->method('error2');

		$errorCallback->expects($this->once())
			->method('error3');

		// Ensure that we get an error if we can find the file but the file does not contain the class.
		JError::setErrorHandling(E_ERROR, 'callback', array($errorCallback, 'error1'));

		$this->assertThat(
			JHtml::_('empty.anything'),
			$this->isFalse()
		);

		// Ensure that we get an error if we can't find the file.
		JError::setErrorHandling(E_ERROR, 'callback', array($errorCallback, 'error2'));

		$this->assertThat(
			JHtml::_('nofile.anything'),
			$this->isFalse()
		);

		// Ensure that we get an error if we have the class but not the method.
		JError::setErrorHandling(E_ERROR, 'callback', array($errorCallback, 'error3'));

		$this->assertThat(
			JHtml::_('inspector.nomethod'),
			$this->isFalse()
		);

		// Restore the error handlers.
		$this->setErrorHandlers($this->savedErrorState);
	}

	/**
	 * Test the register method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testRegister()
	{
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		// test that we can register the method
		$this->assertThat(
			JHtml::register('prefix.register.testfunction', array($registered, 'mockFunction')),
			$this->isTrue(),
			'The class method did not register properly.'
		);

		// test that calling _ actually calls the function
		$registered->expects($this->once())
			->method('mockFunction');

		JHtml::_('prefix.register.testfunction');

		$this->assertThat(
			JHtml::register('prefix.register.missingtestfunction', array($registered, 'missingFunction')),
			$this->isFalse(),
			'Registering a missing method should fail.'
		);
	}

	/**
	 * Test the unregister method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testUnregister()
	{
		// Register a method so we can unregister it
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		JHtml::register('prefix.unregister.testfunction', array($registered, 'mockFunction'));

		$this->assertThat(
			JHtml::unregister('prefix.unregister.testfunction'),
			$this->isTrue(),
			'The method was not unregistered.'
		);

		$this->assertThat(
			JHtml::unregister('prefix.unregister.testkeynotthere'),
			$this->isFalse(),
			'Unregistering a missing method should fail.'
		);
	}

	/**
	 * Tests the isRegistered method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testIsRegistered()
	{
		$registered = $this->getMock('MyHtmlClass', array('mockFunction'));

		// test that we can register the method
		JHtml::register('prefix.isregistered.method', array($registered, 'mockFunction'));

		$this->assertThat(
			JHtml::isRegistered('prefix.isregistered.method'),
			$this->isTrue(),
			'Calling isRegistered on a valid method should pass.'
		);

		$this->assertThat(
			JHtml::isRegistered('prefix.isregistered.nomethod'),
			$this->isFalse(),
			'Calling isRegistered on a missing method should fail.'
		);
	}

	/**
	 * @todo Implement testCore().
	 */
	public function testCore()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Gets the data for testing the JHtml::link method.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestLink()
	{
		// $url, $text, $attribs, $expected, $msg
		return array(
			// Standard link with string attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				'title="My Link Title"',
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>',
			),

			// Standard link with array attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'<a href="http://www.example.com" title="My Link Title">Link Text</a>',
			)
		);
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
	 * @since   11.1
	 * @dataProvider dataTestLink
	 */
	public function testLink($url, $text, $attribs, $expected)
	{
		$this->assertThat(
			JHtml::link($url, $text, $attribs),
			$this->equalTo($expected)
		);
	}

	/**
	 * @todo Implement testImage().
	 */
	public function testImage()
	{
		if(!is_array($_SERVER)) {
			$_SERVER = array();
		}

		// we save the state of $_SERVER for later and set it to appropriate values
		$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// these are some paths to pass to JHtml for testing purposes
		$urlpath = 'test1/';
		$urlfilename = 'image1.jpg';

		// we generate a random template name so that we don't collide or hit anything
		$template = 'mytemplate'.rand(1,10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		JFactory::$application = $mock;

		// we create the file that JHtml::image will look for
		mkdir(JPATH_THEMES .'/'. $template .'/images/'. $urlpath, 0777, true);
		file_put_contents(JPATH_THEMES .'/'. $template .'/images/'. $urlpath.$urlfilename, 'test');

		// we do a test for the case that the image is in the templates directory
		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/templates/'.$template.'/images/'.$urlpath.$urlfilename.'" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the templates directory'
		);

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/templates/'.$template.'/images/'.$urlpath.$urlfilename),
			'JHtml::image failed in URL only mode when it should come from the templates directory'
		);

		unlink(JPATH_THEMES .'/'. $template .'/images/'. $urlpath.$urlfilename);
		rmdir(JPATH_THEMES .'/'. $template .'/images/'. $urlpath);
		rmdir(JPATH_THEMES .'/'. $template .'/images');
		rmdir(JPATH_THEMES .'/'. $template);

		// we create the file that JHtml::image will look for
		mkdir(JPATH_ROOT .'/media/'. $urlpath .'images', 0777, true);
		file_put_contents(JPATH_ROOT .'/media/'. $urlpath .'images/'. $urlfilename, 'test');

		// we do a test for the case that the image is in the templates directory
		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/media/'.$urlpath.'images/'.$urlfilename.'" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/media/'.$urlpath.'images/'.$urlfilename),
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		unlink(JPATH_ROOT .'/media/'. $urlpath .'images/'. $urlfilename);
		rmdir(JPATH_ROOT .'/media/'. $urlpath .'images');
		rmdir(JPATH_ROOT .'/media/'. $urlpath);

		file_put_contents(JPATH_ROOT .'/media/system/images/'. $urlfilename, 'test');

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/media/system/images/'.$urlfilename.'" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/media/system/images/'.$urlfilename),
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		unlink(JPATH_ROOT .'/media/system/images/'. $urlfilename);

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory'
		);

		$this->assertThat(
			JHtml::image($urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(null),
			'JHtml::image failed when we should get it from the media directory in path only mode'
		);

		$extension = 'testextension';
		$element = 'element';
		$urlpath = 'path1/';
		$urlfilename = 'image1.jpg';

		mkdir(JPATH_ROOT .'/media/'. $extension.'/'.$element .'/images/'. $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT .'/media/'. $extension.'/'.$element .'/images/'. $urlpath.$urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/media/'. $extension.'/'.$element .'/images/'. $urlpath.$urlfilename.'" alt="My Alt Text"  />'),
			'JHtml::image failed when we should get it from the media directory, with the plugin fix'
		);

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/media/'. $extension.'/'.$element .'/images/'. $urlpath.$urlfilename),
			'JHtml::image failed when we should get it from the media directory, with the plugin fix path only mode'
		);
		// we remove the file from the media directory
		unlink(JPATH_ROOT .'/media/'. $extension.'/'.$element .'/images/'. $urlpath.$urlfilename);
		rmdir(JPATH_ROOT .'/media/'. $extension.'/'.$element .'/images/'. $urlpath);
		rmdir(JPATH_ROOT .'/media/'. $extension.'/'.$element .'/images');
		rmdir(JPATH_ROOT .'/media/'. $extension.'/'.$element);
		rmdir(JPATH_ROOT .'/media/'. $extension);

		mkdir(JPATH_ROOT .'/media/'. $extension.'/images/'.$element .'/'. $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT .'/media/'. $extension.'/images/'.$element .'/'. $urlpath.$urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/media/'.$extension.'/images/'.$element.'/'. $urlpath.$urlfilename.'" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/media/'.$extension.'/images/'.$element.'/'.$urlpath.$urlfilename)
		);

		unlink(JPATH_ROOT .'/media/'. $extension.'/images/'.$element .'/'. $urlpath.$urlfilename);
		rmdir(JPATH_ROOT .'/media/'. $extension.'/images/'.$element .'/'. $urlpath);
		rmdir(JPATH_ROOT .'/media/'. $extension.'/images/'.$element);
		rmdir(JPATH_ROOT .'/media/'. $extension.'/images');
		rmdir(JPATH_ROOT .'/media/'. $extension);

		mkdir(JPATH_ROOT .'/media/system/images/'. $element.'/'. $urlpath, 0777, true);
		file_put_contents(JPATH_ROOT .'/media/system/images/'. $element.'/'. $urlpath.$urlfilename, 'test');

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="'.JURI::base(true).'/media/system/images/'.$element.'/'. $urlpath.$urlfilename.'" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(JURI::base(true).'/media/system/images/'.$element.'/'.$urlpath.$urlfilename)
		);

		unlink(JPATH_ROOT .'/media/system/images/'. $element.'/'. $urlpath.$urlfilename);
		rmdir(JPATH_ROOT .'/media/system/images/'. $element.'/'. $urlpath);
		rmdir(JPATH_ROOT .'/media/system/images/'. $element);

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true),
			$this->equalTo('<img src="" alt="My Alt Text"  />')
		);

		$this->assertThat(
			JHtml::image($extension.'/'.$element.'/'.$urlpath.$urlfilename, 'My Alt Text', null, true, true),
			$this->equalTo(null)
		);

		$this->assertThat(
			JHtml::image('http://www.example.com/test/image.jpg', 'My Alt Text',
				array(
					'width' => 150,
					'height' => 150
				)
			),
			$this->equalTo('<img src="http://www.example.com/test/image.jpg" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path'
		);

		mkdir(JPATH_ROOT .'/test', 0777, true);
		file_put_contents(JPATH_ROOT .'/test/image.jpg', 'test');
		$this->assertThat(
			JHtml::image('test/image.jpg', 'My Alt Text',
				array(
					'width' => 150,
					'height' => 150
				),
				false
			),
			$this->equalTo('<img src="'.JURI::root(true).'/test/image.jpg" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path, URL does not start with http'
		);
		unlink(JPATH_ROOT .'/test/image.jpg');
		rmdir(JPATH_ROOT .'/test');

		$this->assertThat(
			JHtml::image('test/image.jpg', 'My Alt Text',
				array(
					'width' => 150,
					'height' => 150
				),
				false
			),
			$this->equalTo('<img src="" alt="My Alt Text" width="150" height="150" />'),
			'JHtml::image with an absolute path, URL does not start with http'
		);

		$_SERVER['HTTP_HOST'] = $http_host;
		$_SERVER['SCRIPT_NAME'] = $script_name;
	}

	/**
	 * Gets the data for testing the JHtml::iframe method.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestIFrame()
	{
		return array(
			// Iframe with text attribs, no noframes text failed
			array(
				'http://www.example.com',
				'Link Text',
				'title="My Link Title"',
				'',
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>',
			),

			// Iframe with array attribs failed
			array(
				'http://www.example.com',
				'Link Text',
				array('title' => 'My Link Title'),
				'',
				'<iframe src="http://www.example.com" title="My Link Title" name="Link Text"></iframe>',
			)
		);
	}

	/**
	 * Tests the iframe method.
	 *
	 * @param   string  $url
	 * @param   string  $name
	 * @param   string  $attribs
	 * @param   string  $noFrames
	 * @param   string  $expected
	 *
	 * @return  void
	 *
	 * @since   11.1
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
	 * @todo  Implement testScript
	 */
	public function testScript()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'The original test is causing errors.'
		);
	}

	/**
	 * @todo Implement testSetFormatOptions().
	 */
	public function testSetFormatOptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testImage().
	 */
	public function testStylesheet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'The original test is causing errors.'
		);
	}

	/**
	 * @todo Implement testDate().
	 */
	public function testDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testTooltip().
	 */
	public function testTooltip()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'The original test is causing errors.'
		);
	}

	/**
	 * Tests JHtml::calendar() method with and without 'readonly' attribute.
	 */
	public function testCalendar()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testAddIncludePath().
	 */
	public function testAddIncludePath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}