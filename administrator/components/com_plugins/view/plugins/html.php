<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       3.2
 */
class PluginsViewPluginsHtml extends JViewHtmlCmslist
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  3.2
	 */
	protected function addToolbar()
	{
		$canDo = PluginsHelperPlugins::getActions(0, 0, 'com_plugins');

		JToolbarHelper::title(JText::_('COM_PLUGINS_MANAGER_PLUGINS'), 'plugin');

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('j.displayform.plugin.edit','JTOOLBAR_EDIT',true);
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('j.updatestatelist.published', 'JTOOLBAR_ENABLE', true);
			JToolbarHelper::unpublish('j.updatestatelist.unpublished', 'JTOOLBAR_DISABLE', true);
			JToolbarHelper::checkin('j.checkin');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_plugins');
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_PLUGIN_MANAGER');

		JHtmlSidebar::setAction('index.php?option=com_plugins&view=plugins');

		JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_enabled',
				JHtml::_('select.options', PluginsHelperPlugins::publishedOptions(), 'value', 'text', $this->state->get('filter.enabled'), true)
		);

		JHtmlSidebar::addFilter(
				JText::_('COM_PLUGINS_OPTION_FOLDER'),
				'filter_folder',
				JHtml::_('select.options', PluginsHelperPlugins::folderOptions(), 'value', 'text', $this->state->get('filter.folder'))
		);

		JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_ACCESS'),
				'filter_access',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		$this->sidebar = JHtmlSidebar::render();
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
				'ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'name' => JText::_('JGLOBAL_TITLE'),
				'folder' => JText::_('COM_PLUGINS_FOLDER_HEADING'),
				'element' => JText::_('COM_PLUGINS_ELEMENT_HEADING'),
				'access' => JText::_('JGRID_HEADING_ACCESS'),
				'extension_id' => JText::_('JGRID_HEADING_ID')
		);
	}

}