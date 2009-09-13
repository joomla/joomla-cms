<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Users users view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewUsers extends JView
{
	/**
	 * Display the view
	 *
	 * @return	void
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

		// Build the active state filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', 'Any');
		$options[]	= JHtml::_('select.option', '0', 'Active');
		$options[]	= JHtml::_('select.option', '1', 'Blocked');

		$this->assignRef('state',			$state);
		$this->assignRef('items',			$items);
		$this->assignRef('pagination',		$pagination);
		$this->assignRef('filter_state',	$options);

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
		JToolBarHelper::title(JText::_('Users_View_Users_Title'), 'user');

		//JToolBarHelper::custom('user.activate', 'publish.png', 'publish_f2.png', 'Activate', true);
		//JToolBarHelper::custom('user.block', 'unpublish.png', 'unpublish_f2.png', 'Block', true);

		JToolBarHelper::custom('user.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('user.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::deleteList('', 'user.delete');

		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_users', '480', '570', 'JToolbar_Options');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.users');
	}
}