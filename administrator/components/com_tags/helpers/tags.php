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
 * @since       3.1
 */
class TagsHelper extends JHelperContent
{
	/**
	 * Configure the Submenu links.
	 *
	 * @param   string  The extension.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($extension)
	{
		$parts = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		// Try to find the component helper.
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_tags/helpers/tags.php');

	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @since   3.1
	 */
	public static function getActions($categoryId = 0, $id = 0,  $assetName = '')
	{
		$assetName = 'com_tags';
		$level     = 'component';
		$actions   = JAccess::getActions('com_tags', $level);

		$actions = JAccess::getActions('com_newsfeeds', $level);

		return parent::getActions($categoryId, $id, $assetName);
	}
}
