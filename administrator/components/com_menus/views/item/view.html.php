<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Menus Menu Item View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusViewItem extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$itemForm	= $this->get('Form');
		$paramsForm	= $this->get('ParamsForm');
		$modules	= $this->get('Modules');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$itemForm->bind($item);
		$paramsForm->bind($item->params);

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('form',		$itemForm);
		$this->assignRef('paramsform',	$paramsForm);
		$this->assignRef('modules',		$modules);

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

		JToolBarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_ITEM_TITLE' : 'COM_MENUS_VIEW_EDIT_ITEM_TITLE'), 'menu-add');


		// If not checked out, can save the item.
		if ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'))
		{

			JToolBarHelper::apply('item.apply','JTOOLBAR_APPLY');
			JToolBarHelper::save('item.save','JTOOLBAR_SAVE');
			JToolBarHelper::addNew('item.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}
		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('item.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false)
			;}
		if ($isNew) {
			JToolBarHelper::cancel('item.cancel','JTOOLBAR_CANCEL');
			}
		else {
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.menus.item','JTOOLBAR_HELP');
	}
}
