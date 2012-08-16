<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * User notes list view
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
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
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->user = $this->get('User');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Get the component HTML helpers
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// turn parameters into registry objects
		foreach ($this->items as $item) {
			$item->cparams = new JRegistry;
			$item->cparams->loadString($item->category_params);
		}

		parent::display($tpl);
		$this->addToolbar();
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
		$canDo = UsersHelper::getActions();

		JToolbarHelper::title(JText::_('COM_USERS_VIEW_NOTES_TITLE'), 'user');

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

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'notes.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('notes.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_users');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_USERS_USER_NOTES');
	}
}
