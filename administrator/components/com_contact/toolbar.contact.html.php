<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Contact
 */
class TOOLBAR_contact
{
	/**
	* Draws the menu for a New Contact
	*/
	function _EDIT($edit) {
		$cid = JRequest::getVar('cid', array(0), '', 'array');

		$text = ($edit ? JText::_('Edit') : JText::_('New'));

		JToolBarHelper::title(JText::_('Contact') .': <small><small>[ '. $text .' ]</small></small>', 'generic.png');

		//JToolBarHelper::custom('save2new', 'new.png', 'new_f2.png', 'Save & New', false,  false);
		//JToolBarHelper::custom('save2copy', 'copy.png', 'copy_f2.png', 'Save To Copy', false,  false);
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('cancel', 'Close');
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help('screen.contactmanager.edit');
	}

	function _DEFAULT() {

		JToolBarHelper::title(JText::_('Contact Manager'), 'generic.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_contact', '500');

		JToolBarHelper::help('screen.contactmanager');
	}
}