<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Update Joomla 3.10 to Joomla 4 Tests
 *
 * @since  __DEPLOY_VERSION__
 */
class Updateto4Cest
{
	/**
	 * Update Joomla 3.10 to next major
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function updateToJoomla4(\AcceptanceTester $I)
	{
		$I->comment('I am going to test 3.10 to 4.0 nightly updates');
		$I->doAdministratorLogin();
		$I->amOnPage('administrator/index.php?option=com_joomlaupdate');
		$I->checkForPhpNoticesOrWarnings();
		$I->wait(2);
		$I->click('#toolbar-options');
		$I->waitForText('Joomla Update: Options');
		$I->wait(2);
		$I->click('#jform_updatesource_chzn');
		$I->wait(2);
		$I->selectOptionInChosenByIdUsingJs('jform_updatesource', 'Custom URL');
		$I->wait(2);
		$I->fillField('#jform_customurl', 'https://update.joomla.org/core/nightlies/next_major_list.xml');
		$I->clickToolbarButton('Save & Close');
		$I->waitForText('Joomla Update');
		$I->wait(2);
		$I->click('Live Update');
		$I->wait(2);
		$I->click('Install the Update');
		$I->waitForText('Joomla Version Update Status', 60);
		$I->checkForPhpNoticesOrWarnings();
		$I->see('Your site has been updated.');
	}
}
