<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;
use Joomla\Registry\Registry;

/**
 * Helper for mod_quickicon
 *
 * @since  1.6
 */
abstract class QuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @var     array[]
	 * @since   1.6
	 */
	protected static $buttons = array();

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   Registry        $params       The module parameters
	 * @param   CMSApplication  $application  The application
	 *
	 * @return  array  An array of buttons
	 *
	 * @since   1.6
	 */
	public static function &getButtons(Registry $params, CMSApplication $application = null)
	{
		if ($application == null)
		{
			$application = Factory::getApplication();
		}

		$key     = (string) $params;
		$context = (string) $params->get('context', 'mod_quickicon');

		if (!isset(self::$buttons[$key]))
		{
			// Load mod_quickicon language file in case this method is called before rendering the module
			$application->getLanguage()->load('mod_quickicon');

			self::$buttons[$key] = [];

			if ($params->get('show_users'))
			{
				$tmp = [
					'image'   => 'fas fa-users',
					'link'    => Route::_('index.php?option=com_users&view=users'),
					'linkadd' => Route::_('index.php?option=com_users&task=user.add'),
					'name'    => 'MOD_QUICKICON_USER_MANAGER',
					'access'  => array('core.manage', 'com_users', 'core.create', 'com_users'),
					'group'   => 'MOD_QUICKICON_SITE',
				];

				if ($params->get('show_users') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_users&amp;task=users.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_menuitems'))
			{
				$tmp = [
					'image'   => 'fas fa-list',
					'link'    => Route::_('index.php?option=com_menus&view=items&menutype='),
					'linkadd' => Route::_('index.php?option=com_menus&task=item.add'),
					'name'    => 'MOD_QUICKICON_MENUITEMS_MANAGER',
					'access'  => array('core.manage', 'com_menus', 'core.create', 'com_menus'),
					'group'   => 'MOD_QUICKICON_STRUCTURE',
				];

				if ($params->get('show_menuitems') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_menus&amp;task=items.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_articles'))
			{
				$tmp = [
					'image'   => 'fas fa-file-alt',
					'link'    => Route::_('index.php?option=com_content&view=articles'),
					'linkadd' => Route::_('index.php?option=com_content&task=article.add'),
					'name'    => 'MOD_QUICKICON_ARTICLE_MANAGER',
					'access'  => array('core.manage', 'com_content', 'core.create', 'com_content'),
					'group'   => 'MOD_QUICKICON_SITE',
				];

				if ($params->get('show_articles') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_content&amp;task=articles.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_categories'))
			{
				$tmp = [
					'image'   => 'fas fa-folder-open',
					'link'    => Route::_('index.php?option=com_categories&view=categories&extension=com_content'),
					'linkadd' => Route::_('index.php?option=com_categories&task=category.add'),
					'name'    => 'MOD_QUICKICON_CATEGORY_MANAGER',
					'access'  => array('core.manage', 'com_categories', 'core.create', 'com_categories'),
					'group'   => 'MOD_QUICKICON_SITE',
				];

				if ($params->get('show_categories') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_categories&amp;task=categories.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_media'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fas fa-images',
					'link'   => Route::_('index.php?option=com_media'),
					'name'   => 'MOD_QUICKICON_MEDIA_MANAGER',
					'access' => array('core.manage', 'com_media'),
					'group'  => 'MOD_QUICKICON_SITE',
				];
			}

			if ($params->get('show_modules'))
			{
				$tmp = [
					'image'   => 'fas fa-cube',
					'link'    => Route::_('index.php?option=com_modules&view=modules&client_id=0'),
					'linkadd' => Route::_('index.php?option=com_modules&view=select&client_id=0'),
					'name'    => 'MOD_QUICKICON_MODULE_MANAGER',
					'access'  => array('core.manage', 'com_modules'),
					'group'   => 'MOD_QUICKICON_SITE'
				];

				if ($params->get('show_modules') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_modules&amp;task=modules.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_plugins'))
			{
				$tmp = [
					'image'  => 'fas fa-plug',
					'link'   => Route::_('index.php?option=com_plugins'),
					'name'   => 'MOD_QUICKICON_PLUGIN_MANAGER',
					'access' => array('core.manage', 'com_plugins'),
					'group'  => 'MOD_QUICKICON_SITE'
				];

				if ($params->get('show_plugins') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_plugins&amp;task=plugins.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_template_styles'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fas fa-paint-brush',
					'link'   => Route::_('index.php?option=com_templates&view=styles&client_id=0'),
					'name'   => 'MOD_QUICKICON_TEMPLATE_STYLES',
					'access' => array('core.admin', 'com_templates'),
					'group'  => 'MOD_QUICKICON_SITE'
				];
			}

			if ($params->get('show_template_code'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fas fa-code',
					'link'   => Route::_('index.php?option=com_templates&view=templates&client_id=0'),
					'name'   => 'MOD_QUICKICON_TEMPLATE_CODE',
					'access' => array('core.admin', 'com_templates'),
					'group'  => 'MOD_QUICKICON_SITE'
				];
			}

			if ($params->get('show_checkin'))
			{
				$tmp = [
					'image'   => 'fas fa-unlock-alt',
					'link'    => Route::_('index.php?option=com_checkin'),
					'name'    => 'MOD_QUICKICON_CHECKINS',
					'access'  => array('core.admin', 'com_checkin'),
					'group'   => 'MOD_QUICKICON_SYSTEM'
				];

				if ($params->get('show_checkin') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_checkin&amp;task=getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_cache'))
			{
				$tmp = [
					'image'   => 'fas fa-cloud',
					'link'    => Route::_('index.php?option=com_cache'),
					'name'    => 'MOD_QUICKICON_CACHE',
					'access'  => array('core.admin', 'com_cache'),
					'group'   => 'MOD_QUICKICON_SYTEM'
				];

				if ($params->get('show_cache') == 2)
				{
					$tmp['ajaxurl'] = 'index.php?option=com_cache&amp;task=display.getQuickiconContent&amp;format=json';
				}

				self::$buttons[$key][] = $tmp;
			}

			if ($params->get('show_global'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fas fa-cog',
					'link'   => Route::_('index.php?option=com_config'),
					'name'   => 'MOD_QUICKICON_GLOBAL_CONFIGURATION',
					'access' => array('core.manage', 'com_config', 'core.admin', 'com_config'),
					'group'  => 'MOD_QUICKICON_SYSTEM',
				];
			}

			PluginHelper::importPlugin('quickicon');

			$arrays = (array) $application->triggerEvent(
				'onGetIcons',
				new QuickIconsEvent('onGetIcons', ['context' => $context])
			);

			foreach ($arrays as $response)
			{
				if (!\is_array($response))
				{
					continue;
				}

				foreach ($response as $icon)
				{
					$default = array(
						'link'    => null,
						'image'   => null,
						'text'    => null,
						'name'    => null,
						'linkadd' => null,
						'access'  => true,
						'class'   => null,
						'group'   => 'MOD_QUICKICON',
					);

					$icon = array_merge($default, $icon);

					if (!\is_null($icon['link']) && !\is_null($icon['text']))
					{
						self::$buttons[$key][] = $icon;
					}
				}
			}
		}

		return self::$buttons[$key];
	}
}
