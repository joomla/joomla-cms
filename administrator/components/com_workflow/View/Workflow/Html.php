<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Workflow;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\View\HtmlView;

/**
 * View class to add or edit Workflow
 *
 * @since  4.0
 */
class Html extends HtmlView
{

	protected $form;

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
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			\JError::raiseError(500, implode('<br />', $errors));

			return;
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
	 * @since   4.0
	 */
	protected function addToolbar()
	{
		\JToolbarHelper::title(empty($this->item->id) ? \JText::_('COM_WORKFLOW_WORKFLOWS_ADD') : \JText::_('COM_WORKFLOW_WORKFLOWS_EDIT'), 'address');
		\JFactory::getApplication()->input->set('hidemainmenu', true);
		\JToolbarHelper::saveGroup(
			[
				['apply', 'workflow.apply'],
				['save', 'workflow.save'],
				['save2new', 'workflow.save2new']
			],
			'btn-success'
		);
		\JToolbarHelper::cancel('workflow.cancel');
	}
}
