<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$I = new \AcceptanceTester($scenario);

$I->wantTo('Install Joomla CMS');
$I->expect('no configuration.php is in the Joomla CMS folder');
$I->dontSeeFileFound('configuration.php', $I->getConfiguration('Joomla folder'));

$I->amOnPage('/installation/index.php');

// I Wait for the text Main Configuration, meaning that the page is loaded
$I->waitForText('Main Configuration',10,'h3');

// I instantiate the Installation Configuration Page Elements:
$configurationPage = \JoomlaInstallationConfigurationPage::$elements;
$I->click($configurationPage['Language Selector']);
$I->click($configurationPage[$I->getConfiguration('Language')]);
$I->fillField('Site Name','Joomla CMS test');
$I->fillField('Description','Site for testing Joomla CMS');
// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
$I->fillField('Admin Email',$I->getConfiguration('Admin email'));
$I->fillField('Admin Username',$I->getConfiguration('Username'));
$I->fillField('Admin Password',$I->getConfiguration('Password'));
$I->fillField('Confirm Admin Password',$I->getConfiguration('Password'));
$I->click($configurationPage['No Site Offline']);
$I->click('Next');

$I->wantTo('Fill the form for creating the Joomla site Database');
$I->waitForText('Database Configuration', 10, 'h3');
// I instance the Install Joomla Database Page
$databasePage = \JoomlaInstallationDatabasePage::$elements;
$I->selectOption($databasePage['Database Type'], $I->getConfiguration('Database Type'));
$I->fillField('Host Name',$I->getConfiguration('Database Host'));
$I->fillField('Username',$I->getConfiguration('Database User'));
$I->fillField('Password',$I->getConfiguration('Database Password'));
$I->fillField('Database Name',$I->getConfiguration('Database Name'));
$I->fillField('Table Prefix',$I->getConfiguration('Database Prefix'));
$I->click($databasePage['Remove Old Database button']);
$I->click('Next');

$I->wantTo('Fill the form for creating the Joomla site database');
$I->waitForText('Finalisation', 10, 'h3');
$overviewPage = \JoomlaInstallationOverviewPage::$elements;

if ($I->getConfiguration('Install Sample Data')) :
	$I->selectOption($overviewPage['Sample Data'], $I->getConfiguration('Sample Data'));
else :
	$I->selectOption($overviewPage['Sample Data'], $overviewPage['No sample Data']);
endif;

$I->click('Install');

// Wait while Joomla gets installed
$I->waitForText('Congratulations! Joomla! is now installed.',30,'h3');
