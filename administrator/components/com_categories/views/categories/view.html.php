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
		// Initialise variables.
		$extension	= JRequest::getCmd('extension');
		$categoryId	= $this->state->get('filter.category_id');
		$section	= $this->state->get('filter.section');
		$canDo		= null;

		// Avoid nonsense situation.
		if ($extension == 'com_categories') {
			return;
		}

 		// The extension can be in the form com_foo.section
		$parts		= explode('.',$extension);
		$component	= $parts[0];

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
			$lang->load($component.'.sys', JPATH_BASE, null, false, false)
		||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
		||	$lang->load($component.'.sys', JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);

 		// The extension can be in the form com_foo.section
		$parts = explode('.',$extension);
		$component = $parts[0];

		// Load the category helper.
		require_once JPATH_COMPONENT.'/helpers/categories.php';

		// Get the results for each action.
		$canDo = CategoriesHelper::getActions($component, $categoryId);

		// If the section is defined, component supports multiple category groups.
		if ($section) {
			$title = JText::sprintf(
				'COM_CATEGORIES_CATEGORIES_TITLE',
				$this->escape(JText::_($component.($section?"_$section":'')))
			);
		}
		else {
			$title = JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Prepare the toolbar.
		JToolBarHelper::title($title, 'categories.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('category.edit', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
		}

		if ($canDo->get('core.edit' )) {
			JToolBarHelper::custom('category.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('categories.archive','JTOOLBAR_ARCHIVE');
		}

		if (JFactory::getUser()->authorise('core.admin')) {
			JToolBarHelper::custom('categories.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $extension)) {
			JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('categories.trash','JTOOLBAR_TRASH');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolBarHelper::preferences($extension);
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_COMPONENTS_'.strtoupper(substr($component,4)).'_CATEGORIES');
	}
}