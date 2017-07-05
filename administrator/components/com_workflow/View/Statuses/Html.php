<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Statuses;

defined('_JEXEC') or die;

use Joomla\CMS\View\HtmlView;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Workflows view class for the Workflow package.
 *
 * @since  4.0
 */
class Html extends HtmlView
{
	/**
	 * An array of statuses
	 *
	 * @var     array
	 * @since   4.0
	 */
	protected $statuses;

	/**
	 * The model state
	 *
	 * @var     object
	 * @since   4.0
	 */
	protected $state;

	/**
	 * The HTML for displaying sidebar
	 *
	 * @var     string
	 * @since   4.0
	 */
	protected $sidebar;

	/**
	 * The pagination object
	 *
	 * @var     \JPagination
	 * @since   4.0
	 */
	protected $pagination;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   4.0
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->state         = $this->get('State');
		$this->statuses      = $this->get('Items');
		$this->pagination    = $this->get('Pagination');

		WorkflowHelper::addSubmenu("statuses." . $this->state->get("filter.workflow_id"));
		CategoriesHelper::addSubmenu($this->state->get('filter.extension'));
		$this->sidebar       = \JHtmlSidebar::render();


		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(\JText::_('COM_WORKFLOW_STATUSES_LIST'), 'address contact');
		ToolbarHelper::addNew('status.add');
		ToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'statuses.delete');
		ToolbarHelper::editList('status.edit');
	}
}
