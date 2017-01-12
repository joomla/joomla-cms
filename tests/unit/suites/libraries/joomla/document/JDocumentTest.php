<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocument.
 */
class JDocumentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var  JDocument
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JDocument;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		JDocument::$_buffer = null;
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Provides constructor data for test methods
	 *
	 * @return  array
	 */
	public function constructData()
	{
		return array(
			array(
				array('lineend' => "\12"),
				array(
					'lineend' => "\12",
					'charset' => 'utf-8',
					'language' => 'en-gb',
					'direction' => 'ltr',
					'tab' => "\11",
					'link' => '',
					'base' => ''
				)
			),
			array(
				array('charset' => "euc-jp", 'mediaversion' => '1a2b3c4d'),
				array(
					'lineend' => "\12",
					'charset' => 'euc-jp',
					'language' => 'en-gb',
					'direction' => 'ltr',
					'tab' => "\11",
					'link' => '',
					'base' => '',
					'mediaversion' => '1a2b3c4d'
				)
			),
			array(
				array('language' => "de-de", 'direction' => 'rtl',
					'tab' => 'Crazy Tab', 'link' => 'http://joomla.org',
					'base' => 'http://base.joomla.org/dir'),
				array(
					'lineend' => "\12",
					'charset' => 'utf-8',
					'language' => 'de-de',
					'direction' => 'rtl',
					'tab' => "Crazy Tab",
					'link' => 'http://joomla.org',
					'base' => 'http://base.joomla.org/dir'
				)
			)
		);
	}

	/**
	 * @param   array  $options  Options array to inject
	 * @param   array  $expects  Expected data values
	 *
	 * @dataProvider constructData
	 */
	public function testInjectingOptionsIntoTheObjectConstructor($options, $expects)
	{
		$object = new JDocument($options);

		$this->assertAttributeSame($expects['lineend'], '_lineEnd', $object);
		$this->assertAttributeSame($expects['charset'], '_charset', $object);
		$this->assertAttributeSame($expects['language'], 'language', $object);
		$this->assertAttributeSame($expects['direction'], 'direction', $object);
		$this->assertAttributeSame($expects['tab'], '_tab', $object);
		$this->assertAttributeSame($expects['link'], 'link', $object);
		$this->assertAttributeSame($expects['base'], 'base', $object);
	}

	/**
	 * @testdox  Test retrieving an instance of JDocumentHtml
	 */
	public function testRetrievingAnInstanceOfTheHtmlDocument()
	{
		$this->assertInstanceOf('JDocumentHtml', JDocument::getInstance());
	}

	/**
	 * @testdox  Test retrieving non-existing JDocument type returns a JDocumentRaw instance
	 */
	public function testRetrievingANonExistantTypeFetchesARawDocument()
	{
		$doc = JDocument::getInstance('custom');
		$this->assertInstanceOf('JDocumentRaw', $doc);
		$this->assertAttributeSame('custom', '_type', $doc);
	}

	/**
	 * @testdox  Test that setType returns an instance of $this
	 */
	public function testEnsureSetTypeReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setType('raw'));
	}

	/**
	 * @testdox  Test the default return for getType is null
	 */
	public function testTheDefaultReturnForGetTypeIsNull()
	{
		$this->assertNull($this->object->getType());
	}

	/**
	 * @testdox  Test that setBuffer returns an instance of $this
	 */
	public function testEnsureSetBufferReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setBuffer('My awesome content'));
	}

	/**
	 * @testdox  Test the default return for getBuffer is null
	 */
	public function testTheDefaultReturnForGetBufferIsNull()
	{
		$this->assertNull($this->object->getBuffer());
	}

	/**
	 * @testdox  Test that setMetadata with the 'generator' param returns an instance of $this
	 */
	public function testEnsureSetMetadataForGeneratorReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setMetaData('generator', 'My Custom Generator'));
	}

	/**
	 * @testdox  Test the default return for getMetaData with 'generator' param
	 */
	public function testTheDefaultReturnForGetMetaDataWithGenerator()
	{
		$this->assertSame('Joomla! - Open Source Content Management', $this->object->getMetaData('generator'));
	}

	/**
	 * @testdox  Test that setMetadata with the 'description' param returns an instance of $this
	 */
	public function testEnsureSetMetadataForDescriptionReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setMetaData('description', 'My Description'));
	}

	/**
	 * @testdox  Test the default return for getMetaData with 'description' param
	 */
	public function testTheDefaultReturnForGetMetaDataWithDescription()
	{
		$this->assertEmpty($this->object->getMetaData('description'));
	}

	/**
	 * @testdox  Test that setMetadata with a custom param returns an instance of $this
	 */
	public function testEnsureSetMetadataForCustomParamsReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setMetaData('myMetaTag', 'myMetaContent'));
	}

	/**
	 * @testdox  Test the return for getMetaData with a custom param and HTTP-Equiv flag true with data not set to HTTP-Equiv
	 */
	public function testTheReturnForGetMetaDataWithCustomParamAndHttpEquivTrueAndDataNotSet()
	{
		$this->object->setMetaData('myMetaTag', 'myMetaContent');

		$this->assertEmpty($this->object->getMetaData('myMetaTag', true));
	}

	/**
	 * @testdox  Test the return for getMetaData with a custom param and HTTP-Equiv flag true with data set to HTTP-Equiv
	 */
	public function testTheReturnForGetMetaDataWithCustomParamAndHttpEquivTrueAndDataSet()
	{
		$this->object->setMetaData('myMetaTag', 'myMetaContent', true);

		$this->assertSame('myMetaContent', $this->object->getMetaData('myMetaTag', true));
	}

	/**
	 * @testdox  Test the return for getMetaData with a custom param and HTTP-Equiv flag false with data set to HTTP-Equiv
	 */
	public function testTheReturnForGetMetaDataWithCustomParamAndHttpEquivFalseAndDataNotSet()
	{
		$this->object->setMetaData('myMetaTag', 'myMetaContent', true);

		$this->assertEmpty($this->object->getMetaData('myMetaTag'));
	}

	/**
	 * @testdox  Test the return for getMetaData with a custom param and HTTP-Equiv flag false with data not set to HTTP-Equiv
	 */
	public function testTheReturnForGetMetaDataWithCustomParamAndHttpEquivFalseAndDataSet()
	{
		$this->object->setMetaData('myMetaTag', 'myMetaContent');

		$this->assertSame('myMetaContent', $this->object->getMetaData('myMetaTag'));
	}

	/**
	 * @testdox  Test that addScript returns an instance of $this
	 */
	public function testEnsureAddScriptReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addScript('https://www.joomla.org/media/system/js/core.min.js'));
	}

	/**
	 * @testdox  Test that addScriptVersion with default params returns an instance of $this
	 */
	public function testEnsureAddScriptVersionWithDefaultParamsReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addScriptVersion('https://www.joomla.org/media/system/js/core.min.js'));
	}

	/**
	 * @testdox  Test that addScriptVersion with default params and $this->mediaVersion set returns an instance of $this
	 *
	 * @covers   JDocument::addScriptVersion
	 * @uses     JDocument::addScript
	 * @uses     JDocument::getMediaVersion
	 * @uses     JDocument::setMediaVersion
	 */
	public function testEnsureAddScriptVersionWithDefaultParamsAndMediaVersionSetReturnsThisObject()
	{
		$this->object->setMediaVersion('1a2b3c4d');

		$this->assertSame($this->object, $this->object->addScriptVersion('https://www.joomla.org/media/system/js/core.min.js'));
	}

	/**
	 * @testdox  Test that addScriptDeclaration returns an instance of $this
	 */
	public function testEnsureAddScriptDeclarationReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addScriptDeclaration('<script>this.window.close();</script>'));
	}

	/**
	 * @testdox  Test that calling addScriptDeclaration twice returns an instance of $this
	 */
	public function testEnsureTwoAddScriptDeclarationCallsReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addScriptDeclaration('<script>this.document.id();</script>'));
		$this->assertSame($this->object, $this->object->addScriptDeclaration('<script>this.window.close();</script>'));
	}

	/**
	 * @testdox  Test that addStyleSheet returns an instance of $this
	 */
	public function testEnsureAddStylesheetReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addStyleSheet('https://www.joomla.org/media/system/css/system.css'));
	}

	/**
	 * @testdox  Test that addStyleSheetVersion with default params returns an instance of $this
	 */
	public function testEnsureAddStylesheetVersionWithDefaultParamsReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addStyleSheetVersion('https://www.joomla.org/media/system/css/system.css'));
	}

	/**
	 * @testdox  Test that addStyleSheetVersion with default params and $this->mediaVersion set returns an instance of $this
	 *
	 * @covers   JDocument::addStyleSheetVersion
	 * @uses     JDocument::addStylesheet
	 * @uses     JDocument::getMediaVersion
	 * @uses     JDocument::setMediaVersion
	 */
	public function testEnsureAddStylesheetVersionWithDefaultParamsAndMediaVersionSetReturnsThisObject()
	{
		$this->object->setMediaVersion('1a2b3c4d');

		$this->assertSame($this->object, $this->object->addStyleSheetVersion('https://www.joomla.org/media/system/css/system.css'));
	}

	/**
	 * @testdox  Test that addStyleDeclaration returns an instance of $this
	 */
	public function testEnsureAddStyleDeclarationReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addStyleDeclaration('<style>div { padding: 0; }</style>'));
	}

	/**
	 * @testdox  Test that calling addStyleDeclaration twice returns an instance of $this
	 */
	public function testEnsureTwoAddStyleDeclarationCallsReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->addStyleDeclaration('<style>div { padding: 0; }</style>'));
		$this->assertSame($this->object, $this->object->addStyleDeclaration('<style>h1 { font-size: 4px; }</style>'));
	}

	/**
	 * @testdox  Test that setCharset returns an instance of $this
	 */
	public function testEnsureSetCharsetReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setCharset('utf-8'));
	}

	/**
	 * @testdox  Test the default return for getCharset
	 */
	public function testTheDefaultReturnForGetCharset()
	{
		$this->assertSame('utf-8', $this->object->getCharset());
	}

	/**
	 * @testdox  Test that setLanguage returns an instance of $this
	 */
	public function testEnsureSetLanguageReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLanguage('de-de'));
	}

	/**
	 * @testdox  Test the default return for getLanguage
	 */
	public function testTheDefaultReturnForGetLanguage()
	{
		$this->assertSame('en-gb', $this->object->getLanguage());
	}

	/**
	 * @testdox  Test that setDirection returns an instance of $this
	 */
	public function testEnsureSetDirectionReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setDirection('rtl'));
	}

	/**
	 * @testdox  Test the default return for getDirection
	 */
	public function testTheDefaultReturnForGetDirection()
	{
		$this->assertSame('ltr', $this->object->getDirection());
	}

	/**
	 * @testdox  Test that setTitle returns an instance of $this
	 */
	public function testEnsureSetTitleReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setTitle('Joomla! Rocks'));
	}

	/**
	 * @testdox  Test the default return for getTitle
	 */
	public function testTheDefaultReturnForGetTitle()
	{
		$this->assertEmpty($this->object->getTitle());
	}

	/**
	 * @testdox  Test that setBase returns an instance of $this
	 */
	public function testEnsureSetBaseReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setBase('https://www.joomla.org'));
	}

	/**
	 * @testdox  Test the default return for getBase
	 */
	public function testTheDefaultReturnForGetBase()
	{
		$this->assertEmpty($this->object->getBase());
	}

	/**
	 * @testdox  Test that setDescription returns an instance of $this
	 */
	public function testEnsureSetDescriptionReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setDescription('Joomla!'));
	}

	/**
	 * @testdox  Test the default return for getDescription
	 */
	public function testTheDefaultReturnForGetDescription()
	{
		$this->assertEmpty($this->object->getDescription());
	}

	/**
	 * @testdox  Test that setLink returns an instance of $this
	 */
	public function testEnsureSetLinkReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLink('https://www.joomla.org'));
	}

	/**
	 * @testdox  Test the default return for getLink
	 */
	public function testTheDefaultReturnForGetLink()
	{
		$this->assertEmpty($this->object->getLink());
	}

	/**
	 * @testdox  Test that setGenerator returns an instance of $this
	 */
	public function testEnsureSetGeneratorReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setGenerator('Joomla! Content Management System'));
	}

	/**
	 * @testdox  Test the default return for getGenerator
	 */
	public function testTheDefaultReturnForGetGenerator()
	{
		$this->assertSame('Joomla! - Open Source Content Management', $this->object->getGenerator());
	}

	/**
	 * @testdox  Test that setModifiedDate returns an instance of $this
	 */
	public function testEnsureSetModifiedDateReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setModifiedDate('2014-10-17'));
	}

	/**
	 * @testdox  Test the default return for getModifiedDate
	 */
	public function testTheDefaultReturnForGetModifiedDate()
	{
		$this->assertEmpty($this->object->getModifiedDate());
	}

	/**
	 * @testdox  Test that setMimeEncoding returns an instance of $this
	 */
	public function testEnsureSetMimeEncodingReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setMimeEncoding('application/json'));
	}

	/**
	 * @testdox  Test the default return for getMimeEncoding
	 */
	public function testTheDefaultReturnForGetMimeEncoding()
	{
		$this->assertEmpty($this->object->getMimeEncoding());
	}

	/**
	 * @testdox  Test that setLineEnd with param 'win' returns an instance of $this
	 */
	public function testEnsureSetLineEndWinReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLineEnd('win'));
	}

	/**
	 * @testdox  Test that setLineEnd with param 'unix' returns an instance of $this
	 */
	public function testEnsureSetLineEndUnixReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLineEnd('unix'));
	}

	/**
	 * @testdox  Test that setLineEnd with param 'mac' returns an instance of $this
	 */
	public function testEnsureSetLineEndMacReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLineEnd('mac'));
	}

	/**
	 * @testdox  Test that setLineEnd with a custom param returns an instance of $this
	 */
	public function testEnsureSetLineEndCustomReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setLineEnd('special'));
	}

	/**
	 * @testdox  Test the default return for _getLineEnd
	 */
	public function testTheDefaultReturnForGetLineEnd()
	{
		$this->assertSame("\12", $this->object->_getLineEnd());
	}

	/**
	 * @testdox  Test that setTab with a custom param returns an instance of $this
	 */
	public function testEnsureSetTabReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->setTab("\t"));
	}

	/**
	 * @testdox  Test the default return for _getTab
	 */
	public function testTheDefaultReturnForGetTab()
	{
		$this->assertSame("\11", $this->object->_getTab());
	}

	/**
	 * @testdox  Test that loadRenderer returns the intended object
	 *
	 * @covers   JDocument::loadRenderer
	 * @uses     JDocument::setType
	 */
	public function testEnsureLoadRendererReturnsCorrectObject()
	{
		$this->object->setType('html');
		$this->assertInstanceOf('JDocumentRendererHtmlHead', $this->object->loadRenderer('head'));
	}

	/**
	 * @testdox  Test that loadRenderer throws an exception for an unknown renderer type
	 * @expectedException  RuntimeException
	 *
	 * @covers   JDocument::loadRenderer
	 * @uses     JDocument::setType
	 */
	public function testEnsureLoadRendererThrowsException()
	{
		$this->object->setType('html');
		$this->object->loadRenderer('unknown');
	}

	/**
	 * @testdox  Test that parse returns an instance of $this
	 */
	public function testEnsureParseReturnsThisObject()
	{
		$this->assertSame($this->object, $this->object->parse());
	}
}
