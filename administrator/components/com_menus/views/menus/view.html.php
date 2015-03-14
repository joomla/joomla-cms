<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Menus View.
 *
 * @since  1.6
 */
class MenusViewMenus extends JViewLegacy
{
	/**
	 * @var  mixed
	 */
	protected $items;

	/**
	 * @var  array
	 */
	protected $modules;

	/**
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->modules		= $this->get('Modules');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		MenusHelper::addSubmenu('menus');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= JHelperContent::getActions('com_menus');

		JToolbarHelper::title(JText::_('COM_MENUS_VIEW_MENUS_TITLE'), 'list menumgr');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('menu.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('menu.edit');
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('', 'menus.delete');
		}

		JToolbarHelper::custom('menus.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_menus');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_MENUS_MENU_MANAGER');
	}
}
