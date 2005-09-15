<?php
/**
* @version $Id: frontend.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
		echo '<div class="message">'. $mosmsg .'</div>';
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

	mosFS::load( 'components/com_'. $name .'/'. $name .'.php' );
}

/**
* Cache some modules information
* @return array
*/
function &initModules() {
	global $database, $my, $Itemid;

	if (!isset( $GLOBALS['_MOS_MODULES'] )) {
		$query = "SELECT id, title, module, position, content, showtitle, params"
		."\n FROM #__modules AS m, #__modules_menu AS mm"
		. "\n WHERE m.published = '1'"
		. "\n AND m.access <= '$my->gid'"
		. "\n AND m.client_id = '0'"
		. "\n AND mm.moduleid = m.id"
		. "\n AND ( mm.menuid = '$Itemid' OR mm.menuid = '0')"
		. "\n ORDER BY ordering"
		;
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
	global $mosConfig_gzip, $mosConfig_caching;
	global $database, $my, $Itemid;

	$tp = mosGetParam( $_GET, 'tp', 0 );
	if ($tp) {
	    echo '<div style="height:50px;background-color:#eee;margin:2px;padding:10px;border:1px solid #f00;color:#700;">';
		echo $position;
		echo '</div>';
		return;
	}
	$style = intval( $style );

	mosFS::load( 'includes/frontend.html.php' );

	$allModules =& initModules();
	if (isset( $GLOBALS['_MOS_MODULES'][$position] )) {
	    $modules = $GLOBALS['_MOS_MODULES'][$position];
	} else {
		$modules = array();
	}

	if (count( $modules ) < 1) {
		$style = 0;
	}
	if ( $style == 1 ) {
		echo '<table cellspacing="1" cellpadding="0" border="0" width="100%">';
		echo '<tr>';
	}
	$prepend 	= ( $style == 1 ? '<td valign="top">' : '' );
	$postpend 	= ( $style == 1 ? '</td>' : '' );

	foreach ($modules as $module) {
		$params = new mosParameters( $module->params );

		echo $prepend;

		if (( substr( $module->module, 0, 4 ) ) == 'mod_') {
			modules_html::module2( $module, $params, $style );
		} else {
			modules_html::module( $module, $params, $style );
		}

		echo $postpend;
	}
	if ( $style == 1 ) {
		echo '</tr></table>';
	}
}

/**
* Assembles head tags
*/
function mosShowHead() {
	global $database, $my, $mainframe;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename;
	global $mosConfig_live_bookmark, $mosConfig_live_bookmark_file, $mosConfig_live_bookmark_show, $mosConfig_favicon;
	global $_VERSION, $_SERVER, $_LANG;

	$task = mosGetParam( $_REQUEST, 'task', '' );

	$mainframe->appendMetaTag( 'description', $mosConfig_MetaDesc );
	$mainframe->appendMetaTag( 'keywords', $mosConfig_MetaKeys );
	$mainframe->addMetaTag( 'Generator', $_VERSION->PRODUCT . ' - ' . $_VERSION->COPYRIGHT );
	$mainframe->addMetaTag( 'robots', 'index, follow' );

	echo $mainframe->getHead();

	if (isset( $mosConfig_sef ) && $mosConfig_sef) {
		echo '<base href="'. $mosConfig_live_site .'/" />';
	}

	if ($my->id) {
		$rtl = ($_LANG->rtl() ? '_rtl': '');
		?>
		<script language="JavaScript1.2" src="<?php echo $mosConfig_live_site;?>/includes/js/mambojavascript.js" type="text/javascript"></script>
		<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/includes/js/tabs/tabpane<?php echo $rtl; ?>.css" type="text/css" id="luna-tab-style-sheet" />

		<?php
		initEditor();
	}

	// favourites icon
	if ($mosConfig_favicon) {
		$icon = $mosConfig_absolute_path . $mosConfig_favicon;

		// checks to see if file exists
		if (!file_exists( $icon )) {
			$icon = $mosConfig_live_site .'/images/favicon.ico';
		} else {
			$icon = $mosConfig_live_site . $mosConfig_favicon;
		}

		// outputs link tag for page
		?>
		<link rel="shortcut icon" href="<?php echo $icon;?>" />
		<?php
	}

	// support for Live Bookmarks ability for site syndication
	if ($mosConfig_live_bookmark) {
		$show = 1;

		if ($mosConfig_live_bookmark_file) {
			$link_file 	= $mosConfig_live_site .'/cache/'. $mosConfig_live_bookmark_file;
		} else {
			$link_file 	= $mosConfig_live_site . '/index2.php?option=com_rss&feed='. $mosConfig_live_bookmark .'&no_html=1';
		}

		// xhtml check
		$link_file = ampReplace( $link_file );

		// when live bookmarks is only for the `home` page
		if ($mosConfig_live_bookmark_show) {
			if ($_SERVER['QUERY_STRING']) {
				// get home page info
				$query = "SELECT *"
				. "\n FROM #__menu"
				. "\n WHERE menutype = 'mainmenu'"
				. "\n AND published = '1'"
				. "\n ORDER BY parent, ordering"
				;
				$database->setQuery( $query, 0, 1 );
				$home_menu = $database->loadObjectList();

				// homepage link
				$home_link 		= $home_menu[0]->link .'&Itemid=' . $home_menu[0]->id;
				// current link
				$current_link	= 'index.php?'. $_SERVER['QUERY_STRING'];

				if ($current_link == $home_link) {
					$show = 0;
				}
			}
		}

		// outputs link tag for page
		if ($show) {
			?>
			<link rel="alternate" type="application/rss+xml" title="<?php echo $mosConfig_sitename; ?>" href="<?php echo $link_file; ?>" />
			<?php
		}
	}
}

/**
* Assembles head tags
*/
function mosShowHead_print() {
	global $mainframe, $_LANG;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename, $mosConfig_favicon;
	global $_VERSION, $_SERVER, $_LANG;

	?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
	<?php

	$cur_template = $mainframe->getTemplate();

	$mainframe->appendMetaTag( 'description', $mosConfig_MetaDesc );
	$mainframe->appendMetaTag( 'keywords', $mosConfig_MetaKeys );
	$mainframe->addMetaTag( 'Generator', $_VERSION->PRODUCT . ' - ' . $_VERSION->COPYRIGHT);

	echo $mainframe->getHead();

	if (isset( $mosConfig_sef ) && $mosConfig_sef) {
		echo '<base href="'. $mosConfig_live_site .'/" />';
	}

	// favourites icon
	if ($mosConfig_favicon) {
		$icon = $mosConfig_absolute_path . $mosConfig_favicon;

		// checks to see if file exists
		if (!file_exists( $icon )) {
			$icon = $mosConfig_live_site .'/images/favicon.ico';
		} else {
			$icon = $mosConfig_live_site . $mosConfig_favicon;
		}

		// outputs link tag for page
		?>
		<link rel="shortcut icon" href="<?php echo $icon;?>" />
		<?php
	}

	$path	= 'templates/'. $cur_template .'/css/';
	if (file_exists( $path .'print_css' )) {
		$css = $path .'print_css';
	} else {
		$css = $path .'template_css';
	}
	?>
	<link rel="stylesheet" href="<?php echo $css; ?>" type="text/css" />
	<?php
}

class mambotScreens {
	/**
	 * Static method to create the template object
	 * @param string The dynamic body file
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $bodyFile, $files=null ) {
		global $mainframe;
		global $mosConfig_absolute_path, $mosConfig_internal_templates;

		$tmpl =& mosFactory::getPatTemplate( $files );

		// default behavious is to load core file from /mambots/tmpl directory
		$root = $mosConfig_absolute_path .'/mambots/tmpl';

		if ($mosConfig_internal_templates) {
		// Loading of Custom Internal pT Templates
			$template 	= $mainframe->getTemplate();
			$path		= $mosConfig_absolute_path .'/templates/'. $template .'/mambots';
			// check if /modules directory exists in /templates directory
			if (file_exists( $path )) {
				// check if custom html file exists in /mambots directory
				if ( file_exists( $path .'/'. $bodyFile ) ) {
					$root = $path;
				}
			}
		}

		$tmpl->setRoot( $root );
		$tmpl->setTemplateCachePrefix('');

		$tmpl->setAttribute( 'body', 'src', $bodyFile );

		return $tmpl;
	}
}

class moduleScreens {
	/**
	 * Static method to create the template object
	 * @param string The dynamic body file
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $bodyFile, $files=null ) {
		global $mainframe;
		global $mosConfig_absolute_path, $mosConfig_internal_templates;

		$tmpl =& mosFactory::getPatTemplate( $files );

		// default behavious is to load core file from /modules/tmpl directory
		$root = $mosConfig_absolute_path .'/modules/tmpl';

		if ($mosConfig_internal_templates) {
		// Loading of Custom Internal pT Templates
			$template 	= $mainframe->getTemplate();
			$path		= $mosConfig_absolute_path .'/templates/'. $template .'/modules';
			// check if /modules directory exists in /templates directory
			if ( file_exists( $path ) ) {
				// check if custom html file exists in /modules directory
				if ( file_exists( $path .'/'. $bodyFile ) ) {
					$root = $path;
				}
			}
		}

		$tmpl->setRoot( $root );
		$tmpl->setTemplateCachePrefix('');

		$tmpl->readTemplatesFromFile( $bodyFile );

		return $tmpl;
	}
}

function mosComponentDirectory( $bodyFile, $directory='' ) {
	global $mosConfig_absolute_path, $mosConfig_internal_templates;
	global $mainframe, $option;

	// default behavious is to load core file from /components/{OPTION}/tmpl directory
	$root = $directory . '/tmpl';

	if ($mosConfig_internal_templates) {
	// Loading of Custom Internal pT Templates
		$template 	= $mainframe->getTemplate();
		$path		= $mosConfig_absolute_path .'/templates/'. $template .'/components/'. $option;
		// check if /components/{OPTION} directory exists in /templates directory
		if (file_exists( $path )) {
			// check if custom html file exists in /components/{OPTION} directory
			if (file_exists( $path .'/'. $bodyFile )) {
				$root = $path;
			}
		}
	}

	return $root;
}
?>