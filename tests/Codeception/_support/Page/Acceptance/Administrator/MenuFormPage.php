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
 * Acceptance Page object for menu form pages.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class MenuFormPage extends AdminFormPage
{
    /**
     * Url to menu create page.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $url = "administrator/index.php?option=com_menus&view=menu&layout=edit";

    /**
     * Page title for adding a menu.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $pageTitleText = 'Menus: Add';

    /**
     * Title field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $fieldTitle = ['id' => 'jform_title'];

    /**
     * Type field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $fieldMenuType = ['id' => 'jform_menutype'];

    /**
     * Optional Description field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $fieldMenuDescription = ['id' => 'jform_menudescription'];
}
