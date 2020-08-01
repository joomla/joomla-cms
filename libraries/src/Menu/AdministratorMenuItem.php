<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

\defined('JPATH_PLATFORM') or die;

/**
 * Object representing an administrator menu item
 *
 * @since  4.0.0
 */
class AdministratorMenuItem extends MenuItem
{
	/**
	 * The target attribute of the link
	 *
	 * @var    string|null
	 * @since  4.0.0
	 */
	public $target;

	/**
	 * The icon image of the menu item
	 *
	 * @var    string|null
	 * @since  4.0.0
	 */
	public $icon;

	/**
	 * The icon image of the link
	 *
	 * @var    string|null
	 * @since  4.0.0
	 */
	public $iconImage;
}
