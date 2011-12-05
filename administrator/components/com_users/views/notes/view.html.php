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
 * Categories view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		2.5.0
 */
class UsersViewNotes extends JView
{
	protected $items;
	protected $pagination;
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
			$this->items = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$this->state = $this->get('State');
			$this->user = $this->get('User');

			// Check for errors.
			if (count($errors = $this->get('Errors')))
			{
				throw new Exception(implode("\n", $errors), 500);
				return false;
			}

			$this->_setToolbar();
			parent::display($tpl);
		}
		catch (Exception $e)
		{
			JError::raiseError(500, $e->getMessage());
		}
	}

	/**
	 * Display the toolbar
	 */
	private function _setToolbar()
	{
		$canDo = UsersHelper::getActions();
		$state = $this->get('State');

		JToolBarHelper::title(JText::_('COM_USERS_VIEW_NOTES_TITLE'), 'logo');

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('notes.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('notes.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publish('notes.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('notes.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			JToolBarHelper::divider();
			JToolBarHelper::archiveList('notes.archive');
			JToolBarHelper::checkin('notes.checkin');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'notes.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('notes.trash');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_users');
			JToolBarHelper::divider();
		}
	}
}
