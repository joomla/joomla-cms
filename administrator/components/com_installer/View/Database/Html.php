<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\View\Database;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Installer\Administrator\View\Installer\Html as InstallerViewDefault;

/**
 * Extension Manager Database View
 *
 * @since  1.6
 */
class Html extends InstallerViewDefault
{
	/**
	 * List pagination.
	 *
	 * @var \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

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
		// Set variables
		$app = \JFactory::getApplication();

		// Get data from the model.
		$this->changeSet      = $this->get('Items');
		$this->errorCount     = $this->get('ErrorCount');
		$this->pagination     = $this->get('Pagination');
		$this->filterForm     = $this->get('FilterForm');
		$this->activeFilters  = $this->get('ActiveFilters');

		if ($this->errorCount === 0)
		{
			$app->enqueueMessage(\JText::_('COM_INSTALLER_MSG_DATABASE_CORE_OK'), 'info');
		}
		else
		{
			// Database Core Errors
			$app->enqueueMessage(\JText::_('COM_INSTALLER_MSG_DATABASE_CORE_ERRORS'), 'warning');
		}

		// Include the component HTML helpers.
		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

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
		/*
		 * Set toolbar items for the page.
		 */
		ToolbarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', false);

		ToolbarHelper::custom('database.findproblems', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_PROBLEMS', false);
		ToolbarHelper::divider();
		parent::addToolbar();
		ToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DATABASE');
	}
}
