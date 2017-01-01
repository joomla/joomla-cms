<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_quickicon
 *
 * @since  1.6
 */
abstract class ModQuickIconHelper
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
	 * @param   JObject  $params  The module parameters.
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
				JFactory::getLanguage()->load('mod_quickicon');

				self::$buttons[$key] = array(
					array(
						'link'   => JRoute::_('index.php?option=com_content&task=article.add'),
						'image'  => 'pencil-2',
						'icon'   => 'header/icon-48-article-add.png',
						'text'   => JText::_('MOD_QUICKICON_ADD_NEW_ARTICLE'),
						'access' => array('core.manage', 'com_content', 'core.create', 'com_content'),
						'group'  => 'MOD_QUICKICON_CONTENT'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_content'),
						'image'  => 'stack',
						'icon'   => 'header/icon-48-article.png',
						'text'   => JText::_('MOD_QUICKICON_ARTICLE_MANAGER'),
						'access' => array('core.manage', 'com_content'),
						'group'  => 'MOD_QUICKICON_CONTENT'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_categories&extension=com_content'),
						'image'  => 'folder',
						'icon'   => 'header/icon-48-category.png',
						'text'   => JText::_('MOD_QUICKICON_CATEGORY_MANAGER'),
						'access' => array('core.manage', 'com_content'),
						'group'  => 'MOD_QUICKICON_CONTENT'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_media'),
						'image'  => 'pictures',
						'icon'   => 'header/icon-48-media.png',
						'text'   => JText::_('MOD_QUICKICON_MEDIA_MANAGER'),
						'access' => array('core.manage', 'com_media'),
						'group'  => 'MOD_QUICKICON_CONTENT'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_menus'),
						'image'  => 'list-view',
						'icon'   => 'header/icon-48-menumgr.png',
						'text'   => JText::_('MOD_QUICKICON_MENU_MANAGER'),
						'access' => array('core.manage', 'com_menus'),
						'group'  => 'MOD_QUICKICON_STRUCTURE'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_users'),
						'image'  => 'users',
						'icon'   => 'header/icon-48-user.png',
						'text'   => JText::_('MOD_QUICKICON_USER_MANAGER'),
						'access' => array('core.manage', 'com_users'),
						'group'  => 'MOD_QUICKICON_USERS'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_modules'),
						'image'  => 'cube',
						'icon'   => 'header/icon-48-module.png',
						'text'   => JText::_('MOD_QUICKICON_MODULE_MANAGER'),
						'access' => array('core.manage', 'com_modules'),
						'group'  => 'MOD_QUICKICON_STRUCTURE'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_config'),
						'image'  => 'cog',
						'icon'   => 'header/icon-48-config.png',
						'text'   => JText::_('MOD_QUICKICON_GLOBAL_CONFIGURATION'),
						'access' => array('core.manage', 'com_config', 'core.admin', 'com_config'),
						'group'  => 'MOD_QUICKICON_CONFIGURATION'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_templates'),
						'image'  => 'eye',
						'icon'   => 'header/icon-48-themes.png',
						'text'   => JText::_('MOD_QUICKICON_TEMPLATE_MANAGER'),
						'access' => array('core.manage', 'com_templates'),
						'group'  => 'MOD_QUICKICON_CONFIGURATION'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_languages'),
						'image'  => 'comments-2',
						'icon'   => 'header/icon-48-language.png',
						'text'   => JText::_('MOD_QUICKICON_LANGUAGE_MANAGER'),
						'access' => array('core.manage', 'com_languages'),
						'group'  => 'MOD_QUICKICON_CONFIGURATION'
					),
					array(
						'link'   => JRoute::_('index.php?option=com_installer'),
						'image'  => 'download',
						'icon'   => 'header/icon-48-extension.png',
						'text'   => JText::_('MOD_QUICKICON_INSTALL_EXTENSIONS'),
						'access' => array('core.manage', 'com_installer'),
						'group'  => 'MOD_QUICKICON_EXTENSIONS'
					)
				);
			}
			else
			{
				self::$buttons[$key] = array();
			}

			// Include buttons defined by published quickicon plugins
			JPluginHelper::importPlugin('quickicon');
			$app = JFactory::getApplication();
			$arrays = (array) $app->triggerEvent('onGetIcons', array($context));

			foreach ($arrays as $response)
			{
				foreach ($response as $icon)
				{
					$default = array(
						'link'   => null,
						'image'  => 'cog',
						'text'   => null,
						'access' => true,
						'group'  => 'MOD_QUICKICON_EXTENSIONS'
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

	/**
	 * Classifies the $buttons by group
	 *
	 * @param   array  $buttons  The buttons
	 *
	 * @return  array  The buttons sorted by groups
	 *
	 * @since   3.2
	 */
	public static function groupButtons($buttons)
	{
		$groupedButtons = array();

		foreach ($buttons as $button)
		{
			$groupedButtons[$button['group']][] = $button;
		}

		return $groupedButtons;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param   JObject  $params  The module parameters.
	 * @param   JObject  $module  The module.
	 *
	 * @return  string	The alternate title for the module.
	 *
	 * @deprecated  4.0 Unused. Title can be adjusted in module itself if needed.
	 */
	public static function getTitle($params, $module)
	{
		$key = $params->get('context', 'mod_quickicon') . '_title';

		if (JFactory::getLanguage()->hasKey($key))
		{
			return JText::_($key);
		}
		else
		{
			return $module->title;
		}
	}
}
