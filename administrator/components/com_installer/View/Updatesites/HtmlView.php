<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Updatesites;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\Model\UpdatesitesModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * Extension Manager Update Sites View
 *
 * @since  3.4
 */
class HtmlView extends InstallerViewDefault
{
	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  3.4
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  3.4
	 */
	public $activeFilters = [];

	/**
	 * List of updatesites
	 *
	 * @var    \stdClass[]
	 * @since 3.4
	 */
	protected $items;

	/**
	 * Pagination object
	 *
	 * @var    Pagination
	 * @since 3.4
	 */
	protected $pagination;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  mixed|void
	 *
	 * @since   3.4
	 *
	 * @throws  \Exception on errors
	 */
	public function display($tpl = null): void
	{
		/** @var UpdatesitesModel $model */
		$model               = $this->getModel();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Display the view
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function addToolbar(): void
	{
		$canDo = ContentHelper::getActions('com_installer');

		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::editList('updatesite.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('updatesites.publish', 'JTOOLBAR_ENABLE', true);
			ToolbarHelper::unpublish('updatesites.unpublish', 'JTOOLBAR_DISABLE', true);
			ToolbarHelper::divider();
		}

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'updatesites.delete');
			ToolbarHelper::divider();
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::checkin('updatesites.checkin');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::custom('updatesites.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}

		parent::addToolbar();

		ToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_UPDATESITES');
	}
}
