<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
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

		JFactory::$session = $this->getMockSession();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
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
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
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
			JHtmlForm::token(),
			$this->equalTo('<input type="hidden" name="' . $token . '" value="1" />')
		);
	}

	/**
	 * Tests the JHtmlForm::csrf method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCsrf()
	{
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$document = new JDocumentHtml;

		JHtmlForm::csrf();

		$doc = JFactory::getDocument();

		$this->assertEquals(JSession::getFormToken(), $doc->_metaTags['name']['csrf-token']);
	}
}
