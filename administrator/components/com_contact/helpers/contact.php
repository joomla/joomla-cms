<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		1.6
 */
class ContactHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_CONTACT_SUBMENU_CONTACTS'),
			'index.php?option=com_contact&view=contacts',
			$vName == 'contacts'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_CONTACT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_contact',
			$vName == 'categories'
		);

		if ($vName=='categories') {
			JToolBarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_contact')),
				'contact-categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 * @param	int		The contact ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0, $contactId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($contactId) && empty($categoryId)) {
			$assetName = 'com_contact';
			$level = 'component';
		}
		elseif (empty($contactId)) {
			$assetName = 'com_contact.category.'.(int) $categoryId;
			$level = 'category';
		}
		else {
			$assetName = 'com_contact.contact.'.(int) $contactId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_contact', $level);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}
}
