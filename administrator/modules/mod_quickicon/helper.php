<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 */
abstract class QuickIconHelper
{
	/**
	 * Stack to hold default buttons
	 */
	protected static $buttons = array();

	/**
	 * Helper method to generate a button in administrator panel
	 *
	 * @param	array	A named array with keys link, image, text, access and imagePath
	 * @return	string	HTML for button
	 */
	public static function button($button)
	{
		if (!empty($button['access']))
		{
			if (!JFactory::getUser()->authorize($button['access'])) {
				return '';
			}
		}

		if (empty($button['imagePath']))
		{
			$template = JFactory::getApplication()->getTemplate();
			$button['imagePath'] = '/templates/'. $template .'/images/header/';
		}

		ob_start();
		require JModuleHelper::getLayoutPath('mod_quickicon', 'button');
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @return	array	An array of buttons
	 */
	public static function &getButtons()
	{
		if (empty(self::$buttons))
		{
			self::$buttons = array(
				array(
					'link' => JRoute::_('index.php?option=com_content&task=article.add'),
					'image' => 'icon-48-article-add.png',
					'text' => JText::_('MOD_QUICKICON_ADD_NEW_ARTICLE')
				),
				array(
					'link' => JRoute::_('index.php?option=com_content'),
					'image' => 'icon-48-article.png',
					'text' => JText::_('MOD_QUICKICON_ARTICLE_MANAGER')
				),
				array(
					'link' => JRoute::_('index.php?option=com_categories&extension=com_content'),
					'image' => 'icon-48-category.png',
					'text' => JText::_('MOD_QUICKICON_CATEGORY_MANAGER')
				),
				array(
					'link' => JRoute::_('index.php?option=com_media'),
					'image' => 'icon-48-media.png',
					'text' => JText::_('MOD_QUICKICON_MEDIA_MANAGER')
				),
				array(
					'link' => JRoute::_('index.php?option=com_menus'),
					'image' => 'icon-48-menumgr.png',
					'text' => JText::_('MOD_QUICKICON_MENU_MANAGER'),
					'access' => 'core.menus.manage'
				),
				array(
					'link' => JRoute::_('index.php?option=com_users'),
					'image' => 'icon-48-user.png',
					'text' => JText::_('MOD_QUICKICON_USER_MANAGER'),
					'access' => 'core.users.manage'
				),
				array(
					'link' => JRoute::_('index.php?option=com_modules'),
					'image' => 'icon-48-module.png',
					'text' => JText::_('MOD_QUICKICON_MODULE_MANAGER'),
				),
				array(
					'link' => JRoute::_('index.php?option=com_installer'),
					'image' => 'icon-48-extension.png',
					'text' => JText::_('MOD_QUICKICON_EXTENSION_MANAGER'),
				)
			);
		}

		return self::$buttons;
	}
}