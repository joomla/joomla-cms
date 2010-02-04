<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of plugins.
 *
 * @package		Joomla.Administrator
 * @subpackage	Plugins
 * @since		1.5
 */
class PluginsViewPlugins extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= PluginsHelper::getActions();

		JToolBarHelper::title(JText::_('COM_PLUGINS_MANAGER_PLUGINS'), 'plugin');

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('plugins.publish', 'publish.png', 'publish_f2.png', 'JToolbar_Enable', true);
			JToolBarHelper::custom('plugins.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JToolbar_Disable', true);
		}

		/**
		* Don't think we need an Edit button if names are clickable.
		*/
		//if ($canDo->get('core.edit')) {
		//	JToolBarHelper::editList('plugin.edit');
		// }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_plugins');
		}
		JToolBarHelper::help('screen.plugins','JTOOLBAR_HELP');
	}
}