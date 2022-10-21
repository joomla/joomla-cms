<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:ignoreFile
use Codeception\Actor;
use Codeception\Lib\Friend;

/**
 * Acceptance Tester global class for entry point.
 *
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 * @method getConfig(string $string)
 *
 * @SuppressWarnings(PHPMD)
 *
 * @since  3.7.3
 */
class AcceptanceTester extends Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Function to check for PHP Notices or Warnings.
     *
     * @param   string  $page  Optional, if not given checks will be done in the current page
     *
     * @note    doAdminLogin() before
     *
     * @since   3.7.3
     *
     * @return  void
     */
    public function checkForPhpNoticesOrWarnings($page = null)
    {
        $I = $this;

        if ($page) {
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

    /**
     * Function to wait for JS to be properly loaded on page change.
     *
     * @param   integer|float  $timeout  Time to wait for JS to be ready
     *
     * @since   4.0.0
     *
     * @return  void
     */
    public function waitForJsOnPageLoad($timeout = 1)
    {
        $I = $this;

        $I->waitForJS('return document.readyState == "complete"', $timeout);

        // Wait an additional 500ms to make sure that really all JS is loaded
        $I->wait(0.5);
    }
}
