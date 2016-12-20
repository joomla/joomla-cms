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
 * Fields View
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsViewFields extends JViewLegacy
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

		FieldsHelperInternal::addSubmenu($this->state->get('filter.context'), 'fields');
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
		$fieldId   = $this->state->get('filter.field_id');
		$component = $this->state->get('filter.component');
		$section   = $this->state->get('filter.section');
		$canDo     = JHelperContent::getActions($component, 'field', $fieldId);

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load extension language file
		JFactory::getLanguage()->load($component, JPATH_ADMINISTRATOR);

		$title = JText::sprintf('COM_FIELDS_VIEW_FIELDS_TITLE', JText::_(strtoupper($component)));

		// Prepare the toolbar.
		JToolbarHelper::title($title, 'puzzle fields ' . substr($component, 4) . ($section ? "-$section" : '') . '-fields');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('field.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('field.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('fields.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('fields.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('fields.archive');
		}

		if (JFactory::getUser()->authorise('core.admin'))
		{
			JToolbarHelper::checkin('fields.checkin');
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
			JToolbarHelper::preferences($component);
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $component))
		{
			JToolbarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('fields.trash');
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
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'    => JText::_('JSTATUS'),
			'a.title'    => JText::_('JGLOBAL_TITLE'),
			'a.type'     => JText::_('COM_FIELDS_FIELD_TYPE_LABEL'),
			'a.access'   => JText::_('JGRID_HEADING_ACCESS'),
			'language'   => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'       => JText::_('JGRID_HEADING_ID'),
		);
	}
}
