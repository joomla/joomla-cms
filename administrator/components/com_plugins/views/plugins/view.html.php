<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of plugins.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.5
 */
class PluginsViewPlugins extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

			// Check if there are no matching items
		if(!count($this->items)){
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_PLUGINS_MSG_MANAGE_NO_PLUGINS')
				, 'warning'
			);
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= PluginsHelper::getActions();

		JToolBarHelper::title(JText::_('COM_PLUGINS_MANAGER_PLUGINS'), 'plugin');

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('plugin.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('plugins.publish', 'JTOOLBAR_ENABLE', true);
			JToolBarHelper::unpublish('plugins.unpublish', 'JTOOLBAR_DISABLE', true);
			JToolBarHelper::divider();
			JToolBarHelper::checkin('plugins.checkin');
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_plugins');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_PLUGIN_MANAGER');
	}
}
