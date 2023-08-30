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
 * Acceptance Page object class for Field list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class FieldListPage extends AdminListPage
{
    /**
     * Link to the article listing page.
     *
     * @var    string
     * @since  4.0.0
     */
    public static $url = "/administrator/index.php?option=com_fields&view=fields&context=com_content.article";

    /**
     * Locator for Fields publish icon.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $seePublished = ['xpath' => "//table[@id='fieldList']//*//span[@class='icon-check']"];

    public static $titleField = "#jform_title";

    public static $fieldType = '#jform_type';

    public static $successMessage = 'Field saved';

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
}
