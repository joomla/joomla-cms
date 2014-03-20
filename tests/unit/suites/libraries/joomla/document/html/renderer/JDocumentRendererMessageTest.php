<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once JPATH_PLATFORM . '/joomla/document/html/renderer/message.php';

/**
 * Test class for JDocumentRendererMessage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererMessageTest extends TestCaseDatabase
{
	/**
	 * The instance of the object to test.
	 *
	 * @var    JDocumentRendererMessage
	 */
	private $_instance;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		JFactory::$application = $this->getMockApplication();
		JFactory::$document = $this->getMockDocument();

		$this->_instance = new JDocumentRendererMessage(JFactory::getDocument());

		parent::setUp();
	}

	/**
	 * Test Render
	 *
	 * @todo Implement testRender().
	 *
	 * @return void
	 */
	public function testRender()
	{
		$app = JFactory::getApplication();

		// Test with no messages in the queue
		$matcher = array(
			'id' => 'system-message-container',
			'tag' => 'div'
			);

		$this->assertTag(
			$matcher,
			$this->_instance->render('foo'),
			'Expected a <div> with id "system-message-container"'
		);

		// Test with a message in the queue
		$app->enqueueMessage('foo', 'bar');

		$matcher['child'] = array(
				'id' => 'system-message',
				'tag' => 'div',
				'child' => array(
						'tag' => 'div',
						'attributes' => array(
								'class' => 'alert alert-bar'
							)
					)
			);

		$this->assertTag(
			$matcher,
			$this->_instance->render('foo'),
			'Expected a tag structure like #system-message-container > #system-message > .alert.alert-bar'
		);
	}
}
