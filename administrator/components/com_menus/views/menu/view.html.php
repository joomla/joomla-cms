<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusViewMenu extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$canDo		= MenusHelper::getActions($this->state->get('filter.parent_id'));

		JToolBarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_MENU_TITLE' : 'COM_MENUS_VIEW_EDIT_MENU_TITLE'), 'menu.png');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create')) {
			if ($canDo->get('core.edit')) {
				JToolBarHelper::apply('menu.apply');
			}
			JToolBarHelper::save('menu.save');
		}

		// If user can edit, can save the item.
		if (!$isNew && $canDo->get('core.edit')) {
			JToolBarHelper::apply('menu.apply');
			JToolBarHelper::save('menu.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create')) {
			JToolBarHelper::save2new('menu.save2new');
		}
		if ($isNew) {
			JToolBarHelper::cancel('menu.cancel');
		} else {
			JToolBarHelper::cancel('menu.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_MENUS_MENU_MANAGER_EDIT');
	}
}
