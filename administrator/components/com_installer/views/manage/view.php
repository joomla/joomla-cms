<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

include_once dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php';

/**
 * Extension Manager Manage View
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerViewManage extends InstallerViewDefault
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom('refresh', 'refresh', 'refresh','Refresh Cache',false,false);
		JToolBarHelper::deleteList('', 'remove', 'Uninstall');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.installer2');

		$dbo =& JFactory::getDBO();

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$item = new stdClass();
		$lists = Array(); //$this->lists;
		$lists['filter'] = JRequest::getVar('filter');

		$dbo->setQuery('SELECT DISTINCT type FROM #__extensions');
		$type_list = $dbo->loadObjectList();
		$item->type = 'All'; // will get translated below
		array_unshift($type_list, $item);
		$lists['type'] = JHTML::_('select.genericlist', $type_list, 'extensiontype', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'type', 'type', JRequest::getVar('extensiontype'), false, true);

		$select[] = JHTML::_('select.option', '-1', JText::_('All'));
		$select[] = JHTML::_('select.option', '0', JText::_('Site'));
		$select[] = JHTML::_('select.option', '1', JText::_('Admininistrator'));
		$lists['clientid'] = JHTML::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $state->get('filter.client'));
		$dbo->setQuery('SELECT DISTINCT CASE `folder` WHEN "" THEN "N/A" ELSE `folder` END AS folder from #__extensions');
		$folder_list = $dbo->loadObjectList();
		$item->folder = 'All'; // will get translated below
		array_unshift($folder_list, $item);
		$lists['folder'] = JHTML::_('select.genericlist', $folder_list, 'folder', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'folder','folder', JRequest::getVar('folder','All'), false, true);
		//$lists['state'] = ''; // published or otherwise?
		$lists['hideprotected'] = JRequest::getBool('hideprotected', 1);
		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;
		$item->img		= $item->enabled ? 'tick.png' : 'publish_x.png';
		$item->task 	= $item->enabled ? 'disable' : 'enable';
		$item->alt 		= $item->enabled ? JText::_('Enabled') : JText::_('Disabled');
		$item->action	= $item->enabled ? JText::_('disable') : JText::_('enable');

		if ($item->protected) {
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