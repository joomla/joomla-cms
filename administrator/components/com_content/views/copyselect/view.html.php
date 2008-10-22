<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.0
 */
class ContentViewCopyselect extends JView
{
	function display($tpl = null)
	{
		// Initialize variables
		$db			= & JFactory::getDBO();

		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$sectionid	= JRequest::getVar( 'sectionid', 0, '', 'int' );
		$option		= JRequest::getCmd( 'option' );
		$task		= JRequest::getCmd( 'task' );

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$msg = JText::_('Select an item to '.($task=='copy' ? 'copy' : 'move'));
			$mainframe->redirect('index.php?option='.$option, $msg, 'error');
		}

		//seperate contentids
		$cids = implode(',', $cid);
		## Articles query
		$query = 'SELECT a.title' .
				' FROM #__content AS a' .
				' WHERE ( a.id IN ( '. $cids .' ) )' .
				' ORDER BY a.title';
		$db->setQuery($query);
		$items = $db->loadObjectList();

		## Section & Category query
		$query = 'SELECT CONCAT_WS(",",s.id,c.id) AS `value`, CONCAT_WS(" / ", s.title, c.title) AS `text`' .
				' FROM #__sections AS s' .
				' INNER JOIN #__categories AS c ON c.section = s.id' .
				' WHERE s.scope = "content"' .
				' ORDER BY s.title, c.title';
		$db->setQuery($query);

		// Add a row for uncategorized content
		$uncat	= JHTML::_('select.option', '0,0', JText::_('UNCATEGORIZED'));
		$rows	= $db->loadObjectList();
		array_unshift($rows, $uncat);
		// build the html select list
		$sectCatList = JHTML::_('select.genericlist', $rows, 'sectcat', 'class="inputbox" size="10"', 'value', 'text', NULL);

		$this->assignRef('option',		$option);
		$this->assignRef('cid',			$cid);
		$this->assignRef('sectCatList',	$sectCatList);
		$this->assignRef('sectionid',	$sectionid);
		$this->assignRef('items',		$items);

		// Render article preview
		parent::display($tpl);
	}
}