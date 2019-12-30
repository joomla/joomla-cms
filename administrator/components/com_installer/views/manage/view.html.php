<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerViewDefault', dirname(__DIR__) . '/default/view.php');

/**
 * Extension Manager Manage View
 *
 * @since  1.6
 */
class InstallerViewManage extends InstallerViewDefault
{
	protected $items;

	protected $pagination;

	protected $form;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  mixed|void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Get data from the model.
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Display the view.
		parent::display($tpl);
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
		$canDo = JHelperContent::getActions('com_installer');

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('manage.publish', 'JTOOLBAR_ENABLE', true);
			JToolbarHelper::unpublish('manage.unpublish', 'JTOOLBAR_DISABLE', true);
			JToolbarHelper::divider();
		}

		JToolbarHelper::custom('manage.refresh', 'refresh', 'refresh', 'JTOOLBAR_REFRESH_CACHE', true);
		JToolbarHelper::divider();

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('COM_INSTALLER_CONFIRM_UNINSTALL', 'manage.remove', 'JTOOLBAR_UNINSTALL');
			JToolbarHelper::divider();
		}

		JHtmlSidebar::setAction('index.php?option=com_installer&view=manage');

		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_MANAGE');
	}
}
