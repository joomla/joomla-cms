<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Transitions;

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
	 * An array of transitions
	 *
	 * @var     array
	 * @since   4.0
	 */
	protected $transitions;

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
	 * Form object for search filters
	 *
	 * @var     \JForm
	 * @since   4.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var     array
	 * @since   4.0
	 */
	public $activeFilters;

	/**
	 * The ID of current workflow
	 *
	 * @var     integer
	 * @since   4.0
	 */
	protected $workflowID;

	/**
	 * The name of current extension
	 *
	 * @var     string
	 * @since   4.0
	 */
	protected $extension;

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

		$this->state            = $this->get('State');
		$this->transitions      = $this->get('Items');
		$this->pagination       = $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		$this->workflowID = $this->state->get("filter.workflow_id");
		$this->extension = $this->state->get("filter.extension");

		// Set the form selects sql
		$sqlStatesFrom = WorkflowHelper::getStatesSQL('from_state', $this->workflowID);
		$sqlStatesTo = WorkflowHelper::getStatesSQL('to_state', $this->workflowID);
		$this->filterForm->setFieldAttribute('from_state', 'query', $sqlStatesFrom);
		$this->filterForm->setFieldAttribute('to_state', 'query', $sqlStatesTo);

		WorkflowHelper::addSubmenu(
			implode(
				'.', array(
			"transitions",
			$this->workflowID,
			$this->extension
		)
		)
		);

		CategoriesHelper::addSubmenu($this->extension);
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
		ToolbarHelper::title(\JText::_('COM_WORKFLOW_TRANSITIONS_LIST'), 'address contact');
		ToolbarHelper::addNew('transition.add');
		ToolbarHelper::editList('transition.edit');
		ToolbarHelper::publishList('transitions.publish');
		ToolbarHelper::unpublishList('transitions.unpublish');
		ToolbarHelper::trash('transitions.trash');

		if ($this->state->get("filter.published") === "-2")
		{
			ToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'transitions.delete');
		}
	}
}
