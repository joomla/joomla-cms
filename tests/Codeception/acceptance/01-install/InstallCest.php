<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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
        $I->installJoomla();
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
