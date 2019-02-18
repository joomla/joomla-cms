<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Submenu\Administrator\Menu;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;

/**
 * Helper class to handle permissions in mod_submenu
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class Menu
{
	/**
	 * Filter and perform other preparatory tasks for loaded menu items based on access rights and module configurations for display
	 *
	 * @param   MenuItem  $parent  A menu item to process
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function preprocess($parent)
	{
		$app      = Factory::getApplication();
		$user     = $app->getIdentity();
		$children = $parent->getChildren();

		/**
		 * Trigger onPreprocessMenuItems for the current level of backend menu items.
		 * $children is an array of MenuItem objects. A plugin can traverse the whole tree,
		 * but new nodes will only be run through this method if their parents have not been processed yet.
		 */
		$app->triggerEvent('onPreprocessMenuItems', array('administrator.module.mod_submenu', $children));


		foreach ($children as $item)
		{
			// Exclude item with menu item option set to exclude from menu modules
			if ($item->permission)
			{
				@list($action, $asset) = explode(';', $item->permission);

				if (!$user->authorise($action, $asset))
				{
					$parent->removeChild($item);
					continue;
				}
			}

			if ($item->hasChildren())
			{
				self::preprocess($item);
			}
		}
	}
}
