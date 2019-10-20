<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Submenu\Administrator\Menu;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Utilities\ArrayHelper;


/**
 * Helper class to handle permissions in mod_submenu
 *
 * @since  4.0.0
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
	 * @since   4.0.0
	 */
	public static function preprocess($parent)
	{
		$app      = Factory::getApplication();
		$user     = $app->getIdentity();
		$children = $parent->getChildren();
		$language = Factory::getLanguage();

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

			// Populate automatic children for container items
			if ($item->type === 'container')
			{
				$exclude    = (array) $item->params->get('hideitems') ?: array();
				$components = MenusHelper::getMenuItems('main', false, $exclude);

				// We are adding the nodes first to preprocess them, then sort them and add them again.
				foreach ($components->getChildren() as $c)
				{
					if (!$c->hasChildren())
					{
						$temp = clone $c;
						$c->addChild($temp);
					}

					$item->addChild($c);
				}

				self::preprocess($item);
				$children = ArrayHelper::sortObjects($item->getChildren(), 'text', 1, false, true);

				foreach ($children as $c)
				{
					$parent->addChild($c);
				}

				$parent->removeChild($item);
				continue;
			}

			// Exclude Mass Mail if disabled in global configuration
			if ($item->scope === 'massmail' && ($app->get('massmailoff', 0) == 1))
			{
				$parent->removeChild($item);
				continue;
			}

			if ($item->element === 'com_fields')
			{
				parse_str($item->link, $query);

				// Only display Fields menus when enabled in the component
				$createFields = null;

				if (isset($query['context']))
				{
					$createFields = ComponentHelper::getParams(strstr($query['context'], '.', true))->get('custom_fields_enable', 1);
				}

				if (!$createFields)
				{
					$parent->removeChild($item);
					continue;
				}
			}
			elseif ($item->element === 'com_workflow')
			{
				parse_str($item->link, $query);

				// Only display Workflow menus when enabled in the component
				$workflow = null;

				if (isset($query['extension']))
				{
					$workflow = ComponentHelper::getParams($query['extension'])->get('workflows_enable', 1);
				}

				if (!$workflow)
				{
					$parent->removeChild($item);
					continue;
				}

				list($assetName) = isset($query['extension']) ? explode('.', $query['extension'], 2) : array('com_workflow');
			}
			// Special case for components which only allow super user access
			elseif (\in_array($item->element, array('com_config', 'com_privacy', 'com_actionlogs'), true) && !$user->authorise('core.admin'))
			{
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_joomlaupdate' && !$user->authorise('core.admin'))
			{
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_admin')
			{
				parse_str($item->link, $query);

				if (isset($query['view']) && $query['view'] === 'sysinfo' && !$user->authorise('core.admin'))
				{
					$parent->removeChild($item);
					continue;
				}
			}
			elseif ($item->element === 'com_menus')
			{
				// Get badges for Menus containing a Home page.
				$iconImage = $item->icon;

				if ($iconImage)
				{
					if (substr($iconImage, 0, 6) === 'class:' && substr($iconImage, 6) === 'icon-home')
					{
						$iconImage = '<span class="home-image icon-featured" aria-hidden="true"></span>';
						$iconImage .= '<span class="sr-only">' . Text::_('JDEFAULT') . '</span>';
					}
					elseif (substr($iconImage, 0, 6) === 'image:')
					{
						$iconImage = '&nbsp;<span class="badge badge-secondary">' . substr($iconImage, 6) . '</span>';
					}
					else
					{
						$iconImage = '<span>' . HTMLHelper::_('image', $iconImage, null) . '</span>';
					}

					$item->title = $item->title . $iconImage;
				}
			}

			if ($item->hasChildren())
			{
				self::preprocess($item);
			}

			// Ok we passed everything, load language at last only
			if ($item->element)
			{
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR, null, false, true) ||
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->element, null, false, true);
			}

			if ($item->type === 'separator' && $item->params->get('text_separator') == 0)
			{
				$item->title = '';
			}

			$item->text = Text::_($item->title);
		}
	}
}
