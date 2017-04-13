<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Help
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Help
 * @since       3.0
 */
class JHelpTest extends TestCase
{
	/**
	 * The mock config object
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  3.0
	 */
	protected $config;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		// Store the factory state so we can mock the necessary objects
		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$config      = $this->getMockConfig();
		JFactory::$session     = $this->getMockSession();
		JFactory::$language    = JLanguage::getInstance('en-GB');

		// Set up our mock config
		$this->config = JFactory::getConfig();
		$this->config->set('helpurl', 'https://help.joomla.org/proxy/index.php?keyref=Help{major}{minor}:{keyref}');

		// Load the admin en-GB.ini language file
		JFactory::getLanguage()->load('', JPATH_ADMINISTRATOR);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function tearDown()
	{
		// Restore the state
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the createURL method for com_content's Article Manager view
	 *
	 * @return  void
	 *
	 * @covers  JHelp::createURL
	 * @since   3.0
	 */
	public function testCreateUrl()
	{
		$this->assertEquals(
			'help/en-GB/Content_Article_Manager.html',
			JHelp::createUrl('JHELP_CONTENT_ARTICLE_MANAGER'),
			'Creates a local help URL for com_content Article Manager.'
		);

		$this->assertEquals(
			'components/com_content/help/en-GB/Content_Article_Manager.html',
			JHelp::createUrl('JHELP_CONTENT_ARTICLE_MANAGER', true, null, 'com_content'),
			'Creates a local help URL for com_content Article Manager in the component.'
		);

		$this->assertEquals(
			'http://domain.tld/help',
			JHelp::createUrl('JHELP_CONTENT_ARTICLE_MANAGER', true, 'http://domain.tld/help', 'com_content'),
			'Creates a remote help URL via an override for com_content Article Manager.'
		);

		$this->assertEquals(
			'help/en-GB/Content_Article_Manager.html',
			JHelp::createUrl('JHELP_CONTENT_ARTICLE_MANAGER', false, null, 'com_content'),
			'Creates a local help URL for com_content Article Manager.'
		);
	}

	/**
	 * Tests the createSiteList method
	 *
	 * @return  void
	 *
	 * @covers  JHelp::createSiteList
	 * @since   3.0
	 */
	public function testCreateSiteList()
	{
		$helpsite = array(
			'text' => 'English (GB) help.joomla.org',
			'value' => 'http://help.joomla.org'
		);
		$this->assertEquals(array($helpsite), JHelp::createSiteList(null), 'Returns the default help site list');

		$this->assertInternalType('array', JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml'), 'Returns the help site list defined in the XML file');
	}
}
