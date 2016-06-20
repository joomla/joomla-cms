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
 * This class tests the  Control panel.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class ControlPanel0001Test extends JoomlaWebdriverTestCase
{

	/**
	 * do admin login and check for icons
	 *
	 * @return void
	 *
	 * @test
	 */
	public function doAdminLogin_NormalLogin_LogsIntoBackEnd()
	{
		$cpPage = $this->doAdminLogin();
		$this->assertTrue(is_a($cpPage, 'ControlPanelPage'));
		$visibleMenus = $cpPage->getVisibleMenuLinks();
		$actualVisibleMenus = array();
		foreach($visibleMenus as $menu)
		{
			$actualVisibleMenus[] = $menu->text;
		}
		$this->assertEquals($cpPage->visibleMenuText, $actualVisibleMenus);

		// Check Icons
		$icons = $cpPage->getControlPanelIcons();
		foreach ($icons as $icon)
		{
			$iconArray[$icon->text] = $icon->href;
		}

		foreach ($cpPage->expectedIconArray as $iconText => $href)
		{
			$this->assertArrayHasKey($iconText, $iconArray);
			$this->assertStringStartsWith($this->testUrl . $href, $iconArray[$iconText]);
		}

		// Test All Menu Link Values
		$allMenus = $cpPage->getAllMenuLinks();
		$actualLinks = '';
		foreach ($allMenus as $object)
		{
			$actualLinks .= $object->href . "\n";
		}

		foreach ($cpPage->allMenuLinks as $menuText => $link)
		{
			$link = (substr($link, 0, 7) == 'http://' || substr($link, 0, 8) == 'https://' ) ? $link : $this->cfg->host . $this->cfg->path . $link;
			$this->assertTrue(strpos($actualLinks, $link) !== false, "Expected link $link should be in on the page.");
		}

		$this->doAdminLogout();
	}

	/**
	 * check the menu links
	 *
	 * @return void
	 *
	 * @test
	 */
	public function clickMenu_LoadBackendMenuLinks_LinksShouldLoad()
	{
		$cpPage = $this->doAdminLogin();
		$testPage = $cpPage->clickMenu('Global Configuration', 'GenericAdminPage');

		// These pages are skipped because they don't have a help icon, which is used by the GenericAdminPage.
		// As we create page classes for each screen, we could check to see if the page class exists and use that instead of the generic.
		
		$skipPages = array('Control Panel', 'Edit Account', 'Logout', 'Joomla! Update', 'Joomla Help', 'Post-installation Messages');
		foreach ($cpPage->allMenuLinks as $menuText => $link)
		{
			if (strpos($link, 'http') !== 0 && (array_search($menuText, $skipPages) === false) && ($link != 'administrator/index.php#'))
			{
				$testPage = $testPage->clickMenu($menuText, 'GenericAdminPage');
			}
		}
		$this->doAdminLogout();
	}
}
