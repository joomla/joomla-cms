<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a user view level.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.6
 */
class UsersViewLevel extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew	= ($this->item->id == 0);
		$canDo  = UsersHelper::getActions();

		JToolbarHelper::title(JText::_($isNew ? 'COM_USERS_VIEW_NEW_LEVEL_TITLE' : 'COM_USERS_VIEW_EDIT_LEVEL_TITLE'), 'levels-add');

		if ($canDo->get('core.edit')||$canDo->get('core.create'))
		{
			JToolbarHelper::apply('level.apply');
			JToolbarHelper::save('level.save');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('level.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')){
				JToolbarHelper::save2copy('level.save2copy');
			}
		if (empty($this->item->id)){
				JToolbarHelper::cancel('level.cancel');
		}
		else
		{
				JToolbarHelper::cancel('level.cancel', 'JTOOLBAR_CLOSE');
		}

			JToolbarHelper::divider();
			JToolbarHelper::help('JHELP_USERS_ACCESS_LEVELS_EDIT');
	}
}
