<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\View\Database;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * Extension Manager Database View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
	/**
	 * List of change sets
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $changeSet = array();

	/**
	 * The number of errors found
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $errorCount = 0;

	/**
	 * List pagination.
	 *
	 * @var    Pagination
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The filter form
	 *
	 * @var    Form
	 * @since  __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * A list of form filters
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public $activeFilters = array();

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Get the application
		$app = Factory::getApplication();

		// Get data from the model.
		/** @var DatabaseModel $model */
		$model = $this->getModel();

		try
		{
			$this->changeSet = $model->getItems();
		}
		catch (\Exception $exception)
		{
			$app->enqueueMessage($exception->getMessage(), 'error');
		}

		$this->errorCount    = $model->getErrorCount();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		if ($this->changeSet)
		{
			($this->errorCount === 0)
			? $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_OK'), 'info')
			: $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_ERRORS'), 'warning');
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
		ToolbarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', true);
		ToolbarHelper::divider();
		parent::addToolbar();
		ToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DATABASE');
	}
}
