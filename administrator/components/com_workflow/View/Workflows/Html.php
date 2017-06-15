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

/**
 * Workflows view class for the Workflow package.
 *
 * @since  1.6
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
//		$this->workflows = $this->get('Items');
//		$this->pagination = $this->get('Pagination');
//		$this->state = $this->get('State');
//		$this->addToolbar();
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
		\JToolbarHelper::title(JText::_('COM_PROVE_LIST_EMAILS'), 'address contact');
		\JToolbarHelper::addNew('workflow.add');
		\JToolbarHelper::publish('workflows.publish', 'JTOOLBAR_PUBLISH', true);
		\JToolbarHelper::unpublish('workflows.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		\JToolbarHelper::trash('workflows.trash');
		\JToolBarHelper::deleteList('Are you sure ?', 'workflows.delete');
		\JToolbarHelper::archiveList('workflows.archive');
	}
}
