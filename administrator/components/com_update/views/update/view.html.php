<?php
/**
 * @version		$Id:view.php 10586 2008-07-25 05:57:24Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Extension Manager Update View
 *
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.6
 */

//include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');
jimport('joomla.application.component.view');

class UpdateViewUpdate extends JView
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom( 'update', 'config', 'config', 'Update', true, false);
		JToolBarHelper::custom( 'update_find', 'refresh', 'refresh','Find Updates',false,false);
		JToolBarHelper::custom( 'update_purge', 'trash', 'trash', 'Purge Cache', false,false);
		JToolBarHelper::help( 'screen.installer' );

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;

		$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;

		$this->assignRef('item', $item);
	}
}