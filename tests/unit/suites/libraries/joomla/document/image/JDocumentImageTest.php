<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/document/document.php';
require_once JPATH_PLATFORM . '/joomla/document/image/image.php';

/**
 * Test class for JDocumentImage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       13.1
 */
class JDocumentImageTest extends TestCase
{
	/**
	 * @var    JDocumentImage
	 * @access protected
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		require_once JPATH_PLATFORM . '/joomla/factory.php';

		$app = $this->getMockApplication();

		JFactory::$application = $app;

		$this->object = new JDocumentImage;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::teardown();
	}

	/**
	 * Tests the JDocumentImage::__construct method.
	 *
	 * @return void
	 *
	 * @covers JDocumentImage::__construct
	 */
	public function test__construct($options = array())
	{
		$documentImage = new JDocumentImage;

		$this->assertThat(
			$documentImage->_mime,
			$this->equalTo('image/png'),
			'JDocumentImage::__construct: Default Mime does not match'
		);

		$this->assertThat(
			$documentImage->_type,
			$this->equalTo('image'),
			'JDocumentImage::__construct: Default Type does not match'
		);
	}

	/**
	 * Tests the JDocumentImage::render method.
	 *
	 * @covers  JDocumentImage::render
	 *
	 * @return  void
	 */
	public function testRender()
	{
		JResponse::clearHeaders();

		$testFiles = array(
			'jpg' => array(
				'file' => 'logo.jpg',
				'mime' => 'image/jpeg'
			),
			'jpeg' => array(
				'file' => 'logo.jpeg',
				'mime' => 'image/jpeg'
			),
			'gif' => array(
				'file' => 'logo.gif',
				'mime' => 'image/gif'
			),
			'png' => array(
				'file' => 'logo.png',
				'mime' => 'image/png',
			),
			'bmp' => array(
				'file' => 'logo.png',
				'mime' => 'image/png'
			)
		);

		foreach ($testFiles as $type => $info)
		{
			// Set type
			JFactory::$application->input->set('type', $type);

			$buffer = file_get_contents(__DIR__ . '/' . $info['file']);

			// Render
			$this->object->setBuffer($buffer);
			$returnBuffer = $this->object->render();

			// Check buffer return
			$this->assertThat(
				$returnBuffer,
				$this->equalTo($buffer),
				'JDocumentImage::render: Buffer does not match for type `' . $type . '`'
			);

			// Check Mime
			$this->assertThat(
				$this->object->_mime,
				$this->equalTo($info['mime']),
				'JDocumentImage::render: Mime does not match for type `' . $type . '`'
			);
		}

		// Chek Charset
		$this->assertThat(
			$this->object->_charset,
			$this->isNull(),
			'JDocumentImage::render Charset is not null'
		);
	}
}
