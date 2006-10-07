<?php
/**
* @version $Id: view.php 5103 2006-09-20 18:23:25Z louis $
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
 * @package Joomla
 * @subpackage Menus
 * @since 1.5
 */
class JMenuViewMenus extends JView
{
	function display($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'default';

		/*
		 * Set toolbar items for the page
		 */
		JMenuBar::title( JText::_( 'Menu Manager' ), 'menumgr.png' );
		JMenuBar::customX( 'copyMenu', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::customX( 'deleteMenu', 'delete.png', 'delete_f2.png', 'Delete', true );
		JMenuBar::editListX('editMenu');
		JMenuBar::addNewX('editMenu');
		JMenuBar::help( 'screen.menumanager' );

		$document = & JFactory::getDocument();
		$document->setTitle('View Menu Items');

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$menus		= &$this->get('Menus');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('menus', $menus);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('limitstart', $limitstart);

		JCommonHTML::loadOverlib();

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
		JMenuBar::title(  JText::_( 'Copy Menu' ) );
		JMenuBar::custom( 'doCopyMenu', 'copy.png', 'copy_f2.png', 'Copy', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menumanager.copy' );

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
		JMenuBar::title(  JText::_( 'Delete Menu' ) );
		JMenuBar::custom( 'doDeleteMenu', 'delete.png', 'delete_f2.png', 'Delete', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menumanager.delete' );

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

	function editForm($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'edit';

		$table = &$this->get('Table');

		/*
		 * Set toolbar items for the page
		 */
		$text = ( ($table->id != 0) ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		JMenuBar::title( JText::_( 'Menu Details' ).': <small><small>[ '. $text.' ]</small></small>', 'menumgr.png' );
		JMenuBar::custom( 'savemenu', 'save.png', 'save_f2.png', 'Save', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menumanager.new' );

		$this->assignRef('row', $table);
		$this->assign('isnew', ($table->id == 0));

		JCommonHTML::loadOverlib();

		parent::display($tpl);
	}
}
?>