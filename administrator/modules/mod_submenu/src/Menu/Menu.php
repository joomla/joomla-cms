<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Submenu\Administrator\Menu;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Uri\Uri;
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
			if (substr($item->link, 0, 8) === 'special:')
			{
				$special = substr($item->link, 8);

				if ($special === 'language-forum')
				{
					$item->link = 'index.php?option=com_admin&amp;view=help&amp;layout=langforum';
				}
			}

			$uri   = new Uri($item->link);
			$query = $uri->getQuery(true);

			/**
			 * This is needed to populate the element property when the component is no longer
			 * installed but its core menu items are left behind.
			 */
			if ($option = $uri->getVar('option'))
			{
				$item->element = $option;
			}

			// Exclude item if is not enabled
			if ($item->element && !ComponentHelper::isEnabled($item->element))
			{
				$parent->removeChild($item);
				continue;
			}

			/*
			 * Multilingual Associations if the site is not set as multilingual and/or Associations is not enabled in
			 * the Language Filter plugin
			 */

			if ($item->element === 'com_associations' && !Associations::isEnabled())
			{
				$parent->removeChild($item);
				continue;
			}

			$itemParams = $item->getParams();

			// Exclude item with menu item option set to exclude from menu modules
			if ($itemParams->get('menu-permission'))
			{
				@list($action, $asset) = explode(';', $itemParams->get('menu-permission'));

				if (!$user->authorise($action, $asset))
				{
					$parent->removeChild($item);
					continue;
				}
			}

			// Populate automatic children for container items
			if ($item->type === 'container')
			{
				$exclude    = (array) $itemParams->get('hideitems') ?: array();
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
					$parts = explode('.', $query['extension']);

					$workflow = ComponentHelper::getParams($parts[0])->get('workflow_enabled');
				}

				if (!$workflow)
				{
					$parent->removeChild($item);
					continue;
				}

				[$assetName] = isset($query['extension']) ? explode('.', $query['extension'], 2) : array('com_workflow');
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
			elseif (($item->link === 'index.php?option=com_installer&view=install' || $item->link === 'index.php?option=com_installer&view=languages')
				&& !$user->authorise('core.admin'))
			{
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
			elseif ($item->element && !$user->authorise(($item->scope === 'edit') ? 'core.create' : 'core.manage', $item->element))
			{
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_menus')
			{
				// Get badges for Menus containing a Home page.
				$iconImage = $item->icon;

				if ($iconImage)
				{
					if (substr($iconImage, 0, 6) === 'class:' && substr($iconImage, 6) === 'icon-home')
					{
						$iconImage = '<span class="home-image icon-home" aria-hidden="true"></span>';
						$iconImage .= '<span class="visually-hidden">' . Text::_('JDEFAULT') . '</span>';
					}
					elseif (substr($iconImage, 0, 6) === 'image:')
					{
						$iconImage = '&nbsp;<span class="badge bg-secondary">' . substr($iconImage, 6) . '</span>';
					}

					$item->iconImage = $iconImage;
				}
			}

			if ($item->hasChildren())
			{
				self::preprocess($item);
			}

			// Ok we passed everything, load language at last only
			if ($item->element)
			{
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR) ||
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->element);
			}

			if ($item->type === 'separator' && $item->getParams()->get('text_separator') == 0)
			{
				$item->title = '';
			}

			$item->text = Text::_($item->title);
		}
	}
}
