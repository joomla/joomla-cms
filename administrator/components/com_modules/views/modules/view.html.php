<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of modules.
 *
 * @since  1.6
 */
class ModulesViewModules extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->total         = $this->get('Total');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// We do not need the Page and the Language filters when filtering by administrator
		if ($this->state->get('client_id') == 1)
		{
			unset($this->activeFilters['menuitem']);
			$this->filterForm->removeField('menuitem', 'filter');

			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		// We don't need the toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}
		// If in modal layout.
		else
		{
			// Client id selector should not exist.
			$this->filterForm->removeField('client_id', '');

			// If in the frontend state and language should not activate the search tools.
			if (JFactory::getApplication()->isClient('site'))
			{
				unset($this->activeFilters['state']);
				unset($this->activeFilters['language']);
			}
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		return parent::display($tpl);
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
		$state = $this->get('State');
		$canDo = JHelperContent::getActions('com_modules');
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		if ($state->get('client_id') == 1)
		{
			JToolbarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES_ADMIN'), 'cube module');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES_SITE'), 'cube module');
		}

		if ($canDo->get('core.create'))
		{
			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new JLayoutFile('toolbar.newmodule');

			$bar->appendButton('Custom', $layout->render(array()), 'new');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('module.edit');
		}

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::custom('modules.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('modules.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('modules.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin('modules.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_modules') && $user->authorise('core.edit', 'com_modules')
			&& $user->authorise('core.edit.state', 'com_modules'))
		{
			JHtml::_('bootstrap.renderModal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('modules.trash');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_modules');
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_MODULE_MANAGER');

		if (JHtmlSidebar::getEntries())
		{
			$this->sidebar = JHtmlSidebar::render();
		}
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
		if ($this->getLayout() == 'default')
		{
			return array(
				'ordering'       => JText::_('JGRID_HEADING_ORDERING'),
				'a.published'    => JText::_('JSTATUS'),
				'a.title'        => JText::_('JGLOBAL_TITLE'),
				'position'       => JText::_('COM_MODULES_HEADING_POSITION'),
				'name'           => JText::_('COM_MODULES_HEADING_MODULE'),
				'pages'          => JText::_('COM_MODULES_HEADING_PAGES'),
				'a.access'       => JText::_('JGRID_HEADING_ACCESS'),
				'language_title' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.id'           => JText::_('JGRID_HEADING_ID')
			);
		}

		return array(
			'a.title'        => JText::_('JGLOBAL_TITLE'),
			'position'       => JText::_('COM_MODULES_HEADING_POSITION'),
			'name'           => JText::_('COM_MODULES_HEADING_MODULE'),
			'pages'          => JText::_('COM_MODULES_HEADING_PAGES'),
			'a.access'       => JText::_('JGRID_HEADING_ACCESS'),
			'language_title' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'           => JText::_('JGRID_HEADING_ID')
		);
	}
}
