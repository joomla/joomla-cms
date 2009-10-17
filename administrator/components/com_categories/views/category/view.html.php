<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$form		= $this->get('Form');
		$modules	= $this->get('Modules');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
		$this->_setToolBar();
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolBar()
	{
		$user		= &JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		JToolBarHelper::title(JText::_($isNew ? 'Categories_Category_Add_Title' : 'Categories_Category_Edit_Title'), 'category-add');

		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('category.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_copy', false);
		}

		// If not checked out, can save the item.
		if ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'))
		{
			JToolBarHelper::save('category.save');
			JToolBarHelper::apply('category.apply');
			JToolBarHelper::addNew('category.save2new', 'JToolbar_Save_and_new');
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('category.cancel');
		}
		else {
			JToolBarHelper::cancel('category.cancel', 'JToolbar_Close');
		}
			JToolBarHelper::divider();
			JToolBarHelper::help('screen.categories.edit');
	}
}
