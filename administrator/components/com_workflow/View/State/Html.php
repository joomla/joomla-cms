<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\State;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Helper\StateHelper;

/**
 * View class to add or edit Workflow
 *
 * @since  __DEPLOY_VERSION__
 */
class Html extends HtmlView
{
	/**
	 * The model state
	 *
	 * @var     object
	 * @since   4.0
	 */
	protected $state;

	/**
	 * From object to generate fields
	 *
	 * @var     \JForm
	 * @since  __DEPLOY_VERSION__
	 */
	protected $form;

	/**
	 * Items array
	 *
	 * @var     object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * The name of current extension
	 *
	 * @var     string
	 * @since   4.0
	 */
	protected $extension;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

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

		$this->canDo      = StateHelper::getActions($this->extension, 'state', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
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
		$isNew      = ($this->item->id == 0);

		$canDo = $this->canDo;

		ToolbarHelper::title(empty($this->item->id) ? \JText::_('COM_WORKFLOW_STATE_ADD') : \JText::_('COM_WORKFLOW_STATE_EDIT'), 'address');
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		$toolbarButtons = [['apply', 'state.apply'], ['save', 'state.save'], ['save2new', 'state.save2new']];

		if (!$isNew)
		{
			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'state.save2copy'];
			}
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		ToolbarHelper::cancel('state.cancel');
	}
}
