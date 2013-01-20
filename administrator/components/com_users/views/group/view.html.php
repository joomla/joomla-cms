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
 * View to edit a user group.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       1.6
 */
class UsersViewGroup extends JViewLegacy
{
	/*
	 * @var    JForm  The JForm for this view
	 * @since  1.6
	 */
	protected $form;

	/*
	 * @var    JObject  The JObject holding data for this view
	 * @since  1.6
	 */
	protected $item;

	/*
	 * @var   JObject  The JObject holding state data for this view such as parameters, paths and filters.
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Method to display the view
	 *
	 * @param  string  $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

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
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$canDo = UsersHelper::getActions();

		JToolbarHelper::title(JText::_($isNew ? 'COM_USERS_VIEW_NEW_GROUP_TITLE' : 'COM_USERS_VIEW_EDIT_GROUP_TITLE'), 'groups-add');

		if ($canDo->get('core.edit')||$canDo->get('core.create'))
		{
			JToolbarHelper::apply('group.apply');
			JToolbarHelper::save('group.save');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('group.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('group.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('group.cancel');
		}
		else
		{
			JToolbarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_USERS_GROUPS_EDIT');
	}
}
