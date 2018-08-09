<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Workflow;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * View class to add or edit Workflow
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var     object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

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
	 * Display item view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->state      = $this->get('State');
		$this->form       = $this->get('Form');
		$this->item       = $this->get('Item');
		$this->extension  = $this->state->get('filter.extension');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
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
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = empty($this->item->id);

		$canDo = WorkflowHelper::getActions($this->extension, 'workflow', $this->item->id);

		ToolbarHelper::title(empty($this->item->id) ? \JText::_('COM_WORKFLOW_WORKFLOWS_ADD') : \JText::_('COM_WORKFLOW_WORKFLOWS_EDIT'), 'address');

		$toolbarButtons = [];

		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.edit'))
			{
				$toolbarButtons = [['apply', 'workflow.apply'], ['save', 'workflow.save'], ['save2new', 'workflow.save2new']];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			if ($itemEditable)
			{
				$toolbarButtons = [['apply', 'workflow.apply'], ['save', 'workflow.save']];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'workflow.save2new'];
				}
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);
		}

		ToolbarHelper::cancel('workflow.cancel');
	}
}
