<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/../default/view.php';

/**
 * Extension Manager Discover View
 *
 * @since  1.6
 */
class InstallerViewDiscover extends InstallerViewDefault
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Get data from the model.
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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
		 * Set toolbar items for the page.
		 */
		JToolbarHelper::custom('discover.install', 'upload', 'upload', 'JTOOLBAR_INSTALL', true);
		JToolbarHelper::custom('discover.refresh', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DISCOVER', false);
		JToolbarHelper::divider();

		JHtmlSidebar::setAction('index.php?option=com_installer&view=discover');

		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DISCOVER');
	}
}
