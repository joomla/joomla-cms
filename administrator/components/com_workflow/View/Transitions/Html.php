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
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

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

		WorkflowHelper::addSubmenu("transitions." . $this->state->get("filter.workflow_id"));
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
		\JToolbarHelper::title(\JText::_('COM_WORKFLOW_TRANSITIONS_LIST'), 'address contact');
		\JToolbarHelper::addNew('transition.add');
		\JToolbarHelper::deleteList(\JText::_('COM_WORKFLOW_ARE_YOU_SURE'), 'transitions.delete');
		\JToolbarHelper::editList('transition.edit');
	}
}
