<?php
/**
 * @package	 Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The menu controller for ajax requests
 *
 * @since  __DEPLOY_VERSION__
 */
class MenusControllerAjax extends JControllerLegacy
{
	/**
	 * Method to fetch associations of a menu item
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * token: the form token
	 * assocId: the id of the menu item whose associations are to be returned
	 * excludeLang: the association for this language is to be excluded
	 *
	 * @return  null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function fetchAssociations()
	{
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$input     = JFactory::getApplication()->input;
			$extension = $input->get('extension');

			$assocId   = $input->getInt('assocId', 0);

			if ($assocId == 0)
			{
				echo new JResponseJson(null, JText::_('JLIB_FORM_VALIDATE_FIELD_INVALID', "assocId"), true);

				return;
			}

			$excludeLang = $input->get('excludeLang', '', 'STRING');

			$associations = JLanguageAssociations::getAssociations('com_menus', '#__menu', 'com_menus.item', (int) $assocId, 'id', '', '');

			unset($associations[$excludeLang]);

			// Add the title to each of the associated records
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
			$menuTable = JTable::getInstance('Menu', 'JTable', array());

			foreach($associations as $lang => $association)
			{
				$menuTable->load($association->id);
				$associations[$lang]->title = $menuTable->title;
			}

			$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE');

			echo new JResponseJson($associations, $message);
		}
	}
}