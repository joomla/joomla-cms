<?php
/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance\Administrator;

use AcceptanceTester;
use Exception;
use Page\Acceptance\Administrator\AdminPage;

/**
 * Acceptance Step object class for admin steps.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Admin extends AcceptanceTester
{
	/**
	 * Method to confirm system message appear.
	 *
	 * @param   string  $text     The text of message
	 * @param   int     $timeout  Number of seconds to wait
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function seeSystemMessage($text, $timeout = TIMEOUT)
	{
		$I = $this;
		$I->waitForText($text, $timeout, AdminPage::$systemMessageContainer);
		$I->see($text, AdminPage::$systemMessageContainer);
	}
}
