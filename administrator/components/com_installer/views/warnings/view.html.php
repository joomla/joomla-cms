<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerViewDefault', dirname(__DIR__) . '/default/view.php');

/**
 * Extension Manager Templates View
 *
 * @since  1.6
 */
class InstallerViewWarnings extends InstallerViewDefault
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$items = $this->get('Items');
		$this->messages = &$items;
		parent::display($tpl);

		if (count($items) > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_WARNINGS_NOTICE'), 'warning');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_WARNINGS_NONE'), 'info');
		}
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
		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_WARNINGS');
	}
}
