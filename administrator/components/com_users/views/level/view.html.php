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
 * The HTML Users access level view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewLevel extends JView
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

		$item->title	= $item->getAssetGroupName();
		$form->bind($item);

		$this->assignRef('form',	$form);
		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);

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
		$isNew	= ($this->item->getAssetGroupId() == 0);
		JToolBarHelper::title(JText::_($isNew ? 'Users_View_New_Level_Title' : 'Users_View_Edit_Level_Title'), 'levels');

		JToolBarHelper::addNew('level.save2new', 'JToolbar_Save_and_new');
		JToolBarHelper::save('level.save');
		JToolBarHelper::apply('level.apply');
		JToolBarHelper::cancel('level.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.users.level');
	}
}