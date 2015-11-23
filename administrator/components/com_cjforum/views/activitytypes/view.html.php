<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewActivitytypes extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	public function display ($tpl = null)
	{
		CjForumHelper::addSubmenu('activitytypes');
		
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			
			return false;
		}
		
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjforum');
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_ACTIVITY_TYPES_TITLE'), 'stack activitytypes');
		
		JToolbarHelper::custom('activitytypes.scan', 'refresh.png', 'refresh.png', 'COM_CJFORUM_SCAN_RULES', false);
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('activity.edit');
		}
		
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('activitytypes.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('activitytypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin('activitytypes.checkin');
		}
		
		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'activitytypes.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('activitytypes.trash');
		}
		
		if ($user->authorise('core.admin', 'com_cjforum'))
		{
			JToolbarHelper::preferences('com_cjforum');
		}
	}

	protected function getSortFields ()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.published' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'a.activity_name' => JText::_('COM_CJFORUM_ACTIVITY_TYPE'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
}
