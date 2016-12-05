<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Groups View
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsViewGroups extends JViewLegacy
{
	/**
	 * @var  JForm
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $activeFilters;

	/**
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * @var  JPagination
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Display a warning if the fields system plugin is disabled
		if (!JPluginHelper::isEnabled('system', 'fields'))
		{
			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FieldsHelper::getFieldsPluginId());
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_FIELDS_SYSTEM_PLUGIN_NOT_ENABLED', $link), 'warning');
		}

		$this->addToolbar();

		FieldsHelperInternal::addSubmenu($this->state->get('filter.extension'), 'groups');
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$groupId   = $this->state->get('filter.group_id');
		$extension = $this->state->get('filter.extension');
		$canDo     = JHelperContent::getActions($extension, 'fieldgroup', $groupId);

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($extension == 'com_fields')
		{
			return;
		}

		// Load extension language file
		JFactory::getLanguage()->load($extension, JPATH_ADMINISTRATOR);

		$title = JText::sprintf('COM_FIELDS_VIEW_GROUPS_TITLE', JText::_(strtoupper($extension)));

		// Prepare the toolbar.
		JToolbarHelper::title($title, 'puzzle fields ' . substr($extension, 4) . '-groups');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('group.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('group.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('groups.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('groups.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('groups.archive');
		}

		if (JFactory::getUser()->authorise('core.admin'))
		{
			JToolbarHelper::checkin('groups.checkin');
		}

		// Add a batch button
		if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(
				array(
					'title' => $title,
				)
			);

			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences($extension);
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $extension))
		{
			JToolbarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('groups.trash');
		}
	}

	/**
	 * Returns the sort fields.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'  => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'     => JText::_('JSTATUS'),
			'a.title'     => JText::_('JGLOBAL_TITLE'),
			'a.access'    => JText::_('JGRID_HEADING_ACCESS'),
			'language'    => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.extension' => JText::_('JGRID_HEADING_EXTENSION'),
			'a.id'        => JText::_('JGRID_HEADING_ID'),
		);
	}
}
