<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UsersViewUser extends JView
{
	public $state;
	public $item;
	public $form;
	public $groups;
	public $grouplist;
	public $group_id;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$form		= $this->get('Form');
		$groupList	= $this->get('Groups');
		$groups		= $this->get('AssignedGroups');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$form->bind($item);
		$form->setValue('password', null);
		$form->setValue('password2', null);

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('form',		$form);
		$this->assignRef('grouplist',	$groupList);
		$this->assignRef('groups',		$groups);

		parent::display($tpl);
		$this->_setToolbar();

	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);

		$isNew	= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'Users_View_New_User_Title' : 'Users_View_Edit_User_Title'), 'user-add');
		JToolBarHelper::save('user.save');
		JToolBarHelper::apply('user.apply');
		JToolBarHelper::addNew('user.save2new', 'JToolbar_Save_and_new');
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('user.cancel');
		}
		else {
			JToolBarHelper::cancel('user.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.user');
	}
}
