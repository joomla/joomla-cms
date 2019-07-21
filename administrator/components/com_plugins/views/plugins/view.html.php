<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of plugins.
 *
 * @since  1.5
 */
class PluginsViewPlugins extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
		$canDo = JHelperContent::getActions('com_plugins');

		JToolbarHelper::title(JText::_('COM_PLUGINS_MANAGER_PLUGINS'), 'power-cord plugin');

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('plugin.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('plugins.publish', 'JTOOLBAR_ENABLE', true);
			JToolbarHelper::unpublish('plugins.unpublish', 'JTOOLBAR_DISABLE', true);
			JToolbarHelper::checkin('plugins.checkin');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_plugins');
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_PLUGIN_MANAGER');

	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value.
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'enabled'      => JText::_('JSTATUS'),
			'name'         => JText::_('JGLOBAL_TITLE'),
			'folder'       => JText::_('COM_PLUGINS_FOLDER_HEADING'),
			'element'      => JText::_('COM_PLUGINS_ELEMENT_HEADING'),
			'access'       => JText::_('JGRID_HEADING_ACCESS'),
			'extension_id' => JText::_('JGRID_HEADING_ID'),
		);
	}
}
