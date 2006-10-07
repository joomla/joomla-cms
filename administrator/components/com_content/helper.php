<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Content Component Helper
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentHelper
{
	function saveContentPrep( &$row )
	{

		// Get submitted text from the request variables
		$text = JRequest::getVar( 'text', '', 'post', 'string', _J_ALLOWRAW );

		// Clean text for xhtml transitional compliance
		$text		= str_replace( '<br>', '<br />', $text );
		$row->title	= ampReplace($row->title);

		// Search for the {readmore} tag and split the text up accordingly.
		$tagPos	= JString::strpos( $text, '<hr id="system-readmore" />' );

		if ( $tagPos === false )
		{
			$row->introtext	= $text;
		} else
		{
			$row->introtext	= JString::substr($text, 0, $tagPos);
			$row->fulltext	= JString::substr($text, $tagPos + 27 );
		}

		return true;
	}

	/**
	* Function to reset Hit count of an article
	*
	*/
	function resetHits($redirect, $id)
	{
		global $mainframe;

		// Initialize variables
		$db	= & JFactory::getDBO();

		// Instantiate and load an article table
		$row = & JTable::getInstance('content', $db);
		$row->Load($id);
		$row->hits = 0;
		$row->store();
		$row->checkin();

		$msg = JText::_('Successfully Reset Hit count');
		$mainframe->redirect('index.php?option=com_content&sectionid='.$redirect.'&task=edit&hidemainmenu=1&id='.$id, $msg);
	}

	function menuLink($redirect, $id)
	{
		global $mainframe;

		// Initialize variables
		$db		= & JFactory::getDBO();
		$menu	= JRequest::getVar( 'menuselect', '', 'post' );
		$link	= JRequest::getVar( 'link_name', '', 'post' );

		$link	= ampReplace($link);

		// Instantiate a new menu item table
		$row = & JTable::getInstance('menu', $db);
		$row->menutype		= $menu;
		$row->name			= $link;
		$row->type			= 'content_item_link';
		$row->published		= 1;
		$row->componentid	= $id;
		$row->link			= 'index.php?option=com_content&task=view&id='.$id;
		$row->ordering		= 9999;

		// Make sure table values are valid
		if (!$row->check())
		{
			JError::raiseError( 500, $row->getError() );
			return false;
		}

		// Store the menu link
		if (!$row->store())
		{
			JError::raiseError( 500, $row->getError() );
			return false;
		}
		$row->checkin();
		$row->reorder("menutype = '$row->menutype' AND parent = $row->parent");

		$msg = sprintf(JText::_('LINKITEMINMENUCREATED'), $link, $menu);
		$mainframe->redirect('index.php?option=com_content&sectionid='.$redirect.'&task=edit&hidemainmenu=1&id='.$id, $msg);
	}

	function filterCategory($query, $active = NULL)
	{
		// Initialize variables
		$db	= & JFactory::getDBO();

		$categories[] = JHTML::makeOption('0', '- '.JText::_('Select Category').' -');
		$db->setQuery($query);
		$categories = array_merge($categories, $db->loadObjectList());

		$category = JHTML::selectList($categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

		return $category;
	}

}
?>