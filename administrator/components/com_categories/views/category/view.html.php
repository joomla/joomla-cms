<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Categories component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 */
class CategoriesViewCategory extends JView
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
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$user		= &JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		JToolBarHelper::title(JText::_($isNew ? 'Categories_Category_Add_Title' : 'Categories_Category_Edit_Title'), 'category-add');

		// If not checked out, can save the item.
		if ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id')) {
			JToolBarHelper::apply('category.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('category.save', 'JToolbar_Save');
			JToolBarHelper::addNew('category.save2new', 'JToolbar_Save_and_new');
		}

		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('category.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_copy', false);
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('category.cancel','JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('category.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.categories.edit','JTOOLBAR_HELP');
	}
}
