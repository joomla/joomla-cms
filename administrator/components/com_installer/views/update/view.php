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

include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');

class InstallerViewUpdate extends InstallerViewDefault
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom( 'update', 'config', 'config', 'Update', true, false);
		JToolBarHelper::custom( 'update_find', 'refresh', 'refresh','Find Updates',false,false);
		//JToolBarHelper::custom( 'update_purge', 'trash', 'trash', 'Purge Cache', false,false);
		JToolBarHelper::help( 'screen.installer' );

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		JHtml::_('behavior.tooltip');
		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;
/*		$item->img		= $item->enabled ? 'tick.png' : 'publish_x.png';
		$item->task 	= $item->enabled ? 'disable' : 'enable';
		$item->alt 		= $item->enabled ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
		$item->action	= $item->enabled ? JText::_( 'disable' ) : JText::_( 'enable' );

		if ($item->protected) {
			$item->cbd		= 'disabled';
			$item->style	= 'style="color:#999999;"';
		} else {
			$item->cbd		= null;
			$item->style	= null;
		}
*/
		$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;

		$this->assignRef('item', $item);
	}
}