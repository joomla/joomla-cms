<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewProfile extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	public function display ($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_cjforum', 'profile', $this->item->id);
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = ! ($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		
		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		JToolbarHelper::title(JText::_('COM_CJFORUM_PAGE_' . ($checkedOut ? 'VIEW_PROFILE' : 'EDIT_PROFILE')), 'pencil-2 profile-add');
		
		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_cjforum', 'core.create')) > 0))
		{
			JToolbarHelper::apply('profile.apply');
			JToolbarHelper::save('profile.save');
			JToolbarHelper::cancel('profile.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (! $checkedOut)
			{
				// Since it's an existing record, check the edit permission, or
				// fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolbarHelper::apply('profile.apply');
					JToolbarHelper::save('profile.save');
				}
			}
			
			if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_cjforum.profile', $this->item->id);
			}
			
			JToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
		}
		
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_CJFORUM_PROFILE_MANAGER_EDIT');
	}
}