<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlFormTest extends TestCase
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
		parent::setup();

		$this->saveFactoryState();

		JFactory::$session = $this->getMockSession();
	}

	/**
	 * Tears down the fixture.
	 *
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
	 * Tests the JHtmlForm::token method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testToken()
	{
		JFactory::$application = $this->getMockWeb();

		$token = JSession::getFormToken();

		$this->assertThat(
			JHtml::_('form.token'),
			$this->equalTo('<input type="hidden" name="' . $token . '" value="1" />')
		);
	}
}
