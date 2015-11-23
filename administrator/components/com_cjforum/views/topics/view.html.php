<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewTopics extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	public function display ($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			CjForumHelper::addSubmenu('topics');
		}
		
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->authors = $this->get('Authors');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			
			return false;
		}
		
		// Levels filter.
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));
		
		$this->f_levels = $options;
		
		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjforum', 'category', $this->state->get('filter.category_id'));
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_TOPICS_TITLE'), 'stack topic');
		
		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_cjforum', 'core.create'))) > 0)
		{
			JToolbarHelper::addNew('topic.add');
		}
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('topic.edit');
		}
		
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('topics.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('topics.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom('topics.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
			JToolbarHelper::archiveList('topics.archive');
			JToolbarHelper::checkin('topics.checkin');
		}
		
		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'topics.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('topics.trash');
		}
		
		// Add a batch button
		if ($user->authorise('core.create', 'com_cjforum') && $user->authorise('core.edit', 'com_cjforum') &&
				 $user->authorise('core.edit.state', 'com_cjforum'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			
			// Instantiate a new JLayoutFile instance and render the batch
			// button
			$layout = new JLayoutFile('joomla.toolbar.batch');
			
			$dhtml = $layout->render(array(
					'title' => $title
			));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		
		if ($user->authorise('core.admin', 'com_cjforum'))
		{
			JToolbarHelper::preferences('com_cjforum');
		}
		
		JToolbarHelper::help('JHELP_CJFORUM_TOPIC_MANAGER');
	}

	protected function getSortFields ()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'category_title' => JText::_('JCATEGORY'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
}
