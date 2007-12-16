<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Menus
 * @since 1.5
 */
class MenusViewList extends JView
{
	var $_name = 'list';

	function display($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'default';

		/*
		 * Set toolbar items for the page
		 */
		$menutype 	= $mainframe->getUserStateFromRequest( 'com_menus.menutype', 'menutype', 'mainmenu', 'string' );

		JToolBarHelper::title( JText::_( 'MENU ITEM MANAGER' ) .': <small><small>['.$menutype.']</small></small>', 'menu.png' );

		$bar =& JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'menus', 'Menus', "index.php?option=com_menus" );

		JToolBarHelper::makeDefault( 'setdefault' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX( 'move', 'move.png', 'move_f2.png', 'Move', true );
		JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX('type');
		JToolBarHelper::help( 'screen.menus' );

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('View Menu Items'));

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');
		$lists		= &$this->_getViewLists();
		$user		= &JFactory::getUser();

		// ensure ampersands are encoded
		JFilterOutput::ampReplaceRecursive($items);

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'm.ordering');

		JHTML::_('behavior.tooltip');

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
		global $mainframe;

		$this->_layout = 'copy';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Copy Menu Items' ) );
		JToolBarHelper::custom( 'doCopy', 'copy.png', 'copy_f2.png', 'Copy', false );
		JToolBarHelper::cancel('cancelItem');
		JToolBarHelper::help( 'screen.menus.copy' );

		$document = & JFactory::getDocument();
		$document->setTitle('Copy Menu Items');

		$menutype 	= $mainframe->getUserStateFromRequest( 'com_menus.menutype', 'menutype', 'mainmenu', 'string' );

		// Build the menutypes select list
		$menuTypes 	= MenusHelper::getMenuTypes();
		foreach ( $menuTypes as $menuType ) {
			$menu[] = JHTML::_('select.option',  $menuType, $menuType );
		}
		$MenuList = JHTML::_('select.genericlist',   $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

		$items = &$this->get('ItemsFromRequest');

		$this->assignRef('menutype', $menutype);
		$this->assignRef('items', $items);
		$this->assignRef('menutypes', $menuTypes);
		$this->assignRef('MenuList', $MenuList);

		parent::display($tpl);
	}

	function moveForm($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'move';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Menu Items' ) . ': <small><small>[ '. JText::_( 'Move' ) .' ]</small></small>' );
		JToolBarHelper::custom( 'doMove', 'move.png', 'move_f2.png', 'Move', false );
		JToolBarHelper::cancel('cancelItem');
		JToolBarHelper::help( 'screen.menus.move' );

		$document = & JFactory::getDocument();
		$document->setTitle('Copy Menu Items');

		$menutype 	= $mainframe->getUserStateFromRequest( 'com_menus.menutype', 'menutype', 'mainmenu', 'string' );

		// Build the menutypes select list
		$menuTypes 	= MenusHelper::getMenuTypes();
		foreach ( $menuTypes as $menuType ) {
			$menu[] = JHTML::_('select.option',  $menuType, $menuType );
		}
		$MenuList = JHTML::_('select.genericlist',   $menu, 'menu', 'class="inputbox" size="10"', 'value', 'text', null );

		$items = &$this->get('ItemsFromRequest');

		$this->assignRef('menutype', $menutype);
		$this->assignRef('items', $items);
		$this->assignRef('menutypes', $menuTypes);
		$this->assignRef('MenuList', $MenuList);

		parent::display($tpl);
	}

	function &_getViewLists()
	{
		global $mainframe;
		$db		=& JFactory::getDBO();

		$menutype			= $mainframe->getUserStateFromRequest( "com_menus.menutype",					'menutype',			'mainmenu',		'string' );
		$filter_order		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_order",		'filter_order',		'm.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_order_Dir",	'filter_order_Dir',	'ASC',			'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_state",		'filter_state',		'',				'word' );
		$levellimit			= $mainframe->getUserStateFromRequest( "com_menus.$menutype.levellimit",		'levellimit',		10,				'int' );
		$search				= $mainframe->getUserStateFromRequest( "com_menus.$menutype.search",			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		// level limit filter
		$lists['levellist'] = JHTML::_('select.integerlist',    1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit );

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		return $lists;
	}
}
