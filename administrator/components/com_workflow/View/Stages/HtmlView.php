<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Stages;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * Stages view class for the Workflow package.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of stages
	 *
	 * @var     array
	 * @since  4.0.0
	 */
	protected $stages;

	/**
	 * The model stage
	 *
	 * @var     object
	 * @since  4.0.0
	 */
	protected $stage;

	/**
	 * The HTML for displaying sidebar
	 *
	 * @var     string
	 * @since  4.0.0
	 */
	protected $sidebar;

	/**
	 * The pagination object
	 *
	 * @var     \JPagination
	 * @since  4.0.0
	 */
	protected $pagination;

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
	 * The current workflow
	 *
	 * @var     object
	 * @since  4.0.0
	 */
	protected $workflow;

	/**
	 * The ID of current workflow
	 *
	 * @var     integer
	 * @since  4.0.0
	 */
	protected $workflowID;

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
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->state         = $this->get('State');
		$this->stages        = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->workflow      = $this->get('Workflow');
		$this->workflowID    = $this->workflow->id;
		$this->extension     = $this->workflow->extension;

		WorkflowHelper::addSubmenu('stages');

		$this->sidebar       = \JHtmlSidebar::render();

		if (!empty($this->stages))
		{
			$extension = Factory::getApplication()->input->getCmd('extension');
			$workflow  = new Workflow(['extension' => $extension]);

			foreach ($this->stages as $i => $item)
			{
				$item->condition = $workflow->getConditionName($item->condition);
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
	 * @since  4.0.0
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions($this->extension, 'workflow', $this->workflowID);

		$toolbar = Toolbar::getInstance('toolbar');

		$workflow = !empty($this->state->get('active_workflow', '')) ? Text::_($this->state->get('active_workflow', '')) . ': ' : '';

		ToolbarHelper::title(Text::sprintf('COM_WORKFLOW_STAGES_LIST', $this->escape($workflow)), 'address contact');

		$isCore = $this->workflow->core;
		$arrow  = Factory::getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

		ToolbarHelper::link('index.php?option=com_workflow&view=workflows&extension=' . $this->escape($this->workflow->extension),
			'JTOOLBAR_BACK', $arrow
		);

		if (!$isCore)
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('stage.add');
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

				$childBar->publish('stages.publish')->listCheck(true);
				$childBar->unpublish('stages.unpublish')->listCheck(true);
				$childBar->makeDefault('stages.setDefault', 'COM_WORKFLOW_TOOLBAR_DEFAULT');

				if ($canDo->get('core.admin'))
				{
					// @ToDo Imlement the checked_out for workflows
					// $childBar->checkin('stages.checkin', 'JTOOLBAR_CHECKIN', true);
				}

				if ($this->state->get('filter.published') !== '-2')
				{
					$childBar->trash('stages.trash');
				}
			}

			if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete') && !$isCore)
			{
				$toolbar->delete('stages.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		$toolbar->help('JHELP_WORKFLOW_STAGES_LIST');
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
