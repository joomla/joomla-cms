<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Menus View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusViewMenus extends JViewLegacy
{
	protected $items;
	protected $modules;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->modules		= $this->get('Modules');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/menus.php';

		$canDo	= MenusHelper::getActions($this->state->get('filter.parent_id'));

		JToolBarHelper::title(JText::_('COM_MENUS_VIEW_MENUS_TITLE'), 'menumgr.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('menu.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('menu.edit');
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'menus.delete');
		}

		JToolBarHelper::custom('menus.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_menus');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_MENUS_MENU_MANAGER');
	}
}
