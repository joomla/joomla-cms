<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $mosConfig_offset, $mosConfig_caching, $mosConfig_enable_stats;
global $mosConfig_gzip;

$serverinfo = $params->get( 'serverinfo' );
$siteinfo 	= $params->get( 'siteinfo' );

$content = '';

if ($serverinfo) {
	echo "<strong>OS:</strong> "  . substr(php_uname(),0,7) . "<br />\n";
	echo "<strong>PHP:</strong> " .phpversion() . "<br />\n";
	echo "<strong>MySQL:</strong> " .$database->getVersion() . "<br />\n";
	echo "<strong>"._TIME_STAT.": </strong> " .date("H:i",time()+($mosConfig_offset*60*60)) . "<br />\n";
	$c = $mosConfig_caching ? 'Enabled':'Disabled';
	echo "<strong>Caching:</strong> " . $c . "<br />\n";
	$z = $mosConfig_gzip ? 'Enabled':'Disabled';
	echo "<strong>GZIP:</strong> " . $z . "<br />\n";
}

if ($siteinfo) {
	$query="SELECT COUNT( id ) AS count_users"
	. "\n FROM #__users"
	;
	$database->setQuery($query);
	echo "<strong>"._MEMBERS_STAT.":</strong> " .$database->loadResult() . "<br />\n";

	$query="SELECT COUNT( id ) AS count_items"
	. "\n FROM #__content"
	;
	$database->setQuery($query);
	echo "<strong>"._NEWS_STAT.":</strong> ".$database->loadResult() . "<br />\n";

	$query="SELECT COUNT( id ) AS count_links"
	. "\n FROM #__weblinks"
	. "\n WHERE published = 1"
	;
	$database->setQuery($query);
	echo "<strong>"._LINKS_STAT.":</strong> ".$database->loadResult() . "<br />\n";
}

if ($mosConfig_enable_stats) {
	$counter 	= $params->get( 'counter' );
	$increase 	= $params->get( 'increase' );
	if ($counter) {
		$query = "SELECT SUM( hits ) AS count"
		. "\n FROM #__stats_agents"
		. "\n WHERE type = 1"
		;
		$database->setQuery( $query );
		$hits = $database->loadResult();

		$hits = $hits + $increase;

		if ($hits == NULL) {
			$content .= "<strong>" . _VISITORS . ":</strong> 0\n";
		} else {
			$content .= "<strong>" . _VISITORS . ":</strong> " . $hits . "\n";
		}
	}
}
?>