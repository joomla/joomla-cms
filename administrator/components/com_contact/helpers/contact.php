<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	$vName	The name of the active view.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTACT_SUBMENU_CONTACTS'),
			'index.php?option=com_contact&view=contacts',
			$vName == 'contacts'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTACT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_contact',
			$vName == 'categories'
		);

		if ($vName == 'categories')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_contact')),
				'contact-categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  The category ID.
	 * @param   integer  The contact ID.
	 *
	 * @return  JObject
	 * @since   1.6
	 */
	public static function getActions($categoryId = 0, $id = 0, $assetName = '')
	{
		if (empty($contactId) && empty($categoryId))
		{
			$assetName = 'com_contact';
			$level = 'component';
		}
		elseif (empty($contactId))
		{
			$assetName = 'com_contact.category.'.(int) $categoryId;
			$level = 'category';
		}
		else
		{
			$assetName = 'com_contact.contact.'.(int) $contactId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_contact', $level);

		return parent::getActions($categoryId, $id, $assetName);
	}
}
