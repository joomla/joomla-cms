<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modStatsHelper
{
	function &getList( &$params )
	{
		global $mainframe;

		$db =& JFactory::getDBO();
		$rows = array();

		$serverinfo 		= $params->get( 'serverinfo' );
		$siteinfo 			= $params->get( 'siteinfo' );
		$counter 			= $params->get( 'counter' );
		$increase 			= $params->get( 'increase' );

		$i = 0;
		if ( $serverinfo )
		{
			$rows[$i]->title 	= JText::_( 'OS' );
			$rows[$i]->data 	= substr( php_uname(), 0, 7 );
			$i++;
			$rows[$i]->title 	= JText::_( 'PHP' );
			$rows[$i]->data 	= phpversion();
			$i++;
			$rows[$i]->title 	= JText::_( 'MySQL' );
			$rows[$i]->data 	= $db->getVersion();
			$i++;
			$rows[$i]->title 	= JText::_( 'Time' );
			$rows[$i]->data 	= date( 'H:i', time() + ( $mainframe->getCfg('offset') * 60 * 60 ) );
			$i++;
			$rows[$i]->title 	= JText::_( 'Caching' );
			$rows[$i]->data 	=  $mainframe->getCfg('caching') ? JText::_( 'Enabled' ):JText::_( 'Disabled' );
			$i++;
			$rows[$i]->title 	= JText::_( 'GZip' );
			$rows[$i]->data 	= $mainframe->getCfg('gzip') ? JText::_( 'Enabled' ):JText::_( 'Disabled' );
			$i++;
		}

		if ( $siteinfo )
		{
			$query = 'SELECT COUNT( id ) AS count_users'
			. ' FROM #__users'
			;
			$db->setQuery( $query );
			$members = $db->loadResult();

			$query = 'SELECT COUNT( id ) AS count_items'
			. ' FROM #__content'
			. ' WHERE state = "1"'
			;
			$db->setQuery( $query );
			$items = $db->loadResult();

			$query = 'SELECT COUNT( id ) AS count_links'
			. ' FROM #__weblinks'
			. ' WHERE published = "1"'
			;
			$db->setQuery( $query );
			$links = $db->loadResult();

			if ( $members ) {
				$rows[$i]->title 	= JText::_( 'Members' );
				$rows[$i]->data 	= $members;
				$i++;
			}

			if ( $items ) {
				$rows[$i]->title 	= JText::_( 'Content' );
				$rows[$i]->data 	= $items;
				$i++;
			}

			if ( $links ) {
				$rows[$i]->title 	= JText::_( 'Web Links' );
				$rows[$i]->data 	= $links;
				$i++;
			}

		}

		if( $counter )
		{
			$query = 'SELECT SUM( hits ) AS count_hits'
			. ' FROM #__content'
			. ' WHERE state = "1"'
			;
			$db->setQuery( $query );
			$hits = $db->loadResult();

			if ( $hits ) {
				$rows[$i]->title 	= JText::_( 'Content View Hits' );
				$rows[$i]->data 	= $hits + $increase;
				$i++;
			}
		}

		return $rows;
	}
}
