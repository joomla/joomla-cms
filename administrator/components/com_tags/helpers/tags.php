<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @since       3.1
 */
class TagsHelper extends JHelperContent
{
	/**
	 * Configure the Submenu links.
	 * Note that tags does not have submenus at this point.
	 *
	 * @param   string  The extension.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($extension)
	{
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $tagId  The tag ID.
	 *
	 * @return  JObject
	 *
	 * @since   3.1
	 */
	public static function getActions($categoryId = 0, $id = 0, $assetName = 0)
	{
		$assetName = 'com_tags';
		$level     = 'component';

		$actions   = JAccess::getActions('com_tags', $level);

		return parent::getActions($categoryId, $id, $assetName);

	}
}
