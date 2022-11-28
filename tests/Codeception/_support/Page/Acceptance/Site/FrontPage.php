<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Site;

/**
 * Acceptance Page object class to define Frontend page objects.
 *
 * @package  Page\Acceptance\Site
 *
 * @since    3.7.3
 */
class FrontPage extends \AcceptanceTester
{
    /**
     * Link to the frontend.
     *
     * @var    string
     * @since  3.7.3
     */
    public static $url = '/';

    /**
     * Locator for alert message in frontend.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $alertMessage = ['class' => 'alert-message'];

    /**
     * Locator for login greeting for the user.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $loginGreeting = ['class' => 'login-greeting'];
}
