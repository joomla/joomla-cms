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
 * Acceptance Page object class for article list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class ContentListPage extends AdminListPage
{
    /**
     * Link to the article listing page.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $url = "/administrator/index.php?option=com_content&view=articles";

    /**
     * Drop Down Toggle Element.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $dropDownToggle = ['xpath' => "//button[contains(@class, 'dropdown-toggle')]"];

    /**
     * Page object for content body editor field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $content = ['id' => 'jform_articletext'];

    /**
     * Page object for the toggle button.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $toggleEditor = "Toggle editor";

    /**
     * Locator for article's name field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $seeName = ['xpath' => "//table[@id='articleList']//tr[1]//td[4]"];

    /**
     * Locator for article's featured icon.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $seeFeatured = ['xpath' => "//table[@id='articleList']//*//span[@class='icon-star']"];

    /**
     * Locator for article's name field.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $seeAccessLevel = ['xpath' => "//table[@id='articleList']//tr[1]//td[5]"];

    /**
     * Locator for article's unpublish icon.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $seeUnpublished = ['xpath' => "//table[@id='articleList']//*//span[@class='icon-unpublish']"];

    public static $articleTitleField = [ 'id' => "jform_title" ];

    public static $articleAliasField = [ 'id' => "jform_alias" ];

    public static $articleSearchField = [ 'id' => "filter_search" ];

    public static $searchButton = [ 'xpath' => "//button[@aria-label='Search']" ];

    public static $systemMessageAlertClose = ['class' => "joomla-alert--close"];
}
