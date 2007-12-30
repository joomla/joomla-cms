<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
class MenusViewMenus extends JView
{
	function display($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'default';

		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Menu Manager' ), 'menumgr.png' );
		JToolBarHelper::customX( 'copyMenu', 'copy.png', 'copy_f2.png', 'Copy', true );
		JToolBarHelper::customX( 'deleteMenu', 'delete.png', 'delete_f2.png', 'Delete', true );
		JToolBarHelper::editListX('editMenu');
		JToolBarHelper::addNewX('addMenu');
		JToolBarHelper::help( 'screen.menumanager' );

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('View Menus'));

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$menus		= &$this->get('Menus');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('menus', $menus);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('limitstart', $limitstart);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}

	function copyForm($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'copy';

		// view data
		$table	= $this->get('Table');
		$items	= $this->get('MenuItems');

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(  JText::_( 'Copy Menu' ) );
		JToolBarHelper::custom( 'doCopyMenu', 'copy.png', 'copy_f2.png', 'Copy', false );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.menumanager.copy' );

		$document = & JFactory::getDocument();
		$document->setTitle('Copy Menu Items');

		$this->assignRef('items', $items);
		$this->assignRef('table', $table);

		parent::display($tpl);
	}

	function deleteForm($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'delete';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(  JText::_( 'Menu' ) . ': <small><small>[ '. JText::_( 'Delete' ) .' ]</small></small>' );
		JToolBarHelper::custom( 'doDeleteMenu', 'delete.png', 'delete_f2.png', 'Delete', false );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.menumanager.delete' );

		// view data
		$table		= $this->get('Table');
		$modules	= $this->get('Modules');
		$menuItems	= $this->get('MenuItems');

		$document = & JFactory::getDocument();
		$document->setTitle('Confirm Delete Menu Type: ' . $table->menutype );


		$this->assignRef('table', $table);
		$this->assignRef('modules', $modules);
		$this->assignRef('menuItems', $menuItems);

		parent::display($tpl);
	}

	function editForm($edit,$tpl=null)
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		global $mainframe;

		$this->_layout = 'edit';
		if($edit)
			$table = &$this->get('Table');
		else
			$table=& JTable::getInstance('menuTypes');
		/*
		 * Set toolbar items for the page
		 */
		$text = ( ($table->id != 0) ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		JToolBarHelper::title( JText::_( 'Menu Details' ).': <small><small>[ '. $text.' ]</small></small>', 'menumgr.png' );
		JToolBarHelper::custom( 'savemenu', 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.menumanager.new' );

		$this->assignRef('row', $table);
		$this->assign('isnew', ($table->id == 0));

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}
}