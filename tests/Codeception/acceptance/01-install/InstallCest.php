<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Install Joomla
 *
 * @since  3.7.3
 */
class InstallCest
{
	/**
	 * Sets in Administrator->Global Configuration the Error reporting to Maximum (formerly development)
	 * {@internal doAdminLogin() before}
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function setErrorReportingToDevelopment(AcceptanceTester $I)
	{
		$I->debug('I open Joomla Global Configuration Page');
		$I->amOnPage('/administrator/index.php?option=com_config');
		$I->debug('I wait for Global Configuration title');
		$I->waitForText('Global Configuration', 1, ['css' => '.page-title']);
		$I->debug('I open the Server Tab');

		// TODO improve
		$I->wait(1);
		$I->click(['button' => 'Server']);
		$I->debug('I wait for error reporting dropdown');
		$I->selectOption('Error Reporting', 'Maximum');
		$I->debug('I click on save');
		$I->clickToolbarButton('save');
		$I->debug('I wait for global configuration being saved');
		$I->waitForText('Global Configuration', 1, ['css' => '.page-title']);
		$I->waitForText('Configuration saved.', 1, ['id' => 'system-message-container']);
	}

	/**
	 * Install Joomla, disable statistics and enable Error Reporting.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   3.7.3
	 *
	 * @return  void
	 */
	public function installJoomla(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->installJoomlaRemovingInstallationFolder();
	}

	/**
	 * Disables the statistics and sets error reporting to development.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function configureJoomla(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->doAdministratorLogin(null, null, false);
		$I->disableStatistics();
		$I->setErrorReportingToDevelopment();
	}
}
