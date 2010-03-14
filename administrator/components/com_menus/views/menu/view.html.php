<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Menus Menu Item View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusViewMenu extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
		$this->_setToolBar();
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolBar()
	{
		$isNew	= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_MENU_TITLE' : 'COM_MENUS_VIEW_EDIT_MENU_TITLE'));

		JToolBarHelper::apply('menu.apply','JTOOLBAR_APPLY');
		JToolBarHelper::save('menu.save','JTOOLBAR_SAVE');
		JToolBarHelper::addNew('menu.save2new', 'JTOOLBAR_SAVE_AND_NEW');

		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('menu.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if ($isNew) {
			JToolBarHelper::cancel('menu.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('menu.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.menus.menu','JTOOLBAR_HELP');
	}
}
