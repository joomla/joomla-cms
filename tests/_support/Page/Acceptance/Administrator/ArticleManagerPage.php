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
 * Acceptance Page object class to define Content Manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    3.7
 */
class ArticleManagerPage extends AdminPage
{
	/**
	 * Page object for article title field.
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $title = ['id' => 'jform_title'];

	/**
	 * Page object for content body editor field.
	 *
	 * @var array
	 * @since version
	 */
	public static $content = ['id' => 'jform_articletext'];

	/**
	 * Page object for the toggle button.
	 *
	 * @var    string
	 * @since  3.7
	 */
	public static $toggleEditor = "Toggle editor";

	/**
	 * Page object for search filter element of article listing page.
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $filterSearch = ['id' => 'filter_search'];

	/**
	 * Page object for search icon button of article listing page.
	 *
	 * @var    array
	 * @since  3.7
	 */
	public static $iconSearch = ['class' => 'icon-search'];

	/**
	 * Link to the article listing page.
	 *
	 * @var    string
	 * @since  3.7
	 */
	public static $url = "/administrator/index.php?option=com_content&view=articles";
}
