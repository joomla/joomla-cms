<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * User notes list view
 *
 * @since  2.5
 */
class UsersViewNotes extends JViewLegacy
{
	/**
	 * A list of user note objects.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $items;

	/**
	 * The pagination object.
	 *
	 * @var    JPagination
	 * @since  2.5
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var    JObject
	 * @since  2.5
	 */
	protected $state;

	/**
	 * The model state.
	 *
	 * @var    JUser
	 * @since  2.5
	 */
	protected $user;

	/**
	 * Override the display method for the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Initialise view variables.
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->user          = $this->get('User');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		UsersHelper::addSubmenu('notes');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Get the component HTML helpers
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Turn parameters into registry objects
		foreach ($this->items as $item)
		{
			$item->cparams = new Registry($item->category_params);
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_users', 'category', $this->state->get('filter.category_id'));

		JToolbarHelper::title(JText::_('COM_USERS_VIEW_NOTES_TITLE'), 'users user');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('note.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('note.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('notes.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('notes.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			JToolbarHelper::divider();
			JToolbarHelper::archiveList('notes.archive');
			JToolbarHelper::checkin('notes.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'notes.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('notes.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_users');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_USERS_USER_NOTES');

		JHtmlSidebar::setAction('index.php?option=com_users&view=notes');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'u.name'        => JText::_('COM_USERS_USER_HEADING'),
			'a.subject'     => JText::_('COM_USERS_SUBJECT_HEADING'),
			'c.title'       => JText::_('COM_USERS_CATEGORY_HEADING'),
			'a.state'       => JText::_('JSTATUS'),
			'a.review_time' => JText::_('COM_USERS_REVIEW_HEADING'),
			'a.id'          => JText::_('JGRID_HEADING_ID')
		);
	}
}
