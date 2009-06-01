<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package		Joomla.Administrator
 * @subpackage	Categories
 */
class TOOLBAR_categories {
	/**
	* Draws the menu for Editing an existing category
	* @param int The published state (to display the inverse button)
	*/
	function _EDIT($edit)
	{
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		$section = JRequest::getCmd('section');

		$text = ($edit ? JText::_('Edit') : JText::_('New'));

		JToolBarHelper::title(JText::_('Category') .': <small><small>[ '. $text.' ]</small></small>', 'categories.png');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
			// for existing articles the button is renamed `close`
			JToolBarHelper::cancel('cancel', 'Close');
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help('screen.' . substr($section, 4) . '.categories.edit');
	}

	/**
	* Draws the menu for Moving existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _MOVE() {

		JToolBarHelper::title(JText::_('Category') .': <small><small>[ '. JText::_('Move').' ]</small></small>', 'categories.png');
		JToolBarHelper::save('movesave');
		JToolBarHelper::cancel();
	}

	/**
	* Draws the menu for Copying existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _COPY() {
		JToolBarHelper::title(JText::_('Category') .': <small><small>[ '. JText::_('Copy').' ]</small></small>', 'categories.png');

		JToolBarHelper::save('copysave');
		JToolBarHelper::cancel();
	}

	/**
	* Draws the menu for Editing an existing category
	*/
	function _DEFAULT()
	{
		$section = JRequest::getCmd('section');

		JToolBarHelper::title(JText::_('Category Manager') .': <small><small>[ '. JText::_(JString::substr($section, 4)).' ]</small></small>', 'categories.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();

		if ($section == 'com_content' || ($section > 0)) {
			JToolBarHelper::customX('moveselect', 'move.png', 'move_f2.png', 'Move', true);
			JToolBarHelper::customX('copyselect', 'copy.png', 'copy_f2.png', 'Copy', true);
		}
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help('screen.' . substr($section, 4) . '.categories');
	}
}