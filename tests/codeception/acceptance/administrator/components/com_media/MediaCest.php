<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\MediaManagerPage as Page;

/**
 * Media Manager Tests
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaCest
{
	/**
	 * Check Media Manager Overview
	 *
	 * @param   AcceptanceTester  $I  Acceptance Helper Object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkMediaManagerOverview(\AcceptanceTester $I)
	{
		$I->comment('I am going to check the media manager overview');
		$I->doAdministratorLogin();

		$I->amOnPage(Page::$url);

		$I->waitForText(Page::$pageTitleText);
		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Select image and check the information
	 *
	 * @param   AcceptanceTester  $I  Acceptance Helper Object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function selectImageAndCheckTheInformation(\AcceptanceTester $I)
	{
		$I->comment('I am going to test the media manager overview information method');
		$I->doAdministratorLogin();
		$I->amOnPage(Page::$url);

		$I->waitForText(Page::$pageTitleText);

		$I->click(Page::$poweredByImage);

		$I->click(Page::$buttonInfo);
		$I->checkForPhpNoticesOrWarnings();

		$I->see('image/png');
	}
}
