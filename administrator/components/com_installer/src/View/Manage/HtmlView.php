<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Manage;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * Extension Manager Manage View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
	/**
	 * List of updatesites
	 *
	 * @var    \stdClass[]
	 */
	protected $items;

	/**
	 * Pagination object
	 *
	 * @var    Pagination
	 */
	protected $pagination;

	/**
	 * Form object
	 *
	 * @var    Form
	 */
	protected $form;

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
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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
		$canDo = ContentHelper::getActions('com_installer');

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('manage.publish', 'JTOOLBAR_ENABLE', true);
			ToolbarHelper::unpublish('manage.unpublish', 'JTOOLBAR_DISABLE', true);
			ToolbarHelper::divider();
		}

		ToolbarHelper::custom('manage.refresh', 'refresh', 'refresh', 'JTOOLBAR_REFRESH_CACHE', true);
		ToolbarHelper::divider();

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('COM_INSTALLER_CONFIRM_UNINSTALL', 'manage.remove', 'JTOOLBAR_UNINSTALL');
			ToolbarHelper::divider();
		}

		parent::addToolbar();
		ToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_MANAGE');
	}
}
