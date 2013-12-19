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
 * View for plugin editing
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       3.2
 */
class PluginsViewPluginHtml extends JViewHtmlCmsform
{

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$canDo = JHelperContent::getActions(0, 0, 'com_plugins');

		JToolbarHelper::title(JText::sprintf('COM_PLUGINS_MANAGER_PLUGIN', JText::_($this->name)), 'plugin');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('plugins.updateitem.plugin.apply');
			JToolbarHelper::save('plugins.updateitem.plugin.save');
		}

		JToolbarHelper::cancel('j.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::divider();

		// Get the help information for the plugin item.
		JToolbarHelper::help('JHELP_EXTENSIONS_PLUGIN_MANAGER_EDIT');

		$lang = JFactory::getLanguage();
		JHtmlSidebar::setAction('index.php?option=com_plugins');

	}

}
