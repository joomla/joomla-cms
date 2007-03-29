<?php
/**
 * @version		$Id: view.php 4854 2006-08-31 11:29:11Z Jinx $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * @pacakge Joomla
 * @subpackage	Contacts
 */
class ContactViewCategory extends JView
{
	function display()
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$document	=& JFactory::getDocument();
		$document->link = JRoute::_( JURI::base().'index.php?option=com_contact&view=category&catid='.JRequest::getVar('catid', null, '', 'int'));

		$limit 		= JRequest::getVar('limit', 0, '', 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$catid  	= JRequest::getVar('catid', 0, '', 'int');

		$where		= ' WHERE a.published = 1';

		if ( $catid ) {
			$where .= ' AND a.catid = '. $catid;
		}

		$query = 'SELECT'
		. ' a.name AS title,'
		. ' CONCAT( "'.$link.'", a.catid, "&id=", a.id ) AS link,'
		. ' CONCAT( a.con_position, ' - ',a.misc ) AS description,'
		. ' "" AS date,'
		. ' c.title AS category,'
		. ' a.id AS id'
		. ' FROM #__contact_details AS a'
		. ' LEFT JOIN #__categories AS c ON c.id = a.catid'
		. $where
		. ' ORDER BY a.catid, a.ordering'
		;
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			$link = JRoute::_( JURI::base().'index.php?option=com_contact&view=contact&id='. $row->id . '&catid='.$row->catid );

			// strip html from feed item description text
			$description = $row->description;
			$date = ( $row->date ? date( 'r', strtotime($row->date) ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$document->addItem( $item );
		}
	}
}
?>
