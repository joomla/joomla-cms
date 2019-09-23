<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
		$I->doAdministratorLogin();
		$I->disableStatistics();
		$I->setErrorReportingToDevelopment();
	}

	/**
	 * Sets Joomla System Debug Mode On.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function turnOnDebugMode(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->doAdministratorLogin();

		$this->debug('I open Joomla Global Configuration Page');
		$I->amOnPage('/administrator/index.php?option=com_config');
		$this->debug('I wait for Global Configuration title');
		$I->waitForText('Global Configuration', TIMEOUT, ['css' => '.page-title']);
		$this->debug('I open the Server Tab');
		// TODO improve
		$I->wait(2);
		$I->click('System','//a[@href="#page-system"]');
		$this->debug('I wait for debug switcher element');
		$I->click(['id' => 'jform_debug1']);
		$I->fail('force fail to see screen now');


		//		$this->debug('I click on save');
//		$this->clickToolbarButton('save');
//		$this->debug('I wait for global configuration being saved');
//		$this->waitForText('Global Configuration', TIMEOUT, ['css' => '.page-title']);
//		$this->see('Configuration saved.', ['id' => 'system-message-container']);
	}

	/**
	 * Print debug message to the screen.
	 *
	 * @param $message
	 */
	protected function debug($message)
	{
		\codecept_debug($message);
	}
}
