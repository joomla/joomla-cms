<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Codeception\Scenario;
use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\ArticleManagerPage;
use Page\Acceptance\Administrator\UserManagerPage;
use Page\Acceptance\Administrator\CategoryManagerPage;

/**
 * Acceptance Step object class for admin steps.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Admin extends \AcceptanceTester
{
	/**
	 * Method to confirm system message appear
	 *
	 * @param   string  $text     The text of message
	 * @param   int     $timeout  Number of seconds to wait
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function seeSystemMessage($text, $timeout = TIMEOUT)
	{
		$I = $this;
		$I->waitForText($text, $timeout, AdminPage::$systemMessageContainer);
		$I->see($text, AdminPage::$systemMessageContainer);
	}
}
