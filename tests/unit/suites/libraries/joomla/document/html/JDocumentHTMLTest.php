<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocumentHtml
 */
class JDocumentHtmlTest extends TestCase
{
	/**
	 * @var  JDocumentHtml
	 */
	protected $object;

	/**
	 * Test data for methods interfacing with the object's head data methods
	 *
	 * @var  array
	 */
	private $testHeadData = array(
		'title' => 'My Custom Title',
		'description' => 'My Description',
		'link' => 'http://joomla.org',
		'metaTags' => array(
			'myMetaTag' => array('myMetaTag', true)
		),
		'links' => array(
			'index.php' => array(
				'relation' => 'Start',
				'relType' => 'rel',
				'attribs' => array()
			),
			'index.php?option=com_test' => array(
				'relation' => 'End',
				'relType' => 'rel',
				'attribs' => array()
			)
		),
		'styleSheets' => array(
			'test.css' => array(
				'mime' => 'text/css',
				'media' => null,
				'attribs' => array()
			)
		),
		'style' => array(
			'text/css' => 'body { background: white; }'
		),
		'scripts' => array(
			'test.js' => array(
				'mime' => 'text/javascript',
				'defer' => false,
				'async' => false
			)
		),
		'script' => array(
			'text/javascript' => "window.addEvent('load', function() { new JCaption('img.caption'); });"
		),
		'custom' => array(
			"<script>var html5 = true;</script>"
		),
		'scriptText' => array(
			'JYES'
		)
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$language = JLanguage::getInstance('en-GB');

		$this->object = new JDocumentHtml;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		JDocument::$_buffer = null;

		parent::tearDown();
	}

	/**
	 * @testdox  Validate the 'title' key exists in the array returned by getHeadData
	 */
	public function testValidateKeyExistsInDataReturnedByGetHeadData()
	{
		$this->assertArrayHasKey('title', $this->object->getHeadData());
	}

	/**
	 * @testdox  Validate that setHeadData returns null with an empty array
	 */
	public function testValidateSetHeadDataReturnsNullWithEmptyArray()
	{
		$this->assertNull($this->object->setHeadData(array()));
	}

	/**
	 * @testdox  Test that setHeadData returns an instance of $this
	 */
	public function testEnsureSetHeadDataReturnsThisObject()
	{
		// This method calls JText::script() which has a dependency to JHtml::_('behavior.core') and requires the application be loaded
		JFactory::$application = $this->getMockCmsApp();

		$this->assertSame($this->object, $this->object->setHeadData($this->testHeadData));
	}

	/**
	 * @testdox  Validate that mergeHeadData returns null with an empty array
	 */
	public function testValidateMergeHeadDataReturnsNullWithEmptyArray()
	{
		$this->assertNull($this->object->mergeHeadData(array()));
	}

	/**
	 * @testdox  Test that mergeHeadData returns an instance of $this
	 */
	public function testEnsureMergeHeadDataReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->mergeHeadData($this->testHeadData));
	}

	/**
	 * @testdox  Test that addHeadLink returns an instance of $this
	 */
	public function testEnsureAddHeadLinkReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addHeadLink('index.php', 'Start'));
	}

	/**
	 * @testdox  Test that addFavicon returns an instance of $this
	 */
	public function testEnsureAddFaviconReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addFavicon('templates\protostar\favicon.ico'));
	}

	/**
	 * @testdox  Test that addCustomTag returns an instance of $this
	 */
	public function testEnsureAddCustomTagReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addCustomTag("\t  <script>var html5 = true;</script>\r\n"));
	}

	/**
	 * @testdox  Test the default return for isHtml5
	 */
	public function testTheDefaultReturnForIsHtml5()
	{
		$this->assertNull($this->object->isHtml5());
	}

	/**
	 * @testdox  Test the default return for setHtml5
	 */
	public function testTheDefaultReturnForSetHtml5()
	{
		$this->assertNull($this->object->setHtml5(true));
	}

	/**
	 * @testdox  Test the default return for getBuffer is null
	 */
	public function testTheDefaultReturnForGetBufferIsNull()
	{
		$this->assertNull($this->object->getBuffer());
	}

	/**
	 * @testdox  Test that setBuffer returns an instance of $this
	 */
	public function testEnsureSetBufferReturnsThisObject()
	{
		$this->assertSame(
			$this->object,
			$this->object->setBuffer('This is why we test.', array('type' => 'component', 'name' => 'Test Object', 'title' => 'Testing'))
		);
	}
}
