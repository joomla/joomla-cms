<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Workflows;

defined('_JEXEC') or die;

use Joomla\CMS\View\HtmlView;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Workflows view class for the Workflow package.
 *
 * @since  4.0
 */
class Html extends HtmlView
{
	/**
	 * An array of workflows
	 *
	 * @var     array
	 * @since   4.0
	 */
	protected $workflows;

	/**
	 * The model state
	 *
	 * @var     object
	 * @since   4.0
	 */
	protected $state;

	/**
	 * The pagination object
	 *
	 * @var     \JPagination
	 * @since   4.0
	 */
	protected $pagination;

	/**
	 * The HTML for displaying sidebar
	 *
	 * @var     string
	 * @since   4.0
	 */
	protected $sidebar;

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
		$app = \JFactory::getApplication();
		$context = "com_workflow.list.admin.workflows";
		$this->state         = $this->get('State');
		$this->workflows     = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

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
		ToolbarHelper::title(\JText::_('COM_WORKFLOW_WORKFLOWS_LIST'), 'address contact');
		ToolbarHelper::addNew('workflow.add');
		ToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'workflows.delete');
		ToolbarHelper::editList('workflow.edit');
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
			'a.lft'       => \JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => \JText::_('JSTATUS'),
			'a.title'     => \JText::_('JGLOBAL_TITLE'),
			'a.access'    => \JText::_('JGRID_HEADING_ACCESS'),
			'language'    => \JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'        => \JText::_('JGRID_HEADING_ID'),
		);
	}
}
