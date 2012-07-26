<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once dirname(__FILE__).'/../default/view.php';

/**
 * Extension Manager Update View
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerViewUpdate extends InstallerViewDefault
{
	/**
	 * @since	1.6
	 */
	function display($tpl=null)
	{
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
<<<<<<< HEAD
		$this->form	= $this->get('Form');
		$paths = new stdClass();
=======

		$paths = new stdClass;
>>>>>>> fde890837c088df43d66ddec9a0402cc911fb84d
		$paths->first = '';

		JError::raiseNotice(500, JText::_('COM_INSTALLER_MSG_WARNINGS_UPDATE_NOTICE'));
		$this->assignRef('paths', $paths);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= InstallerHelper::getActions();

		JToolBarHelper::custom('update.update', 'upload', 'upload', 'COM_INSTALLER_TOOLBAR_UPDATE', true, false);
		JToolBarHelper::custom('update.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_UPDATES', false, false);
		JToolBarHelper::custom('update.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false, false);
		JToolBarHelper::divider();
		parent::addToolbar();
		JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_UPDATE');
	}
}
