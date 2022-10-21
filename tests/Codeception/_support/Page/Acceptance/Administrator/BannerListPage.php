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
 * Acceptance Page object class for banner list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class BannerListPage extends AdminListPage
{
    /**
     * Locator for Url location.
     *
     * @var string
     *
     * @since    4.0.0
     */
    public static $url = "/administrator/index.php?option=com_banners";

    /**
     * Locator for Title field.
     *
     * @var array
     *
     * @since    4.0.0
     */
    public static $titleField = ['id' => 'jform_name'];

    /**
     * Locator for Alias field.
     *
     * @var array
     *
     * @since    4.0.0
     */
    public static $aliasField = ['id' => 'jform_alias'];

    /**
     * Locator for Search field.
     *
     * @var array
     *
     * @since    4.0.0
     */
    public static $searchField = ['id' => 'filter_search'];

    /**
     * Locator for Search button class.
     *
     * @var array
     *
     * @since    4.0.0
     */
    public static $searchButton = ['class' => 'icon-search'];

    /**
     * Locator for Search button tile.
     *
     * @var array
     *
     * @since    4.0.0
     */
    public static $searchToolButton = ['css' => 'button[data-original-title="Filter the list items."]'];
}
