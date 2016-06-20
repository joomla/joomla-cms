<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Plugin Manager: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class PluginManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     pluginManagerPage
	 * @since   3.0
	 */
	protected $pluginManagerPage = null;

	/**
	 * Login to back end and navigate to menu .
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->pluginManagerPage = $cpPage->clickMenu('Plugin Manager', 'PluginManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * check plugin edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_PluginEditOpened()
	{
		$test_plugin = 'Content - Joomla'; /*A test Plugin which we are going to select to open the edit page*/
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
		$this->pluginManagerPage->clickItem($test_plugin);
		$pluginEditPage = $this->getPageObject('PluginEditPage');
		$pluginEditPage->clickButton('cancel');
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
	}

	/**
	 * check tab Ids
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$test_plugin = 'Content - Joomla'; /*A test Plugin which we are going to select to open the edit page*/
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
		$this->pluginManagerPage->clickItem($test_plugin);
		$pluginEditPage = $this->getPageObject('PluginEditPage');
		$textArray = $pluginEditPage->getTabIds();
		$this->assertEquals($pluginEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$pluginEditPage->clickButton('toolbar-cancel');
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
	}

	/**
	 * check the available plugin types
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getPluginTypes_GetsTypes_EqualsExpected()
	{
		$test_plugin = 'Content - Joomla'; /*A test Plugin whose type we are going to compare with the expected and actual value*/
		$expected_type = 'content';
		$actualPluginType = $this->pluginManagerPage->getPluginType($test_plugin);
		$this->assertEquals($expected_type, $actualPluginType, 'Plugin type should equal expected');
	}

	/**
	 * edit the plugin by changing the values of input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editPlugin_ChangeFields_FieldsChanged()
	{
		/*A test Plugin which we are going to edit, We are going to change the state and Access level for this.*/
		$test_plugin = 'Content - Joomla';
		$expected_type = 'content';
		$expected_state = 'published';
		$expected_access = 'Public';
		$new_pluginAccess = 'Guest';
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
		$this->pluginManagerPage->setFilter('filter_enabled', 'Disabled')->searchFor($test_plugin);
		$this->assertFalse($this->pluginManagerPage->getRowNumber($test_plugin), 'Test plugin should not be present');
		$this->pluginManagerPage->setFilter('filter_enabled', 'Enabled')->searchFor($test_plugin);
		$this->pluginManagerPage->changePluginState($test_plugin, 'unpublished');
		$message = $this->pluginManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Plugin successfully disabled') >= 0, 'Plugin state change should return success');
		$this->assertFalse($this->pluginManagerPage->getRowNumber($test_plugin), 'Test plugin should not be present');
		$this->pluginManagerPage->setFilter('filter_enabled', 'Disabled')->searchFor($test_plugin);
		$this->assertTrue($this->pluginManagerPage->getRowNumber($test_plugin) > 0, 'Test module should be present');
		$this->pluginManagerPage->changePluginState($test_plugin, 'published');
		$message = $this->pluginManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Plugin successfully enabled') >= 0, 'Plugin state change should return success');

		/*8editAccess level of a Plugin*/

		$this->pluginManagerPage->setFilter('filter_enabled', 'Enabled')->searchFor($test_plugin);
		$this->pluginManagerPage->editPlugin($test_plugin, array('Access' => 'Guest'));
		$actuall_pluginAccess = $this->pluginManagerPage->getPluginAccess($test_plugin);
		$this->assertEquals($actuall_pluginAccess, $new_pluginAccess, 'Plugin Access should equal expected');
		$this->pluginManagerPage->editPlugin($test_plugin, array('Access' => $expected_access));
		$actuall_pluginAccess = $this->pluginManagerPage->getPluginAccess($test_plugin);
		$this->assertEquals($actuall_pluginAccess, $expected_access, 'Plugin Access should equal expected');
	}

	/**
	 * change plugin state
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changePluginState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		/*A test Plugin which we are going to change the state.*/
		$test_plugin = 'Content - Joomla';
		$expected_state = 'published';
		$this->pluginManagerPage = $this->getPageObject('PluginManagerPage');
		$actuall_pluginState = $this->pluginManagerPage->getState($test_plugin);
		$this->assertEquals($actuall_pluginState, $expected_state, 'Plugin State should equal expected');
		$this->pluginManagerPage->changePluginState($test_plugin, 'unpublished');
		$message = $this->pluginManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Plugin successfully disabled') >= 0, 'Plugin state change should return success');
		$this->pluginManagerPage->setFilter('filter_enabled', 'Enabled');
		$this->assertFalse($this->pluginManagerPage->getRowNumber($test_plugin), 'Test plugin should not be present');
		$this->pluginManagerPage->setFilter('filter_enabled', 'Disabled')->searchFor($test_plugin);
		$this->assertTrue($this->pluginManagerPage->getRowNumber($test_plugin) > 0, 'Test module should be present');
		$this->pluginManagerPage->changePluginState($test_plugin, 'published');
		$message = $this->pluginManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Plugin successfully enabled') >= 0, 'Plugin state change should return success');
	}
}
