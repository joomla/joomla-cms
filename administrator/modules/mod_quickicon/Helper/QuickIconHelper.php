<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

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
	 * @since   1.6
	 */
	protected static $buttons = array();

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   \JObject  $params  The module parameters.
	 *
	 * @return  array  An array of buttons
	 *
	 * @since   1.6
	 */
	public static function &getButtons($params)
	{
		$key = (string) $params;

		if (!isset(self::$buttons[$key]))
		{
			$context = $params->get('context', 'mod_quickicon');

			if ($context == 'mod_quickicon')
			{
				// Load mod_quickicon language file in case this method is called before rendering the module
				Factory::getLanguage()->load('mod_quickicon');

				self::$buttons[$key] = array(
					array(
						'link'   => Route::_('index.php?option=com_content&task=article.add'),
						'image'  => 'fa fa-pencil-square',
						'text'   => Text::_('MOD_QUICKICON_ADD_NEW_ARTICLE'),
						'access' => array('core.manage', 'com_content', 'core.create', 'com_content'),
						'group'  => 'MOD_QUICKICON_CONTENT',
					),
					array(
						'link'   => Route::_('index.php?option=com_media'),
						'image'  => 'fa fa-image',
						'text'   => Text::_('MOD_QUICKICON_MEDIA_MANAGER'),
						'access' => array('core.manage', 'com_media'),
						'group'  => 'MOD_QUICKICON_CONTENT',
					),
					array(
						'link'   => Route::_('index.php?option=com_config'),
						'image'  => 'fa fa-cog',
						'text'   => Text::_('MOD_QUICKICON_GLOBAL_CONFIGURATION'),
						'access' => array('core.manage', 'com_config', 'core.admin', 'com_config'),
						'group'  => 'MOD_QUICKICON_CONFIGURATION',
					),
					array(
						'link'   => Route::_('index.php?option=com_modules'),
						'image'  => 'fa fa-cubes',
						'text'   => Text::_('MOD_QUICKICON_MODULE_MANAGER'),
						'access' => array('core.manage', 'com_modules'),
						'group'  => 'MOD_QUICKICON_STRUCTURE'
					)
				);
			}
			else
			{
				self::$buttons[$key] = array();
			}

			// Include buttons defined by published quickicon plugins
			PluginHelper::importPlugin('quickicon');

			$arrays = (array) Factory::getApplication()->triggerEvent(
				'onGetIcons',
				new QuickIconsEvent('onGetIcons', ['context' => $context])
			);

			foreach ($arrays as $response)
			{
				foreach ($response as $icon)
				{
					$default = array(
						'link'   => null,
						'image'  => 'fa fa-cog',
						'text'   => null,
						'access' => true,
						'group'  => 'MOD_QUICKICON_EXTENSIONS',
					);
					$icon = array_merge($default, $icon);

					if (!is_null($icon['link']) && !is_null($icon['text']))
					{
						self::$buttons[$key][] = $icon;
					}
				}
			}
		}

		return self::$buttons[$key];
	}
}
