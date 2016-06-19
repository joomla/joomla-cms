<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.file');

/**
 * Fields View
 *
 * @since  3.7
 */
class FieldsViewFields extends JViewLegacy
{

	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   12.2
	 */
	public function display ($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->context = JFactory::getApplication()->input->getCmd('context');
		$parts = FieldsHelper::extract($this->context);
		if (! $parts)
		{
			JError::raiseError(500, 'Invalid context!!');
			return;
		}
		$this->component = $parts[0];
		$this->section = $parts[1];

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return void
	 */
	protected function addToolbar ()
	{
		$fieldId = $this->state->get('filter.field_id');
		$user = JFactory::getUser();
		$component = $this->component;
		$section = $this->section;

		$canDo = new JObject;
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml'))
		{
			$canDo = JHelperContent::getActions($component, 'field', $fieldId);
		}

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true) ||
			$lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component . '_FIELDS_' . ($section ? $section : '')) . '_FIELDS_TITLE'))
		{
			$title = JText::_($component_title_key);
		}
		elseif ($lang->hasKey($component_section_key = strtoupper($component . '_FIELDS_SECTION_' . ($section ? $section : ''))))
		{
			// Else if the component section string exits, let's use it
			$title = JText::sprintf('COM_FIELDS_VIEW_FIELDS_TITLE', $this->escape(JText::_($component_section_key)));
		}
		else
		{
			$title = JText::_('COM_FIELDS_VIEW_FIELDS_BASE_TITLE');
		}

		// Load specific component css
		JHtml::_('stylesheet', $component . '/administrator/fields.css', array(), true);

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
		if ($user->authorise('core.create', $this->context) && $user->authorise('core.edit', $this->context)
			&&	$user->authorise('core.edit.state', $this->context))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(
					array(
						'title' => $title
					)
			);
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences($component);
		}

		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete', $component))
		{
			JToolbarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('fields.trash');
		}

		// Compute the ref_key if it does exist in the component
		if (! $lang->hasKey($ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_FIELDS_HELP_KEY'))
		{
			$ref_key = 'JHELP_COMPONENTS_' . strtoupper(substr($component, 4) . ($section ? "_$section" : '')) . '_FIELDS';
		}

		/*
		 * Get help for the fields view for the component by
		 * -remotely searching in a language defined dedicated URL:
		 * *component*_HELP_URL
		 * -locally searching in a component help file if helpURL param exists
		 * in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the
		 * component and is NOT set to ''
		 */
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		// JToolbarHelper::help($ref_key,
		// JComponentHelper::getParams($component)->exists('helpURL'), $url);
	}

	/**
	 * Returns the sort fields.
	 *
	 * @return string[]
	 */
	protected function getSortFields ()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.published' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'a.type' => JText::_('COM_FIELDS_FIELD_TYPE_LABEL'),
				'a.access' => JText::_('JGRID_HEADING_ACCESS'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
