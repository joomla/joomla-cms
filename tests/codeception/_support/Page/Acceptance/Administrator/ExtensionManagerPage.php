<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define Control Panel page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class ExtensionManagerPage extends AdminPage
{
	/**
	 * Link to the administrator extension Installer url.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "/administrator/index.php?option=com_installer";

	/**
	 * Link to the administrator extension Manager url.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $manageUrl = "/administrator/index.php?option=com_installer&view=manage";

	public static $managePageTitle = "Extensions: Manage";

	public static $updateUrl = "/administrator/index.php?option=com_installer&view=update";

	public static $updatePageTitle = "Extensions: Update";

	public static $updateExtensionButton = ['xpath' => "//div[@id='toolbar-upload']/button"];

	/**
	 * Name of the text to identify the control panel.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $pageTitle = 'Extensions: Install';

	/**
	 * Address of the field to Install URL.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $installUrlField = ['link' => 'Install from URL'];

	/**
	 * Installs a Extension in Joomla that is located in a url
	 *
	 * @param   String  $url   Url address to the .zip file
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 */
	public function installExtensionFromUrl($url, $type = 'Extension')
	{
		$I = $this;
		$I->click(ExtensionManagerPage::$installUrlField);
		$I->comment('I enter the url');
		$I->fillField(['id' => 'install_url'], $url);
		$I->click(['id' => 'installbutton_url']);
		$I->seeSystemMessage(ExtensionManagerPage::$pageTitle, 'was successful');

		if ($type == 'Extension')
		{
			$this->comment('Extension successfully installed from ' . $url);
		}

		if ($type == 'Plugin')
		{
			$this->comment('Installing plugin was successful.' . $url);
		}

		if ($type == 'Package')
		{
			$this->comment('Installation of the package was successful.' . $url);
		}
	}

	public function uninstallExtension($extensionName)
	{
		$I = $this;
		$I->amOnPage(self::$manageUrl);
		$I->waitForText(self::$managePageTitle, '30', ['css' => 'H1']);
		$I->searchForItem($extensionName);
		$I->waitForElement(['id' => 'manageList'], '30');
		$I->click(['xpath' => "//input[@id='cb0']"]);
		$I->click(['xpath' => "//div[@id='toolbar-delete']/button"]);
		$I->acceptPopup();
		$I->wait(3);
		$I->seeSystemMessage(self::$managePageTitle, 'was successful');
		$I->searchForItem($extensionName);
		$I->waitForText(
			'There are no extensions installed matching your query.',
			60,
			['class' => 'alert-no-items']
		);
		$I->see('There are no extensions installed matching your query.', ['class' => 'alert-no-items']);
		$this->comment('Extension successfully uninstalled');
	}
}