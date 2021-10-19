<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\MenuFormPage;
use Page\Acceptance\Administrator\MenuListPage;

/**
 * Administrator Menu Tests
 *
 * @since  4.0.0
 */
class MenuCest
{
	/**
	 * Create a menu.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 *
	 * @throws Exception
	 */
	public function createNewMenu(AcceptanceTester $I)
	{
		$I->comment('I am going to create a menu');
		$I->doAdministratorLogin();

		$I->amOnPage(MenuListPage::$url);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForText(MenuListPage::$pageTitleText);
		$I->waitForJsOnPageLoad();

		$I->clickToolbarButton('new');
		$I->waitForText(MenuFormPage::$pageTitleText);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForJsOnPageLoad();

		$this->fillMenuInformation($I, 'Test Menu');

		$I->clickToolbarButton('save');
		$I->waitForText(MenuListPage::$pageTitleText);
		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Fill out the menu information form.
	 *
	 * @param   AcceptanceTester  $I            The AcceptanceTester Object
	 * @param   string            $title        Title
	 * @param   string            $type         Type of the menu
	 * @param   string            $description  Description
	 *
	 * @since  4.0.0
	 *
	 * @return  void
	 */
	protected function fillMenuInformation($I, $title, $type = 'Test', $description = 'Automated Testing')
	{
		$I->fillField(MenuFormPage::$fieldTitle, $title);
		$I->fillField(MenuFormPage::$fieldMenuType, $type);
		$I->fillField(MenuFormPage::$fieldMenuDescription, $description);
	}
}
