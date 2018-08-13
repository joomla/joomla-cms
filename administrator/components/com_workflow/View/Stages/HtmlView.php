<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Stages;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Workflow\Workflow;
use Joomla\CMS\Language\Text;

/**
 * Stages view class for the Workflow package.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of stages
	 *
	 * @var     array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $stages;

	/**
	 * The model stage
	 *
	 * @var     object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $stage;

	/**
	 * The HTML for displaying sidebar
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

	/**
	 * The pagination object
	 *
	 * @var     \JPagination
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * Form object for search filters
	 *
	 * @var     \JForm
	 * @since  __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var     array
	 * @since  __DEPLOY_VERSION__
	 */
	public $activeFilters;

	/**
	 * The ID of current workflow
	 *
	 * @var     integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $workflowID;

	/**
	 * The name of current extension
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->state         = $this->get('State');
		$this->stages        = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		$this->workflowID = $this->state->get('filter.workflow_id');
		$this->extension = $this->state->get('filter.extension');

		WorkflowHelper::addSubmenu('stages');

		$this->sidebar       = \JHtmlSidebar::render();

		if (!empty($this->stages))
		{
			$workflow = new Workflow(['extension' => 'com_content']);

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
	 * @since  __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions($this->extension, 'workflow', $this->workflowID);

		$workflow = !empty($this->state->get('active_workflow', '')) ? $this->state->get('active_workflow', '') . ': ' : '';

		ToolbarHelper::title(Text::sprintf('COM_WORKFLOW_STAGES_LIST', $this->escape($workflow)), 'address contact');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('stage.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publishList('stages.publish');
			ToolbarHelper::unpublishList('stages.unpublish');
			ToolbarHelper::makeDefault('stages.setDefault', 'COM_WORKFLOW_TOOLBAR_SET_HOME');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::checkin('stages.checkin', 'JTOOLBAR_CHECKIN', true);
		}

		if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList(Text::_('COM_WORKFLOW_ARE_YOU_SURE'), 'stages.delete');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('stages.trash');
		}

		ToolbarHelper::help('JHELP_WORKFLOW_STAGES_LIST');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since  __DEPLOY_VERSION__
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
