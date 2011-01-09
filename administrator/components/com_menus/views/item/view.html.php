<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
class MenusViewItem extends JView
{
	protected $form;
	protected $item;
	protected $modules;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->modules	= $this->get('Modules');
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
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= MenusHelper::getActions($this->state->get('filter.parent_id'));
		
		JToolBarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_ITEM_TITLE' : 'COM_MENUS_VIEW_EDIT_ITEM_TITLE'), 'menu-add');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create')) {
			if ($canDo->get('core.edit')) {
				JToolBarHelper::apply('item.apply','JTOOLBAR_APPLY');
			}
			JToolBarHelper::save('item.save', 'JTOOLBAR_SAVE');
		}
		
		// If not checked out, can save the item.
		if (!$isNew && !$checkedOut && $canDo->get('core.edit')) {
			JToolBarHelper::apply('item.apply','JTOOLBAR_APPLY');
			JToolBarHelper::save('item.save','JTOOLBAR_SAVE');
		}
		
		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('item.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy only if we have create rights.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('item.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('item.cancel','JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}
		
		JToolBarHelper::divider();

		// Get the help information for the menu item.
		$help = $this->get('Help');
		JToolBarHelper::help($help->key, $help->local, $help->url);
	}
}
