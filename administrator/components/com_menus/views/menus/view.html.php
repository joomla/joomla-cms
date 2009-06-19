<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Menus Menu Menus View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusViewMenus extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$modules	= $this->get('Modules');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('modules',		$modules);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolBar()
	{
		JToolBarHelper::title(JText::_('Menus_Title_Menus'));

		JToolBarHelper::custom('menu.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('menu.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::deleteList('', 'menus.delete');
	}
}
