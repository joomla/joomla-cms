<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class InstallCest
{
	/**
	 * Install Joomla, disable statistics and enable Error Reporting
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function installJoomla(\AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->installJoomlaRemovingInstallationFolder();
		$I->doAdministratorLogin();
		$I->disableStatistics();
		$I->setErrorReportingToDevelopment();
	}
}
