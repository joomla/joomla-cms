<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/../default/view.php';

/**
 * Extension Manager Discover View
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerViewDiscover extends InstallerViewDefault
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
		// Get data from the model
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function addToolbar()
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolbarHelper::custom('discover.install', 'upload', 'upload', 'JTOOLBAR_INSTALL', true, false);
		JToolbarHelper::custom('discover.refresh', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DISCOVER', false, false);
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DISCOVER');

		JHtmlSidebar::setAction('index.php?option=com_installer&view=discover');

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_CLIENT_SELECT'),
			'filter_client_id',
			JHtml::_('select.options', array('0' => 'JSITE', '1' => 'JADMINISTRATOR'), 'value', 'text', $this->state->get('filter.client_id'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_TYPE_SELECT'),
			'filter_type',
			JHtml::_('select.options', InstallerHelper::getExtensionTypes(), 'value', 'text', $this->state->get('filter.type'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_FOLDER_SELECT'),
			'filter_group',
			JHtml::_('select.options', array_merge(InstallerHelper::getExtensionGroupes(), array('*' => JText::_('COM_INSTALLER_VALUE_FOLDER_NONAPPLICABLE'))), 'value', 'text', $this->state->get('filter.group'), true)
		);

		parent::addToolbar();
	}
}
