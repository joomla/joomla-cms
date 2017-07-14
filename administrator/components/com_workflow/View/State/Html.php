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

use Joomla\CMS\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class to add or edit Workflow
 *
 * @since  4.0
 */
class Html extends HtmlView
{
	/**
	 * From object to generate fields
	 *
	 * @var     \JForm
	 * @since   4.0
	 */
	protected $form;

	/**
	 * Items array
	 *
	 * @var     object
	 * @since   4.0
	 */
	protected $item;

	/**
	 * Display item view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
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

		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');


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
	 * @since   4.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(empty($this->item->id) ? \JText::_('COM_WORKFLOW_STATE_ADD') : \JText::_('COM_WORKFLOW_STATE_EDIT'), 'address');
		\JFactory::getApplication()->input->set('hidemainmenu', true);
		ToolbarHelper::saveGroup(
			[
				['apply', 'state.apply'],
				['save', 'state.save'],
				['save2new', 'state.save2new']
			],
			'btn-success'
		);
		ToolbarHelper::cancel('state.cancel');
	}
}
