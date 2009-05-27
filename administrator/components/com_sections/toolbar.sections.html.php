<?php
/**
 * @version		$Id: toolbar.sections.html.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Sections
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Sections
 */
class TOOLBAR_sections {
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT($edit) {
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		JArrayHelper::toInteger($cid, array(0));

		$text = ($edit ? JText::_('Edit') : JText::_('New'));

		JToolBarHelper::title(JText::_('Section').': <small><small>[ '. $text.' ]</small></small>', 'sections.png');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('cancel', 'Close');
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help('screen.sections.edit');
	}
	/**
	* Draws the menu for Copying existing sections
	* @param int The published state (to display the inverse button)
	*/
	function _COPY() {
		JToolBarHelper::title(JText::_('Section') .': <small><small>[ '. JText::_('Copy').' ]</small></small>', 'section.png');
		//JToolBarHelper::title(JText::_('Copy Section'), 'sections.png');
		JToolBarHelper::save('copysave');
		JToolBarHelper::cancel();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _DEFAULT(){
		JToolBarHelper::title(JText::_('Section Manager'), 'sections.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX('copyselect', 'copy.png', 'copy_f2.png', 'Copy', true);
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help('screen.sections');
	}
}