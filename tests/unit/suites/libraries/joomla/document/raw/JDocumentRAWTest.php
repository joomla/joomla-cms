<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/document/raw/raw.php';

/**
 * Test class for JDocumentRAW.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRAWTest extends TestCase
{
	/**
	 * @var  JDocumentRaw
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

		$this->object = new JDocumentRaw;
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
			$this->equalTo('Unit Test Buffer')
		);

		$headers = JFactory::getApplication()->getHeaders();

		foreach ($headers as $head)
		{
			if ($head['name'] == 'Expires')
			{
				$this->assertThat(
					$head['value'],
					$this->stringContains('GMT')
				);
			}
		}
	}
}
