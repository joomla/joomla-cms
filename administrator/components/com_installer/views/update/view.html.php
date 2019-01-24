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
 * Extension Manager Update View
 *
 * @since  1.6
 */
class InstallerViewUpdate extends InstallerViewDefault
{
	/**
	 * List of update items.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Model state object.
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * List pagination.
	 *
	 * @var JPagination
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
		// Get data from the model.
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$paths = new stdClass;
		$paths->first = '';

		$this->paths = &$paths;

		if (count($this->items) > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_WARNINGS_UPDATE_NOTICE'), 'warning');
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
		JToolbarHelper::custom('update.update', 'upload', 'upload', 'COM_INSTALLER_TOOLBAR_UPDATE', true);
		JToolbarHelper::custom('update.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_UPDATES', false);
		JToolbarHelper::custom('update.purge', 'purge', 'purge', 'COM_INSTALLER_TOOLBAR_PURGE', false);
		JToolbarHelper::divider();

		JHtmlSidebar::setAction('index.php?option=com_installer&view=manage');

		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_UPDATE');
	}
}
