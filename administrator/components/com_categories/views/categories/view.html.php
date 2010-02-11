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
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($items as $i => &$item)
		{
			// TODO: Complete the ordering stuff with nested sets
			$item->order_up = true;
			$item->order_dn = true;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		$state = $this->get('State');
		$component	= $state->get('filter.component');
		$section	= $state->get('filter.section');

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = &JFactory::getLanguage();
			$lang->load($component.'.menu', JPATH_BASE, null, false, false)
		||	$lang->load($component.'.menu', JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
		||	$lang->load($component.'.menu', JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($component.'.menu', JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);

		JToolBarHelper::title(
			JText::sprintf(
				'Categories_Categories_Title',
				$this->escape(JText::_($component.($section?"_$section":'')))
			),
			'categories.png'
		);
		JToolBarHelper::custom('category.edit', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
		JToolBarHelper::custom('category.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		if ($state->get('filter.published') != -1) {
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('categories.archive','JTOOLBAR_ARCHIVE');
		}
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else {
			JToolBarHelper::trash('categories.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();
		JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JToolbar_Rebuild', false);
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.categories','JTOOLBAR_HELP');

	}
}
