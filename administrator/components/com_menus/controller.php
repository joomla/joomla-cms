<?php
/**
 * @version $Id: admin.menus.php 3607 2006-05-24 01:09:39Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.controller');

/**
 * @package Joomla
 * @subpackage Menus
 */
class JMenuController extends JController
{
	/**
	 * New menu item wizard
	 */
	function wizard()
	{
		$model	= &$this->getModel( 'Wizard', 'JMenuModel' );
		$model->init();
		$view = &$this->getView( 'Wizard', 'com_menus', 'JMenuView' );
		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Edits a menu item
	 */
	function edit()
	{
		$model	=& $this->getModel( 'Item', 'JMenuModel' );
		$view =& $this->getView( 'Item', 'com_menus', 'JMenuView' );
		$view->setModel( $model, true );
		$view->edit();
	}

	/**
	 * Saves a menu item
	 */
	function save()
	{
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();

		$model =& $this->getModel( 'Item', 'JMenuModel' );
		if ($model->store()) {
			$msg = JText::_( 'Menu item Saved' );
		} else {
			$msg = JText::_( 'Error Saving Menu item' );
		}
		
		$item =& $model->getItem();
		switch ( $this->_task ) {
			case 'apply':
				$this->setRedirect( 'index.php?option=com_menus&menutype='. $item->menutype .'&task=edit&id='. $item->id . '&hidemainmenu=1' , $msg );
				break;
	
			case 'save':
			default:
				$this->setRedirect( 'index.php?option=com_menus&menutype='. $item->menutype, $msg );
				break;
		}
	}
}
?>