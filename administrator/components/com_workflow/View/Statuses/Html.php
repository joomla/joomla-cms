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
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * Workflows view class for the Workflow package.
 *
 * @since  4.0
 */
class Html extends HtmlView
{

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
		$this->state         = $this->get('State');
		$this->statuses      = $this->get('Items');

		WorkflowHelper::addSubmenu("statuses." . $this->state->get("fiter.workflow_id"));
		$this->sidebar       = \JHtmlSidebar::render();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
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
		\JToolbarHelper::title(\JText::_('COM_WORKFLOW_WORKFLOWS_LIST'), 'address contact');
		\JToolbarHelper::addNew('status.add');
		\JToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'statuses.delete');
		\JToolbarHelper::editList('status.edit');
	}
}
