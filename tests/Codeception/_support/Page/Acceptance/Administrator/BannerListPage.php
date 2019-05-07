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
 * Acceptance Page object class for banner list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class BannerListPage extends AdminListPage
{
	public static $url = "/administrator/index.php?option=com_banners";

	public static $titleField = ['id' => 'jform_name'];

	public static $aliasField = ['id' => 'jform_alias'];

	public static $searchField = ['id' => 'filter_search'];

	public static $searchButton = ['class' => 'icon-search'];

	public static $searchToolButton = ['css' => 'button[data-original-title="Filter the list items."]'];
}
