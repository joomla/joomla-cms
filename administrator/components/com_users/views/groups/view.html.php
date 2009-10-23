<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Users groups view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewGroups extends JView
{
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

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Users_View_Groups_Title'), 'groups');

		JToolBarHelper::custom('group.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('group.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::deleteList('', 'group.delete');

		JToolBarHelper::divider();

		JToolBarHelper::preferences('com_users');
		JToolBarHelper::help('screen.users.groups');
	}
}
