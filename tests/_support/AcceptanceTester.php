<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Acceptance Tester global class for entry point
 *
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester
 * @since       __DEPLOY_VERSION__
 *
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	/**
	 * Function to check for PHP Notices or Warnings
	 *
	 * @param   string  $page  Optional, if not given checks will be done in the current page
	 *
	 * @note    :   doAdminLogin() before
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		$I = $this;

		if ($page)
		{
			$I->amOnPage($page);
		}

		$I->dontSeeInPageSource('Notice:');
		$I->dontSeeInPageSource('<b>Notice</b>:');
		$I->dontSeeInPageSource('Warning:');
		$I->dontSeeInPageSource('<b>Warning</b>:');
		$I->dontSeeInPageSource('Strict standards:');
		$I->dontSeeInPageSource('<b>Strict standards</b>:');
		$I->dontSeeInPageSource('The requested page can\'t be found');
	}
}
