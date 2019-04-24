<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Workflows;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * Workflows view class for the Workflow package.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of workflows
	 *
	 * @var     array
	 * @since  4.0.0
	 */
	protected $workflows;

	/**
	 * The model state
	 *
	 * @var     object
	 * @since  4.0.0
	 */
	protected $state;

	/**
	 * The pagination object
	 *
	 * @var     \JPagination
	 * @since  4.0.0
	 */
	protected $pagination;

	/**
	 * The HTML for displaying sidebar
	 *
	 * @var     string
	 * @since  4.0.0
	 */
	protected $sidebar;

	/**
	 * Form object for search filters
	 *
	 * @var     \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var     array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * The name of current extension
	 *
	 * @var     string
	 * @since  4.0.0
	 */
	protected $extension;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  4.0.0
	 */
	public function display($tpl = null)
	{
		$this->state         	= $this->get('State');
		$this->workflows    	= $this->get('Items');
		$this->pagination    	= $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->extension = $this->state->get('filter.extension');

		WorkflowHelper::addSubmenu($this->state->get('filter.extension'));
		$this->sidebar       = \JHtmlSidebar::render();

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions($this->extension);

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_WORKFLOW_WORKFLOWS_LIST'), 'address contact');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('workflow.add');
		}

		if ($canDo->get('core.edit.state') || $user->authorise('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-globe')
				->buttonClass('btn btn-info')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('workflows.publish');
			$childBar->unpublish('workflows.unpublish');
			$childBar->makeDefault('workflows.setDefault', 'COM_WORKFLOW_TOOLBAR_DEFAULT');

			if ($canDo->get('core.admin'))
			{
				// @Todo implement the checked_out/checkin feature
				// $childBar->checkin('workflows.checkin');
			}

			if ($canDo->get('core.edit.state') && $this->state->get('filter.published') != -2)
			{
				$childBar->trash('workflows.trash');
			}
		}

		if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete'))
		{
			$toolbar->delete('workflows.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences($this->extension);
		}

		$toolbar->help('JHELP_WORKFLOWS_LIST');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since  4.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.published' => Text::_('JSTATUS'),
			'a.title'     => Text::_('JGLOBAL_TITLE'),
			'a.id'        => Text::_('JGRID_HEADING_ID'),
		);
	}
}
