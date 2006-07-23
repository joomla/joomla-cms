<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modStats
{
	function display($params)
	{
		global $mainframe;
		
		$db			= $mainframe->getDBO();
		
		$serverinfo = $params->get( 'serverinfo' );
		$siteinfo 	= $params->get( 'siteinfo' );
		
		if ($serverinfo) {
			modStats::showServerInfo($params);
		}
		
		if ($siteinfo) {
			modStats::showSiteInfo($params);
		}
		
		if ($mainframe->getCfg('enable_stats')) {
			modStats::showVisitorInfo($params);
		}
	}
	
	function showServerInfo($params) 
	{
		global $mainframe;
		
		$db = $mainframe->getDBO();
		
		echo "<strong>OS:</strong> "  . substr(php_uname(),0,7) . "<br />\n";
		echo "<strong>PHP:</strong> " .phpversion() . "<br />\n";
		echo "<strong>MySQL:</strong> " .$db->getVersion() . "<br />\n";
		echo "<strong>". JText::_( 'Time' ) .": </strong> " .date("H:i",time()+($mainframe->getCfg('offset')*60*60)) . "<br />\n";
		$c = $mainframe->getCfg('caching') ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
		echo "<strong>Caching:</strong> " . $c . "<br />\n";
		$z = $mainframe->getCfg('gzip') ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
		echo "<strong>GZIP:</strong> " . $z . "<br />\n";
	}
	
	function showSiteInfo($params) 
	{
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		
		$query="SELECT COUNT( id ) AS count_users"
		. "\n FROM #__users"
		;
		$db->setQuery($query);
		echo "<strong>". JText::_( 'Members' ) .":</strong> " .$db->loadResult() . "<br />\n";
	
		$query="SELECT COUNT( id ) AS count_items"
		. "\n FROM #__content"
		;
		$db->setQuery($query);
		echo "<strong>". JText::_( 'News' ) .":</strong> ".$db->loadResult() . "<br />\n";
	
		$query="SELECT COUNT( id ) AS count_links"
		. "\n FROM #__weblinks"
		. "\n WHERE published = 1"
		;
		$db->setQuery($query);
		echo "<strong>". JText::_( 'WebLinks' ) .":</strong> ".$db->loadResult() . "<br />\n";
	}
	
	function showVistorInfo($params)
	{
		$counter 	= $params->get( 'counter' );
		$increase 	= $params->get( 'increase' );
			
		if ($counter) 
		{
			$query = "SELECT SUM( hits ) AS count"
				. "\n FROM #__stats_agents"
				. "\n WHERE type = 1"
				;
			$db->setQuery( $query );
			$hits = $db->loadResult();
		
			$hits = $hits + $increase;
		
			if ($hits == NULL) {
				echo "<strong>" . JText::_( 'Visitors' ) . ":</strong> 0\n";
			} else {
				echo "<strong>" . JText::_( 'Visitors' ) . ":</strong> " . $hits . "\n";
			}
		}
	}
}
