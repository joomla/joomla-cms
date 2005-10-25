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

defined( '_VALID_MOS' ) or die( 'Restricted access' );
/**
* Displays the capture output of the main element
*/
function mosMainBody() {
	// message passed via the url
	$mosmsg = mosGetParam( $_REQUEST, 'mosmsg', '' );
	if (!get_magic_quotes_gpc()) {
		$mosmsg = addslashes( $mosmsg );
	}

	$popMessages = false;

	if ($mosmsg && !$popMessages) {
		echo "\n<div class=\"message\">$mosmsg</div>";
	}

	echo $GLOBALS['_MOS_OPTION']['buffer'];

	if ($mosmsg && $popMessages) {
		echo "\n<script language=\"javascript\">alert('$mosmsg');</script>";
	}
}
/**
* Utility functions and classes
*/
function mosLoadComponent( $name ) {
	// set up some global variables for use by the frontend component
	global $mainframe, $database;
	include( $mainframe->getCfg( 'absolute_path' )."/components/com_$name/$name.php" );
}
/**
* Cache some modules information
* @return array
*/
function &initModules() {
	global $database, $my, $Itemid;

	if (!isset( $GLOBALS['_MOS_MODULES'] )) {
		$query = "SELECT id, title, module, position, content, showtitle, params"
		. "\n FROM #__modules AS m, #__modules_menu AS mm"
		. "\n WHERE m.published = 1"
		. "\n AND m.access <= '". $my->gid ."'"
		. "\n AND m.client_id != 1"
		. "\n AND mm.moduleid = m.id"
		. "\n AND ( mm.menuid = '". $Itemid ."' OR mm.menuid = 0 )"
		. "\n ORDER BY ordering";

		$database->setQuery( $query );
		$modules = $database->loadObjectList();
		foreach ($modules as $module) {
			$GLOBALS['_MOS_MODULES'][$module->position][] = $module;
		}
	}
	return $GLOBALS['_MOS_MODULES'];
}
/**
* @param string THe template position
*/
function mosCountModules( $position='left' ) {
	global $database, $my, $Itemid;

	$tp = mosGetParam( $_GET, 'tp', 0 );
	if ($tp) {
		return 1;
	}

	$modules =& initModules();
	if (isset( $GLOBALS['_MOS_MODULES'][$position] )) {
		return count( $GLOBALS['_MOS_MODULES'][$position] );
	} else {
		return 0;
	}
}
/**
* @param string The position
* @param int The style.  0=normal, 1=horiz, -1=no wrapper
*/
function mosLoadModules( $position='left', $style=0 ) {
	global $mosConfig_gzip, $mosConfig_absolute_path, $database, $my, $Itemid, $mosConfig_caching;

	$tp = mosGetParam( $_GET, 'tp', 0 );
	if ($tp) {
		echo '<div style="height:50px;background-color:#eee;margin:2px;padding:10px;border:1px solid #f00;color:#700;">';
		echo $position;
		echo '</div>';
		return;
	}
	$style = intval( $style );
	$cache =& mosCache::getCache( 'com_content' );

	require_once( $mosConfig_absolute_path . '/includes/frontend.html.php' );

	$allModules =& initModules();
	if (isset( $GLOBALS['_MOS_MODULES'][$position] )) {
		$modules = $GLOBALS['_MOS_MODULES'][$position];
	} else {
		$modules = array();
	}

	if (count( $modules ) < 1) {
		$style = 0;
	}
	if ($style == 1) {
		echo "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
		echo "<tr>\n";
	}
	$prepend = ($style == 1) ? "<td valign=\"top\">\n" : '';
	$postpend = ($style == 1) ? "</td>\n" : '';

	$count = 1;
	foreach ($modules as $module) {
		$params = new mosParameters( $module->params );

		echo $prepend;

		if ((substr("$module->module",0,4))=="mod_") {
			if ($params->get('cache') == 1 && $mosConfig_caching == 1) {
				$cache->call('modules_html::module2', $module, $params, $Itemid, $style );
			} else {
				modules_html::module2( $module, $params, $Itemid, $style, $count );
			}
		} else {
			if ($params->get('cache') == 1 && $mosConfig_caching == 1) {
				$cache->call('modules_html::module', $module, $params, $Itemid, $style );
			} else {
				modules_html::module( $module, $params, $Itemid, $style );
			}
		}

		echo $postpend;
		$count++;
	}
	if ($style == 1) {
		echo "</tr>\n</table>\n";
	}
}
/**
* Assembles head tags
*/
function mosShowHead() {
	global $database, $option, $my, $mainframe, $_VERSION;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename, $mosConfig_favicon;

	$task = mosGetParam( $_REQUEST, 'task', '' );

	if ( $my->id ) {
		?>
		<script language="JavaScript" src="<?php echo $mosConfig_live_site;?>/includes/js/joomla.javascript.js" type="text/javascript"></script>
		<?php
	}

	$mainframe->addMetaTag( 'Content-Type', 'text/html; charset=utf-8' );
	$mainframe->addMetaTag( 'Generator', $_VERSION->PRODUCT . " - " . $_VERSION->COPYRIGHT);
	$mainframe->addMetaTag( 'robots', 'index, follow' );

	$mainframe->appendMetaTag( 'description', $mosConfig_MetaDesc );
	$mainframe->appendMetaTag( 'keywords', $mosConfig_MetaKeys );

	echo $mainframe->getHead();
	
	if ( isset($mosConfig_sef) && $mosConfig_sef ) {
		echo "<base href=\"$mosConfig_live_site/\" />\r\n";
	}

	// support for Firefox Live Bookmarks ability for site syndication
	$query = "SELECT a.id"
	. "\n FROM #__components AS a"
	. "\n WHERE a.name = 'Syndicate'"
	;
	$database->setQuery( $query );
	$id = $database->loadResult();

	// load the row from the db table
	$row = new mosComponent( $database );
	$row->load( $id );

	// get params definitions
	$params = new mosParameters( $row->params, $mainframe->getPath( 'com_xml', $row->option ), 'component' );

	$live_bookmark = $params->get( 'live_bookmark', 0 );

	// support for Live Bookmarks ability for site syndication
	if ($live_bookmark) {
		$show = 1;

		$link_file 	= $mosConfig_live_site . '/index2.php?option=com_rss&feed='. $live_bookmark .'&no_html=1';

		// xhtml check
		$link_file = ampReplace( $link_file );

		// outputs link tag for page
		if ($show) {
			?>
			<link rel="alternate" type="application/rss+xml" title="<?php echo $mosConfig_sitename; ?>" href="<?php echo $link_file; ?>" />
			<?php
		}
	}

	// favourites icon
	if ( !$mosConfig_favicon ) {
		$mosConfig_favicon = 'favicon.ico';
	}
	$icon = $mosConfig_absolute_path .'/images/'. $mosConfig_favicon;
	// checks to see if file exists
	if ( !file_exists( $icon ) ) {
		$icon = $mosConfig_live_site .'/images/favicon.ico';
	} else {
		$icon = $mosConfig_live_site .'/images/' .$mosConfig_favicon;
	}

	// outputs link tag for page
	?>
	<link rel="shortcut icon" href="<?php echo $icon;?>" />
	<?php
}
?>