<?php
/**
 * @package     joomla.Frontend
 * @subpackage  
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// Loads the step object, check /_steps/ folder and see: http://codeception.com/docs/07-AdvancedUsage#StepObjects
$I = new AcceptanceTester\LoginSteps($scenario);
$I->wantTo('Execute Log in at Joomla Administrator');

// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
$cfg = $I->getConfig();

// The following command is using a method defined in the Step Object
$I->doAdministratorLogin($cfg['username'], $cfg['password']);

//$I = new AcceptanceTester\GlobalConfigurationSteps($scenario);
$I->wantTo('Set Error Reporting Level');
$I->amOnPage(\GlobalconfigurationPage::$URL);

$globalConfiguration =  \GlobalconfigurationPage::$elements;
$I->waitForText('Global Configuration', 10, '.page-title');
$I->click($globalConfiguration['Server Tab']);
$I->waitForElement($globalConfiguration['Error Reporting Dropdown']);
$I->click($globalConfiguration['Error Reporting Dropdown']);
$I->click($globalConfiguration['option: Development']);
$I->click($globalConfiguration['Save']);
$I->waitForText('Global Configuration', 10, '.page-title');
$I->see('Configuration successfully saved.', $globalConfiguration['Success Message Locator'] );