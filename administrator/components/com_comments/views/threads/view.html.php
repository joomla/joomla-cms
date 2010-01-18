<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Base controller class for JXtended Comments.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		1.3
 */
class CommentsViewThreads extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Assign data to the view.
		$this->assignRef('state', $state);
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= CommentsHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('Comments_Manager_Threads'), 'plugin');
		JToolBarHelper::deleteList('', 'threads.delete');
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_comments');
		}
		JToolBarHelper::help('screen.comments');
	}
}