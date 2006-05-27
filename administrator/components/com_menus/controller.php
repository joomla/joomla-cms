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
	function newwiz()
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
	function edit2()
	{
		$id	= (int) JRequest::getVar( 'id', 0 );

		$model = &$this->getModel( 'JModelMenu' );
		$table = &$model->getTable();

		$isNew = ($this->getTask() == 'new' || $id == 0);

		if ($isNew)
		{
			$id = 0;
			$table->type		= JRequest::getVar( 'type' );
			$table->menutype	= JRequest::getVar( 'menutype' );
			$table->published	= 1;
			$table->access		= 0;
			switch ($table->type)
			{
				case 'component':
					$table->componentid	= (int) JRequest::getVar( 'componentid', 0 );
					break;
				case 'url':
					$table->link = JRequest::getVar( 'link' );
					break;
				case 'separator':
					$table->name = JRequest::getVar( 'name' );
					break;
				case 'component_item_link':
					break;
			}
		}
		else
		{
			$table->load( $id );
		}

		$view = new JMenuEditView( $this );
		$view->setModel( $model, true );
		$view->display();
	}
}
?>