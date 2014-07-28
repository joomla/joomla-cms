<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/document/xml/xml.php';

/**
 * Test class for JDocumentXML.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentXMLTest extends TestCase
{
	/**
	 * @var  JDocumentXml
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockWeb();

		$this->object = new JDocumentXml;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test Render
	 *
	 * @return  void
	 */
	public function testRender()
	{
		$this->object->setBuffer('Unit Test Buffer');

		$this->assertThat(
			$this->object->render(),
			$this->equalTo('Unit Test Buffer'),
			'We did not get the buffer back properly'
		);

		$headers = JFactory::getApplication()->getHeaders();

		$disposition = false;

		foreach ($headers as $head)
		{
			if ($head['name'] == 'Content-disposition')
			{
				$this->assertThat(
					$head['value'],
					$this->stringContains('.xml'),
					'The content disposition did not include json extension'
				);
				$disposition = true;
			}
		}

		$this->assertThat(
			$disposition,
			$this->equalTo(true),
			'No Content-disposition headers'
		);
	}

	/**
	 * We test both at once
	 *
	 * @return  void
	 */
	public function testGetAndSetName()
	{
		$this->object->setName('unittestfilename');

		$this->assertThat(
			$this->object->getName(),
			$this->equalTo('unittestfilename'),
			'setName or getName did not work'
		);
	}
}
