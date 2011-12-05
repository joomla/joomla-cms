<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Category view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		2.5.0
 */
class UsersViewNote extends JView
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Override the display method for the view.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function display($tpl = null)
	{
		try
		{
			// Initialise view variables.
			$this->state = $this->get('State');
			$this->item = $this->get('Item');
			$this->form = $this->get('Form');

			// Check for errors.
			if (count($errors = $this->get('Errors'))) {
				throw new Exception(implode("\n", $errors), 500);
				return false;
			}

			parent::display($tpl);
			$this->_addToolbar();
		}
		catch (Exception $e)
		{
			JError::raiseError(500, $e->getMessage());
		}
	}

	/**
	 * Display the toolbar.
	 *
	 * @return	void
	 * @since	1.0
	 */
	private function _addToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= UsersHelper::getActions($this->state->get('filter.category_id'), $this->item->id);

		JToolBarHelper::title(JText::_('COM_USERS_NOTES'), 'weblinks.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||(count($user->getAuthorisedCategories('COM_USERS', 'core.create')))))
		{
			JToolBarHelper::apply('note.apply');
			JToolBarHelper::save('note.save');
		}
		if (!$checkedOut && (count($user->getAuthorisedCategories('COM_USERS', 'core.create')))){
			JToolBarHelper::save2new('note.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && (count($user->getAuthorisedCategories('COM_USERS', 'core.create')) > 0)) {
			JToolBarHelper::save2copy('note.save2copy');
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('note.cancel');
		}
		else {
			JToolBarHelper::cancel('note.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_USERS_NOTES_EDIT');
	}
}