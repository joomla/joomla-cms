<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/** ensure the installerviewdefault is available */
include_once dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php';

/**
 * Libraries view
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 */
class InstallerViewLibraries extends InstallerViewDefault
{
	/**
	 * Display the view
	 * @access public
	 */
    function display($tpl = null)
    {
    	/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::deleteList('', 'remove', 'Uninstall');
		JToolBarHelper::help('screen.installer2');

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',		$lists);

        parent::display($tpl);
    }

    /**
     * Loads data for an individual item and sets some variables
     * @access public
     */
	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;
/*		$item->img		= $item->enabled ? 'tick.png' : 'publish_x.png';
		$item->task 	= $item->enabled ? 'disable' : 'enable';
		$item->alt 		= $item->enabled ? JText::_('Enabled') : JText::_('Disabled');
		$item->action	= $item->enabled ? JText::_('disable') : JText::_('enable');
*/
		if ($item->libraryname == 'joomla') {
			$item->cbd		= 'disabled';
			$item->style	= 'style="color:#999999;"';
		} else {
			$item->cbd		= null;
			$item->style	= null;
		}
		$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;

		$this->assignRef('item', $item);
	}
}
?>
