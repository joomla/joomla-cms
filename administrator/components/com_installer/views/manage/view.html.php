<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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

	/**
	 * Creates the content for the tooltip which shows compatibility information
	 *
	 * @var  string  $system_data  System_data information
	 *
	 * @since  2.5.28
	 *
	 * @return  string  Content for tooltip
	 */
	protected function createCompatibilityInfo($system_data)
	{
		$system_data = json_decode($system_data);

		if (empty($system_data->compatibility))
		{
			return '';
		}

		$compatibility = $system_data->compatibility;

		$info = JText::sprintf('COM_INSTALLER_COMPATIBILITY_TOOLTIP_INSTALLED',
					$compatibility->installed->version,
					implode(', ', $compatibility->installed->value)
				)
				. '<br/>'
				. JText::sprintf('COM_INSTALLER_COMPATIBILITY_TOOLTIP_AVAILABLE',
					$compatibility->available->version,
					implode(', ', $compatibility->available->value)
				);

		return $info;
	}
}
