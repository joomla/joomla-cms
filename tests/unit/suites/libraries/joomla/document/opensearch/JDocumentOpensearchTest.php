<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDocumentOpensearch
 */
class JDocumentOpensearchTest extends TestCase
{
	/**
	 * @var  JDocumentOpensearch
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['SCRIPT_NAME'] = '';

		$mockApp = $this->getMockCmsApp();
		$mockApp->expects($this->any())
			->method('getName')
			->willReturn('site');

		$mockApp->expects($this->any())
			->method('isClient')
			->with('site')
			->willReturn(true);

		JFactory::$application = $mockApp;

		JFactory::$config = $this->getMockConfig();

		$mockRouter = $this->getMockBuilder('Joomla\\CMS\\Router\\Router')->getMock();
		$mockRouter->expects($this->any())
			->method('build')
			->willReturn(new \JUri);

		TestReflection::setValue('JRoute', '_router', array('site' => $mockRouter));

		$this->object = new JDocumentOpensearch;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		TestReflection::setValue('JRoute', '_router', array());

		$this->restoreFactoryState();

		JDocument::$_buffer = null;

		parent::tearDown();
	}

	/**
	 * @testdox  Test the default return for render
	 */
	public function testTheDefaultReturnForRender()
	{
		$this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $this->object->render());
	}

	/**
	 * @testdox  Test that setShortName returns an instance of $this
	 */
	public function testEnsureSetShortNameReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setShortName('CMS'));
	}

	/**
	 * @testdox  Test that addUrl returns an instance of $this
	 */
	public function testEnsureAddUrlReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addUrl(new JOpenSearchUrl('https://www.joomla.org')));
	}

	/**
	 * @testdox  Test that addImage returns an instance of $this
	 */
	public function testEnsureAddImageReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addImage(new JOpenSearchImage('https://www.joomla.org')));
	}
}
