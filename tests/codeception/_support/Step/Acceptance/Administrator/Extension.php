<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\ExtensionManagerPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Extension extends Admin
{
	/**
	 * @Given There is an extension install text
	 */
	public function thereIsAnExtensionInstallText()
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$url);
		$I->waitForText(ExtensionManagerPage::$pageTitle, 30, ['css' => 'H1']);
	}

	/**
	 * @When I install :arg1 extension
	 */
	public function iInstallExtension($arg1)
	{
		$I = $this;
		$url = 'https://github.com/joomla-extensions/weblinks/releases/download/3.4.1/pkg_weblinks_3.4.1.zip';
		$I->extensionManagerPage->installExtensionFromUrl($url);
	}

	/**
	 * @Then I should see the extension :arg1 is installed
	 */
	public function iShouldSeeTheExtensionIsInstalled($arg1)
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$manageUrl);
		$I->waitForText(ExtensionManagerPage::$managePageTitle, 30, ['css' => 'H1']);
		$I->searchForItem('Weblinks Extension Package');
		$I->checkExistenceOf('Weblinks Extension Package');
	}

	/**
	 * @Given There is an extension :arg1 installed with update available
	 */
	public function thereIsAnExtensionInstalledWithUpdateAvailable($arg1)
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$updateUrl);
		$I->waitForText(ExtensionManagerPage::$updatePageTitle, 30, ['css' => 'H1']);
		$I->click('Find Updates');
		$I->checkExistenceOf('Weblinks Extension Package');
	}

	/**
	 * @When I install extension update
	 */
	public function iInstallExtensionUpdate()
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$updateUrl);
		$I->checkAllResults();
		$I->click(ExtensionManagerPage::$updateExtensionButton);
	}

	/**
	 * @Then I should see the extension :arg1 is updated
	 */
	public function iShouldSeeTheExtensionIsUpdated($arg1)
	{
		$I = $this;
		$I->iShouldSeeTheMessage('Updating package was successful');
	}

	/**
	 * @Given There is an extension :arg1 installed
	 */
	public function thereIsAnExtensionInstalled($arg1)
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$manageUrl);
		$I->waitForText(ExtensionManagerPage::$managePageTitle, 30, ['css' => 'H1']);
		$I->searchForItem($arg1);
		$I->checkExistenceOf($arg1);
	}

	/**
	 * @When I uninstall the extension
	 */
	public function iUninstallTheExtension()
	{
		$I = $this;
		$I->amOnPage(ExtensionManagerPage::$manageUrl);
		$I->waitForText(ExtensionManagerPage::$managePageTitle, 30, ['css' => 'H1']);
		$I->extensionManagerPage->uninstallExtension('Weblinks Extension Package');
		$I->searchForItem('Weblinks Extension Package');
	}

	/**
	 * @Then I should see the extension :arg1 is uninstalled
	 */
	public function iShouldSeeTheExtensionIsUninstalled($arg1)
	{
		$I = $this;
		$I->see('There are no extensions installed matching your query.', ['class' => 'alert-no-items']);
	}


}