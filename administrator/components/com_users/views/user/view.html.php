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
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.5
 */
class UsersViewUser extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $grouplist;

	protected $groups;

	protected $state;

	/**
	 * Display the view
	 *
	 * @since  1.5
	 */
	public function display($tpl = null)
	{
		$this->form			= $this->get('Form');
		$this->item			= $this->get('Item');
		$this->grouplist	= $this->get('Groups');
		$this->groups		= $this->get('AssignedGroups');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->form->setValue('password', null);
		$this->form->setValue('password2',	null);

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$canDo = UsersHelper::getActions();

		$isNew	= ($this->item->id == 0);
		$isProfile = $this->item->id == $user->id;
		JToolbarHelper::title(JText::_($isNew ? 'COM_USERS_VIEW_NEW_USER_TITLE' : ($isProfile ? 'COM_USERS_VIEW_EDIT_PROFILE_TITLE' : 'COM_USERS_VIEW_EDIT_USER_TITLE')), $isNew ? 'user-add' : ($isProfile ? 'user-profile' : 'user-edit'));
		if ($canDo->get('core.edit')||$canDo->get('core.create')) {
			JToolbarHelper::apply('user.apply');
			JToolbarHelper::save('user.save');
		}
		if ($canDo->get('core.create')&&$canDo->get('core.manage')) {
			JToolbarHelper::save2new('user.save2new');
		}
		if (empty($this->item->id))  {
			JToolbarHelper::cancel('user.cancel');
		} else {
			JToolbarHelper::cancel('user.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_USERS_USER_MANAGER_EDIT');
	}
}
