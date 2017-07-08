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
		$this->changeSet        = $this->get('Items');
		$this->errors           = $this->changeSet['core']['changeset']->check();
		$this->results          = $this->changeSet['core']['changeset']->getStatus();
		$this->schemaVersion    = $this->get('SchemaVersion');
		$this->updateVersion    = $this->get('UpdateVersion');
		$this->filterParams     = $this->get('DefaultTextFilters');
		$this->schemaVersion    = ($this->schemaVersion) ? $this->schemaVersion : \JText::_('JNONE');
		$this->updateVersion    = ($this->updateVersion) ? $this->updateVersion : \JText::_('JNONE');
		$this->pagination       = $this->get('Pagination');
		$this->errorCount       = count($this->errors);
		$this->filterForm       = $this->get('FilterForm');
		$this->activeFilters    = $this->get('ActiveFilters');
		$this->errorCount3rd    = 0;

		// We need to check the errors in the changeset but exclude the core database
		foreach ($this->changeSet as $i => $changeset)
		{
			if (strcmp($i, 'core') == 0)
			{
				continue;
			}

			if (strcmp($changeset['schema'], $changeset['extension']->version_id) != 0)
			{
				$this->errorCount3rd++;
			}
		}

		if ($this->schemaVersion != $this->changeSet['core']['changeset']->getSchema())
		{
			$this->errorCount++;
		}

		if (!$this->filterParams)
		{
			$this->errorCount++;
		}

		if (version_compare($this->updateVersion, \JVERSION) != 0)
		{
			$this->errorCount++;
		}

		if ($this->errorCount3rd === 0)
		{
			$app->enqueueMessage(\JText::_('COM_INSTALLER_MSG_DATABASE_3RD_OK'), 'info');
		}
		else
		{
			$app->enqueueMessage(\JText::plural('COM_INSTALLER_MSG_DATABASE_3RD_ERRORS', $this->errorCount3rd), 'warning');
		}

		if ($this->errorCount === 0)
		{
			$app->enqueueMessage(\JText::_('COM_INSTALLER_MSG_DATABASE_CORE_OK'), 'info');
		}
		else
		{
			// Database Core Errors
			$app->enqueueMessage(\JText::_('COM_INSTALLER_MSG_DATABASE_CORE_ERRORS'), 'warning');
		}

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
		if ($this->errorCount != 0)
		{
			ToolbarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', false);
		}

		if ($this->errorCount3rd != 0)
		{
			ToolbarHelper::custom('database.fix3rd', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX_3RD', false);
		}

		ToolbarHelper::divider();
		parent::addToolbar();
		ToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DATABASE');
	}
}
