<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to menu list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class MenuListPage extends AdminListPage
{
    /**
     * Url to menu page.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $url = "administrator/index.php?option=com_menus&view=menus";

    /**
     * Page title of the menu page.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $pageTitleText = 'Menus';
}
