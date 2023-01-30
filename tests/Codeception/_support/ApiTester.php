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
 * @method Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 *
 * @since    4.0.0
 */
class ApiTester extends Actor
{
    use _generated\ApiTesterActions;

    /**
     * Define custom actions here
     */
}
