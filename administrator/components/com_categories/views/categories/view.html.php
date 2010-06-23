<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Categories view class for the Category package.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesViewCategories extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$component	= $this->state->get('filter.component');
		$section	= $this->state->get('filter.section');

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
			$lang->load($component.'.sys', JPATH_BASE, null, false, false)
		||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
		||	$lang->load($component.'.sys', JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);


		if ($component != 'com_content') {
			$title = JText::sprintf(
				'COM_CATEGORIES_CATEGORIES_TITLE',
				$this->escape(JText::_($component.($section?"_$section":'')))
		);
		} else {
			$title = JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}
		JToolBarHelper::title($title, 'categories.png');
		JToolBarHelper::custom('category.edit', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
		JToolBarHelper::custom('category.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
		if ($this->state->get('filter.published') != 2){
			JToolBarHelper::divider();
			JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}
		if ($this->state->get('filter.published') != -1 ) {
				JToolBarHelper::divider();
				if ($this->state->get('filter.published') != 2) {
					JToolBarHelper::archiveList('categories.archive','JTOOLBAR_ARCHIVE');
				}
				else if ($this->state->get('filter.published') == 2) {
					JToolBarHelper::unarchiveList('categories.publish', 'JTOOLBAR_UNARCHIVE');
				}
		}
		if(JFactory::getUser()->authorise('core.manage','com_checkin')) {
			JToolBarHelper::custom('categories.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}
		if ($this->state->get('filter.published') == -2 && JFactory::getUser()->authorise('core.delete', 'com_content')) {
			JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_EMPTY_TRASH');
		} else {
			JToolBarHelper::trash('categories.trash','JTOOLBAR_TRASH');
		}

		JToolBarHelper::divider();
		JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_'.strtoupper(substr($component,4)).'_CATEGORIES');
	}
}
