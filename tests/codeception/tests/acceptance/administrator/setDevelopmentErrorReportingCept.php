<?php
/**
 * @package     joomla.Frontend
 * @subpackage  
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// Loads the step object, check /_steps/ folder and see: http://codeception.com/docs/07-AdvancedUsage#StepObjects
$I = new AcceptanceTester\LoginSteps($scenario);


// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
$I->wantTo('set Joomla Error Reporting to Development');

// The following command is using a method defined in the Step Object (see _steps/loginsteps.php)
$I->doAdministratorLogin($I->getConfiguration('Username'), $I->getConfiguration('Password'));

$I->amOnPage('/administrator/index.php?option=com_config');

$globalConfiguration =  \GlobalconfigurationPage::$elements;
$I->waitForText('Global Configuration',10,'.page-title');
$I->click('Server');
$I->waitForElementVisible($globalConfiguration['Error Reporting Dropdown']);
$I->click($globalConfiguration['Error Reporting Dropdown']);
$I->click($globalConfiguration['option: Development']);
$I->click('Save');
$I->waitForText('Global Configuration',10,'.page-title');
$I->see('Configuration successfully saved.','#system-message-container');