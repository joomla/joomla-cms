<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocumentRendererHtmlModules.
 *
 * @since  3.6
 */
class JDocumentRendererHtmlModulesTest extends TestCaseDatabase
{
	/**
	 * Flag indicating execution of onAfterRenderModules event in JDocumentRendererHtmlModules::render
	 * method.
	 *
	 * @var  boolean
	 */
	protected $callbackExecuted;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$application->expects($this->any())
			->method('triggerEvent')
			->willReturnCallback([$this, 'eventCallback']);

		JFactory::$session = $this->getMockSession();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.6
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');
		$dataSet->addTable('jos_modules', JPATH_TEST_DATABASE . '/jos_modules.csv');
		$dataSet->addTable('jos_modules_menu', JPATH_TEST_DATABASE . '/jos_modules_menu.csv');

		return $dataSet;
	}

	/**
	 * Test JDocumentRendererHtmlModules::render
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function testRender()
	{
		$renderer               = new JDocumentRendererHtmlModules(new JDocumentHtml);
		$params                 = ['name' => 'position-0', 'style' => 'xhtml'];
		$this->callbackExecuted = false;
		$output                 = $renderer->render('position-0', $params);
		$htmlClean              = trim(preg_replace('~>\s+<~', '><', $output));
		$this->assertTrue($this->callbackExecuted, 'onAfterRenderModules event is not executed');
		$html = '<div class="moduletable"><h3>Search</h3><div class="search">'
			. '<form action="index.php" method="post">'
			. '<input name="searchword" id="mod-search-searchword63" class="form-control" type="search" placeholder="Search ...">'
			. '<input type="hidden" name="task" value="search"><input type="hidden" name="option" value="com_search">'
			. '<input type="hidden" name="Itemid" value=""></form></div></div>';
		$this->assertEquals($html, $htmlClean, 'render output does not match expected content');
	}

	/**
	 * Callback for the DispatcherInterface trigger method.
	 *
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function eventCallback($event, array $args = [])
	{
		switch ($event)
		{
			case 'onAfterRenderModules':
				$this->assertContains('mod-search-searchword63', $args[0], 'buffer empty when processing onAfterRenderModules event');
				$this->assertArrayHasKey('name', $args[1], "params['name'] empty when processing onAfterRenderModules event");
				$this->callbackExecuted = true;
		}
	}
}

