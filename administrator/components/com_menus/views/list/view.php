<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @since 1.5
 */
class MenusViewList extends JView
{
	protected $_name = 'list';
	protected $items;
	protected $pagination;
	protected $lists;
	protected $user;
	protected $menutype;
	protected $ordering;
	protected $limitstart = 0;
	protected $menutypes;
	protected $MenuList;

	function display($tpl=null)
	{
		$mainframe = JFactory::getApplication();

		$this->_layout = 'default';

		/*
		 * Set toolbar items for the page
		 */
		$menutype 	= $mainframe->getUserStateFromRequest('com_menus.menutype', 'menutype', 'mainmenu', 'string');

		JToolBarHelper::title(JText::_('MENU ITEM MANAGER') .': <small><small>['.$menutype.']</small></small>', 'menu.png');

		$bar = &JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'menus', 'Menus', "index.php?option=com_menus");

		JToolBarHelper::makeDefault('setdefault');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX('move', 'move.png', 'move_f2.png', 'Move', true);
		JToolBarHelper::customX('copy', 'copy.png', 'copy_f2.png', 'Copy', true);
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX('newItem');
		JToolBarHelper::help('screen.menus');

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('View Menu Items'));

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');
		$lists		= &$this->_getViewLists();
		$user		= &JFactory::getUser();

		// Ensure ampersands and double quotes are encoded in item titles
		foreach ($items as $i => $item) {
			$treename = $item->treename;
			$treename = JFilterOutput::ampReplace($treename);
			$treename = str_replace('"', '&quot;', $treename);
			$items[$i]->treename = $treename;
		}

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering');

		JHtml::_('behavior.tooltip');

		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);
		$this->assignRef('user', $user);
		$this->assignRef('menutype', $menutype);
		$this->assignRef('ordering', $ordering);
		$this->assignRef('limitstart', $limitstart);

		parent::display($tpl);
	}

	function copyForm($tpl=null)
	{
		$mainframe = JFactory::getApplication();

		$this->_layout = 'copy';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(JText::_('Copy Menu Items'));
		JToolBarHelper::custom('doCopy', 'copy.png', 'copy_f2.png', 'Copy', false);
		JToolBarHelper::cancel('cancelItem');
		JToolBarHelper::help('screen.menus');

		$document = & JFactory::getDocument();
		$document->setTitle('Copy Menu Items');

		$menutype 	= $mainframe->getUserStateFromRequest('com_menus.menutype', 'menutype', 'mainmenu', 'string');

		// Build the menutypes select list
		$menuTypes 	= MenusHelper::getMenuTypes();
		foreach ($menuTypes as $menuType) {
			$menu[] = JHtml::_('select.option',  $menuType, $menuType);
		}
		$MenuList = JHtml::_(
			'select.genericlist',
			$menu,
			'menu',
			array('list.attr' => 'class="inputbox" size="10"')
		);

		$items = &$this->get('ItemsFromRequest');

		$this->assignRef('menutype', $menutype);
		$this->assignRef('items', $items);
		$this->assignRef('menutypes', $menuTypes);
		$this->assignRef('MenuList', $MenuList);

		parent::display($tpl);
	}

	function moveForm($tpl=null)
	{
		$mainframe = JFactory::getApplication();

		$this->_layout = 'move';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(JText::_('Menu Items') . ': <small><small>[ '. JText::_('Move') .' ]</small></small>');
		JToolBarHelper::custom('doMove', 'move.png', 'move_f2.png', 'Move', false);
		JToolBarHelper::cancel('cancelItem');
		JToolBarHelper::help('screen.menus');

		$document = & JFactory::getDocument();
		$document->setTitle('Copy Menu Items');

		$menutype = $mainframe->getUserStateFromRequest('com_menus.menutype', 'menutype', 'mainmenu', 'string');

		// Build the menutypes select list
		$menuTypes 	= MenusHelper::getMenuTypes();
		foreach ($menuTypes as $menuType) {
			$menu[$menuType] = $menuType;
		}
		$MenuList = JHtml::_(
			'select.genericlist',
			$menu,
			'menu',
			array('list.attr' => 'class="inputbox" size="10"', 'option.key' => null)
		);

		$items = &$this->get('ItemsFromRequest');

		$this->assignRef('menutype', $menutype);
		$this->assignRef('items', $items);
		$this->assignRef('menutypes', $menuTypes);
		$this->assignRef('MenuList', $MenuList);

		parent::display($tpl);
	}

	function &_getViewLists()
	{
		$mainframe = JFactory::getApplication();
		$db		= &JFactory::getDBO();

		$menutype			= $mainframe->getUserStateFromRequest("com_menus.menutype",					'menutype',			'mainmenu',		'string');
		$filter_order		= $mainframe->getUserStateFromRequest("com_menus.$menutype.filter_order",		'filter_order',		'm.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest("com_menus.$menutype.filter_order_Dir",	'filter_order_Dir',	'ASC',			'word');
		$filter_state		= $mainframe->getUserStateFromRequest("com_menus.$menutype.filter_state",		'filter_state',		'',				'word');
		$levellimit			= $mainframe->getUserStateFromRequest("com_menus.$menutype.levellimit",		'levellimit',		10,				'int');
		$search				= $mainframe->getUserStateFromRequest("com_menus.$menutype.search",			'search',			'',				'string');
		$search				= JString::strtolower($search);

		// level limit filter
		$lists['levellist'] = JHtml::_('select.integerlist', 1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit);

		// state filter
		$lists['state']	= JHtml::_('grid.state',  $filter_state);

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		return $lists;
	}
}
