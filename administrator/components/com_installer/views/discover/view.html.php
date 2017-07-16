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
		// Run discover from the model.
		if (!$this->checkExtensions())
		{
			$this->getModel('discover')->discover();
		}

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

		// Get database model for later check for incomplete core update
		$modelDb = JModelLegacy::getInstance('database', 'InstallerModel');

		if (!($modelDb instanceof InstallerModelDatabase))
		{
			throw new Exception('Could not load database model', 600);
		}

		$changeSet = $modelDb->getItems();

		$changeSetVersion = $changeSet ? $changeSet->getSchema() : JText::_('JNONE');
		$schemaVersion    = $modelDb->getSchemaVersion() ?: JText::_('JNONE');
		$updateVersion    = $modelDb->getUpdateVersion() ?: JText::_('JNONE');

		$this->incompleteCoreUpdate = false;

		// Check for incomplete core update
		if (version_compare($schemaVersion, $changeSetVersion) < 0
		&& version_compare($updateVersion, JVERSION) < 0)
		{
			$this->incompleteCoreUpdate = true;

			JFactory::getApplication()->enqueueMessage(
				JText::sprintf(
					'COM_INSTALLER_MSG_DISCOVER_INCOMPLETE_UPDATE',
					JText::_('COM_INSTALLER_TOOLBAR_FINALISE_CORE_UPDATE'),
					JText::_('JTOOLBAR_INSTALL')
				),
				'warning'
			);
		}

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

		if ($this->incompleteCoreUpdate)
		{
			JToolbarHelper::custom('discover.finaliseUpdate', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FINALISE_CORE_UPDATE', false);
			JToolbarHelper::divider();
		}

		JHtmlSidebar::setAction('index.php?option=com_installer&view=discover');

		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DISCOVER');
	}

	/**
	 * Check extensions.
	 *
	 * Checks uninstalled extensions in extensions table.
	 *
	 * @return  boolean  True if there are discovered extensions on the database.
	 *
	 * @since   3.5
	 */
	public function checkExtensions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('state') . ' = -1');
		$db->setQuery($query);
		$discoveredExtensions = $db->loadObjectList();

		return (count($discoveredExtensions) === 0) ? false : true;
	}
}
