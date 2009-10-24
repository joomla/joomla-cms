<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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

		JToolBarHelper::title(JText::_($isNew ? 'Menus_View_New_Item_Title' : 'Menus_View_Edit_Item_Title'), 'menu-add');


		// If not checked out, can save the item.
		if ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'))
		{

			JToolBarHelper::save('item.save');
			JToolBarHelper::apply('item.apply');
			JToolBarHelper::addNew('item.save2new', 'JToolbar_Save_and_new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew) {
			JToolBarHelper::custom('item.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_copy', false)
			;}
		if ($isNew) {
			JToolBarHelper::cancel('item.cancel','JToolbar_Cancel');
			}
		else {
			JToolBarHelper::cancel('item.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.menus.item');
	}
}
