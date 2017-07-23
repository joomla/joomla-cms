<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\States;

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
	 * An array of states
	 *
	 * @var     array
	 * @since   4.0
	 */
	protected $states;

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
		$this->states        = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		WorkflowHelper::addSubmenu(
			implode(
				'.', array(
			"states",
			$this->state->get("filter.workflow_id"),
			$this->state->get("filter.extension")
		)
		)
		);
		CategoriesHelper::addSubmenu($this->state->get('filter.extension'));
		$this->sidebar       = \JHtmlSidebar::render();

		if (!empty($this->states))
		{
			foreach ($this->states as $i => $item)
			{
				$item->condition = WorkflowHelper::getConditionName($item->condition);
			}
		}

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
		ToolbarHelper::title(\JText::_('COM_WORKFLOW_STATES_LIST'), 'address contact');
		ToolbarHelper::addNew('state.add');
		ToolbarHelper::editList('state.edit');
		ToolbarHelper::publishList('states.publish');
		ToolbarHelper::unpublishList('states.unpublish');
		ToolbarHelper::archiveList('states.archive');
		ToolbarHelper::checkin('states.checkin', 'JTOOLBAR_CHECKIN', true);
		ToolbarHelper::makeDefault('states.setDefault', 'COM_WORKFLOW_TOOLBAR_SET_HOME');

		if ($this->state->get("filter.published") === "-2")
		{
			ToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'states.delete');
		}
		else
		{
			ToolbarHelper::trash('states.trash');
		}

		ToolbarHelper::help('JHELP_WORKFLOWS_LIST');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.published' => \JText::_('JSTATUS'),
			'a.title'     => \JText::_('JGLOBAL_TITLE'),
			'a.id'        => \JText::_('JGRID_HEADING_ID'),
		);
	}
}
