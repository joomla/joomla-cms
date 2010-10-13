<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewDebugGroup extends JView
{
	protected $actions;
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->actions		= $this->get('DebugActions');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->group		= $this->get('Group');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->levels = array(
			JHtml::_('select.option', '1', JText::sprintf('COM_USERS_OPTION_LEVEL_COMPONENT', 1)),
			JHtml::_('select.option', '2', JText::sprintf('COM_USERS_OPTION_LEVEL_CATEGORY', 2)),
			JHtml::_('select.option', '3', JText::sprintf('COM_USERS_OPTION_LEVEL_DEEPER', 3)),
			JHtml::_('select.option', '4', '4'),
			JHtml::_('select.option', '5', '5'),
			JHtml::_('select.option', '6', '6'),
		);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::sprintf('COM_USERS_VIEW_DEBUG_GROUP_TITLE', $this->group->id, $this->group->title), 'groups');

		JToolBarHelper::help('JHELP_USERS_DEBUG_GROUPS');
	}
}
