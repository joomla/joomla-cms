<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once dirname(__FILE__).'/../default/view.php';

/**
 * Extension Manager Manage View
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerViewManage extends InstallerViewDefault
{
	protected $items;
	protected $pagination;
	protected $form;
	protected $state;

	/**
	 * @since	1.6
	 */
	function display($tpl=null)
	{
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->form			= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		//Check if there are no matching items
		if(!count($this->items)){
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_INSTALLER_MSG_MANAGE_NOEXTENSION')
				, 'warning'
			);
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Display the view
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
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publish('manage.publish', 'JTOOLBAR_ENABLE', true);
			JToolBarHelper::unpublish('manage.unpublish', 'JTOOLBAR_DISABLE', true);
			JToolBarHelper::divider();
		}
		JToolBarHelper::custom('manage.refresh', 'refresh', 'refresh', 'JTOOLBAR_REFRESH_CACHE', true);
		JToolBarHelper::divider();
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'manage.remove', 'JTOOLBAR_UNINSTALL');
			JToolBarHelper::divider();
		}
		parent::addToolbar();
		JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_MANAGE');
	}
}
