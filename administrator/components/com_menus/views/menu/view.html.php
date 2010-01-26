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
		JToolBarHelper::title(JText::_($isNew ? 'Menus_View_New_Menu_Title' : 'Menus_View_Edit_Menu_Title'));

		JToolBarHelper::apply('menu.apply','JTOOLBAR_APPLY');
		JToolBarHelper::save('menu.save','JTOOLBAR_SAVE');
		JToolBarHelper::addNew('menu.save2new', 'JToolbar_Save_and_new');

		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('menu.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_copy', false);
		}
		if ($isNew) {
			JToolBarHelper::cancel('menu.cancel', 'JToolbar_Cancel');
		}
		else {
			JToolBarHelper::cancel('menu.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.menus.menu','JTOOLBAR_HELP');
	}
}
