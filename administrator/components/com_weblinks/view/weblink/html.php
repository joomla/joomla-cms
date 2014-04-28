<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/helpers/weblinks.php';

/**
 * View to edit a weblink.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksViewWeblinkHtml extends JViewAdmin
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		parent::addToolbar();
		
		$config = $this->config;
		if ($config['layout'] != 'edit')
		{
			$this->addListToolbar();
		}
		else 
		{
			$this->addEditToolbar();
		}
	}
	
	private function addEditToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		
		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		// Since we don't track these assets at the item level, use the category id.
		$canDo		= WeblinksHelper::getActions($this->item->catid, 0);
		
		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||(count($user->getAuthorisedCategories('com_weblinks', 'core.create')))))
		{
			JToolbarHelper::apply('createEdit.weblink');
			JToolbarHelper::save('createClose.weblink');
		}
		if (!$checkedOut && (count($user->getAuthorisedCategories('com_weblinks', 'core.create')))){
			JToolbarHelper::save2new('createNew.weblink');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && (count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0))
		{
			JToolbarHelper::save2copy('updateCopy.weblink');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('cancel.weblink');
		}
		else
		{
			JToolbarHelper::cancel('cancel.weblink', 'JTOOLBAR_CLOSE');
		}
		
		if ($this->state->params->get('save_history') && $user->authorise('core.edit'))
		{
			$itemId = $this->item->id;
			$typeAlias = 'com_weblinks.weblink';
			JToolbarHelper::versions($typeAlias, $itemId);
		}
		
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_WEBLINKS_LINKS_EDIT');
	}
	
	private function addListToolbar()
	{
		$state	= $this->state;
		$canDo	= WeblinksHelper::getActions($state->get('filter.category_id'));
		$user	= JFactory::getUser();
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		if (count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('add.weblink');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('edit.weblink');
		}
		if ($canDo->get('core.edit.state')) {
		
			JToolbarHelper::publish('statePublish.weblink', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('stateUnpublish.weblink', 'JTOOLBAR_UNPUBLISH', true);
		
			JToolbarHelper::archiveList('stateArchive.weblink');
			JToolbarHelper::checkin('checkin.weblink');
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'delete.weblink', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('trash.weblinks');
		}
		// Add a batch button
		if ($user->authorise('core.create', 'com_weblinks') && $user->authorise('core.edit', 'com_weblinks') && $user->authorise('core.edit.state', 'com_weblinks'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
		
			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');
		
			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_weblinks');
		}
		
		JToolbarHelper::help('JHELP_COMPONENTS_WEBLINKS_LINKS');
	}
	
	protected function addFilters()
	{
		$state	= $this->state;
		
		JHtmlSidebar::setAction('index.php?option=com_weblinks&view=weblink');
		
		JHtmlSidebar::addFilter(
		JText::_('JOPTION_SELECT_PUBLISHED'),
		'filter_state',
		JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
		
		JHtmlSidebar::addFilter(
		JText::_('JOPTION_SELECT_CATEGORY'),
		'filter_category_id',
		JHtml::_('select.options', JHtml::_('category.options', 'com_weblinks'), 'value', 'text', $this->state->get('filter.category_id'))
		);
		
		JHtmlSidebar::addFilter(
		JText::_('JOPTION_SELECT_ACCESS'),
		'filter_access',
		JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);
		
		JHtmlSidebar::addFilter(
		JText::_('JOPTION_SELECT_LANGUAGE'),
		'filter_language',
		JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);
		
		JHtmlSidebar::addFilter(
		JText::_('JOPTION_SELECT_TAG'),
		'filter_tag',
		JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tag'))
		);
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'a.access' => JText::_('JGRID_HEADING_ACCESS'),
				'a.hits' => JText::_('JGLOBAL_HITS'),
				'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
