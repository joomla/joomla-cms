<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewTopic extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	public function display ($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_cjforum', 'topic', $this->item->id);
		
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
		JToolbarHelper::title(JText::_('COM_CJFORUM_PAGE_' . ($checkedOut ? 'VIEW_TOPIC' : ($isNew ? 'ADD_TOPIC' : 'EDIT_TOPIC'))), 
				'pencil-2 topic-add');
		
		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_cjforum', 'core.create')) > 0))
		{
			JToolbarHelper::apply('topic.apply');
			JToolbarHelper::save('topic.save');
			JToolbarHelper::save2new('topic.save2new');
			JToolbarHelper::cancel('topic.cancel');
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
					JToolbarHelper::apply('topic.apply');
					JToolbarHelper::save('topic.save');
					
					// We can save this record, but check the create permission
					// to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('topic.save2new');
					}
				}
			}
			
			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('topic.save2copy');
			}
			
			if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_cjforum.topic', $this->item->id);
			}
			
			JToolbarHelper::cancel('topic.cancel', 'JTOOLBAR_CLOSE');
		}
		
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_CJFORUM_TOPIC_MANAGER_EDIT');
	}
}
