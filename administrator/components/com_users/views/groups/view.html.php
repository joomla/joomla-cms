<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of user groups.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewGroups extends JView
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

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$canDo	= UsersHelper::getActions();

		JToolBarHelper::title(JText::_('Users_View_Groups_Title'), 'groups');

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::custom('group.add', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::custom('group.edit', 'edit.png', 'edit_f2.png','JTOOLBAR_EDIT', true);
		}
		if ($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'group.delete','JTOOLBAR_TRASH');
		}

		JToolBarHelper::divider();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_users');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.groups','JTOOLBAR_HELP');
	}
}
