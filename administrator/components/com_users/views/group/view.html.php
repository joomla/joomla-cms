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
 * View to edit a user group.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewGroup extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$canDo		= UsersHelper::getActions();

		JToolBarHelper::title(JText::_($isNew ? 'Users_View_New_Group_Title' : 'Users_View_Edit_Group_Title'), 'groups-add');

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('group.apply');
			JToolBarHelper::save('group.save');
			JToolBarHelper::addNew('group.save2new', 'JToolbar_Save_and_new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('group.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('group.cancel');
		}
		else {
			JToolBarHelper::cancel('group.cancel', 'JToolbar_Close');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.group');
	}
}