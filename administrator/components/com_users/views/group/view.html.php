<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Users group view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewGroup extends JView
{
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

		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		$this->_setToolbar();
		JRequest::setVar('hidemainmenu', 1);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolbar()
	{
		$isNew	= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'Users_View_New_Group_Title' : 'Users_View_Edit_Group_Title'), 'groups-add');

		JToolBarHelper::addNew('group.save2new', 'JToolbar_Save_and_new');
		JToolBarHelper::save('group.save');
		JToolBarHelper::apply('group.apply');
		JToolBarHelper::cancel('group.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.group');
	}
}