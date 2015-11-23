<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class CjForumViewUsers extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo		= JHelperContent::getActions('com_cjforum');

		CjForumHelper::addSubmenu('users');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= $this->canDo;
		$user 	= JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_CJFORUM_VIEW_USERS_TITLE'), 'users user');

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('user.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::unpublish('users.block', 'COM_CJFORUM_TOOLBAR_BLOCK', true);
			JToolbarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_CJFORUM_TOOLBAR_UNBLOCK', true);
			JToolbarHelper::divider();
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_cjforum') && $user->authorise('core.edit', 'com_cjforum') && $user->authorise('core.edit.state', 'com_cjforum'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::custom('users.sync', 'refresh.png', 'refresh.png', 'COM_CJFORUM_TOOLBAR_SYNC', false);
			JToolbarHelper::preferences('com_cjforum');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_USERS_USER_MANAGER');
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
				'a.name' => JText::_('COM_CJFORUM_HEADING_NAME'),
				'a.username' => JText::_('JGLOBAL_USERNAME'),
				'a.block' => JText::_('COM_CJFORUM_HEADING_ENABLED'),
				'a.activation' => JText::_('COM_CJFORUM_HEADING_ACTIVATED'),
				'a.email' => JText::_('JGLOBAL_EMAIL'),
				'a.lastvisitDate' => JText::_('COM_CJFORUM_HEADING_LAST_VISIT_DATE'),
				'a.registerDate' => JText::_('COM_CJFORUM_HEADING_REGISTRATION_DATE'),
				'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
