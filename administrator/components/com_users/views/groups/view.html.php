<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of user groups.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.6
 */
class UsersViewGroups extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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
		$canDo	= UsersHelper::getActions();

		JToolbarHelper::title(JText::_('COM_USERS_VIEW_GROUPS_TITLE'), 'groups');

		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew('group.add');
		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('group.edit');
			JToolbarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'groups.delete');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_users');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_USERS_GROUPS');
	}
}
