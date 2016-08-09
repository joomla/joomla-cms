<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define category manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    3.7
 */
class CategoryManagerPage extends AdminPage
{
	/**
	 * Link to the article category listing url.
	 *
	 * @var    string
	 * @since  3.7
	 */
	public static $url = '/administrator/index.php?option=com_categories&view=categories&extension=com_content';

	/**
	 * Locator for category name field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $name = ['id' => 'jform_title'];

	/**
	 * Locator for category search field
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $filterSearch = ['id' => 'filter_search'];

	/**
	 * Locator for category search button
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $iconSearch = ['class' => 'icon-search'];

	/**
	 * Locator for invalid category alert
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $invalidTitle = ['class' => 'alert-error'];
}
