<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Groups View
 *
 * @since  3.7.0
 */
class FieldsViewGroups extends JViewLegacy
{
	/**
	 * @var  JForm
	 *
	 * @since  3.7.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	public $activeFilters;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * @var  JPagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * @var  string
	 *
	 * @since  3.7.0
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
	 * @since   3.7.0
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
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display a warning if the fields system plugin is disabled
		if (!JPluginHelper::isEnabled('system', 'fields'))
		{
			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FieldsHelper::getFieldsPluginId());
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_FIELDS_SYSTEM_PLUGIN_NOT_ENABLED', $link), 'warning');
		}

		$this->addToolbar();

		FieldsHelper::addSubmenu($this->state->get('filter.context'), 'groups');
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$groupId   = $this->state->get('filter.group_id');
		$component = '';
		$parts     = FieldsHelper::extract($this->state->get('filter.context'));

		if ($parts)
		{
			$component = $parts[0];
		}

		$canDo     = JHelperContent::getActions($component, 'fieldgroup', $groupId);

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = JFactory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = JText::sprintf('COM_FIELDS_VIEW_GROUPS_TITLE', JText::_(strtoupper($component)));

		// Prepare the toolbar.
		JToolbarHelper::title($title, 'puzzle fields ' . substr($component, 4) . '-groups');

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
			JToolbarHelper::preferences($component);
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $component))
		{
			JToolbarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('groups.trash');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELD_GROUPS');
	}

	/**
	 * Returns the sort fields.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'  => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'     => JText::_('JSTATUS'),
			'a.title'     => JText::_('JGLOBAL_TITLE'),
			'a.access'    => JText::_('JGRID_HEADING_ACCESS'),
			'language'    => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.context'   => JText::_('JGRID_HEADING_CONTEXT'),
			'a.id'        => JText::_('JGRID_HEADING_ID'),
		);
	}
}
