<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Loads the step object, check /_steps/ folder and see: http://codeception.com/docs/07-AdvancedUsage#StepObjects
$I = new AcceptanceTester\JoomlaInstallationSteps($scenario);

$I->wantTo('Install Joomla CMS');

// This _step object method ensures that joomla is not already installed by removing configuration.php
$I->removeConfigFile();

// I navigate to a page, the value of the page is located at test/_pages/joomla/installation/installjoomlaconfigurationPage
$I->amOnPage(JoomlaInstallationConfigurationPage::$URL);

// I Wait for the text Main Configuration, meaning that the page is loaded
$I->waitForText('Main Configuration', 10, 'h3');

// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
$cfg = $I->getConfig();

$I->click(JoomlaInstallationConfigurationPage::$elements['Language Selector']);
$I->click(JoomlaInstallationConfigurationPage::$elements['English (United Kingdom)']);
$I->fillField('Site Name', 'Joomla CMS test');
$I->fillField('Description', 'Site for testing Joomla CMS');
$I->fillField('Admin Email', $cfg['Admin email']);
$I->fillField('Admin Username', $cfg['username']);
$I->fillField('Admin Password', $cfg['password']);
$I->fillField('Confirm Admin Password', $cfg['password']);
$I->click('Next');

$I->wantTo('Fill the form for creating the Joomla site Database');
$I->waitForText('Database Configuration', 10, 'h3');
// I instance the Install Joomla Database Page
$I->selectOption(JoomlaInstallationDatabasePage::$elements['Database Type'], strtolower($cfg['Database Type']));
$I->fillField('Host Name', $cfg['Database Host']);
$I->fillField('Username', $cfg['Database User']);
$I->fillField('Password', $cfg['Database Password']);
$I->fillField('Database Name', $cfg['Database Name']);
$I->fillField('Table Prefix', $cfg['Database Prefix']);
$I->click(JoomlaInstallationDatabasePage::$elements['Remove Old Database button']);
$I->click('Next');

$I->wantTo('Fill the form for creating the Joomla site database');
$I->waitForText('Finalisation', 10, 'h3');
$I->selectOption(JoomlaInstallationOverviewPage::$elements['Sample Data'], JoomlaInstallationOverviewPage::$elements['No sample Data']);
$I->click('Install');

// Wait while Joomla installs
$I->waitForText("Congratulations! Joomla! is now installed.", 30);
