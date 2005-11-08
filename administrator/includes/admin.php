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

function mosMainBody_Admin() {
	echo $GLOBALS['_MOS_OPTION']['buffer'];
}

/**
* Cache some modules information
* @return array
*/
function &initModules() {
	global $database, $my, $Itemid;

	if (!isset( $GLOBALS['_MOS_MODULES'] )) {
		$query = "SELECT id, title, module, position, content, showtitle, params"
			. "\n FROM #__modules AS m"
			. "\n WHERE m.published = 1"
			. "\n AND m.client_id = 1"
			. "\n ORDER BY m.ordering"
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
function mosCountAdminModules(  $position='left' ) {
	
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
* Loads admin modules via module position
* @param string The position
* @param int 0 = no style, 1 = tabbed
*/
function mosLoadAdminModules( $position='left', $style=0 ) {
	global $mosConfig_live_site, $mosConfig_sitename, $mosConfig_lang, $mosConfig_admin_path;
	global $mainframe, $database, $my, $Itemid, $_LANG;
	
	$tp = mosGetParam( $_GET, 'tp', 0 );
	if ($tp) {
		echo '<div style="height:50px;background-color:#eee;margin:2px;padding:10px;border:1px solid #f00;color:#700;">';
		echo $position;
		echo '</div>';
		return;
	}
	$style = intval( $style );
	$cache =& mosCache::getCache( 'com_content' );
	
	require_once( $mosConfig_admin_path . '/includes/admin.html.php' );

	$allModules =& initModules();
	if (isset( $GLOBALS['_MOS_MODULES'][$position] )) {
		$modules = $GLOBALS['_MOS_MODULES'][$position];
	} else {
		$modules = array();
	}
	
	foreach ($modules as $module) {
		
		$_LANG->load($module->module);
		
		$params = new mosParameters( $module->params );
		
		if(substr( $module->module, 0, 4 )  == 'mod_') {
			ob_start();
			mosLoadAdminModule(substr( $module->module, 4 ), $params);
			$module->content = ob_get_contents();
			ob_end_clean();
		}
			
		if ($params->get('cache') == 1 && $mosConfig_caching == 1) {
			$cache->call('modules_html::module', $module, $params, $style );
		} else {
			modules_html::module( $module, $params, $style );
		}
		
	}

	
}
/**
* Loads an admin module
*/
function mosLoadAdminModule( $name, $params=NULL ) {
	global $mosConfig_admin_path, $mosConfig_live_site;
	global $database, $acl, $my, $mainframe, $option, $_LANG;
	
	$_LANG->load('mod_'.$name);

	$task = mosGetParam( $_REQUEST, 'task', '' );

	$name = str_replace( '/', '', $name );
	$name = str_replace( '\\', '', $name );
	
	$path = $mosConfig_admin_path . '/modules/mod_' .$name. '.php';
	
	if (file_exists( $path )) {
		require $path;
	}
}

/**
* Assembles head tags
*/
function mosShowHead_Admin() {
	global $database, $option, $my, $mainframe;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename, $mosConfig_favicon, $mosConfig_caching, $mosConfig_admin_path, $mosConfig_admin_site;
	global $_LANG, $_VERSION, $_MAMBOTS;

	$template 	= $mainframe->getTemplate();
	
	$mainframe->SetPageTitle( $mosConfig_sitename .' :: '. $_LANG->_( 'Administration' ) .'  [Mambo]' );
	$mainframe->appendMetaTag( 'Content-Type', 'text/html; charset=utf-8' );
	$mainframe->appendMetaTag( 'description', $mosConfig_MetaDesc );
	$mainframe->appendMetaTag( 'keywords', $mosConfig_MetaKeys );
	$mainframe->addMetaTag( 'Generator', $_VERSION->PRODUCT . " - " . $_VERSION->COPYRIGHT);
	$mainframe->addMetaTag( 'robots', 'noindex, nofollow' );

	echo $mainframe->getHead();

	if ( $my->id ) {
		?>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/includes/js/JSCookMenu.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/includes/js/mambojavascript.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_admin_site; ?>/includes/js/ThemeOffice/theme.js"></script>
		<?php

		// load editor
		initEditor();
	}
	?>
	<link type="text/css" rel="stylesheet" href="templates/<?php echo $template; ?>/css/template_css<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" />
	<link type="text/css" rel="stylesheet" href="templates/<?php echo $template; ?>/css/theme<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" />
	<?php

	// favourites icon
	if ( $mosConfig_favicon ) {
		$icon = $mosConfig_absolute_path . $mosConfig_favicon;

		// checks to see if file exists
		if ( !file_exists( $icon ) ) {
			$icon = $mosConfig_live_site .'/images/favicon.ico';
		} else {
			$icon = $mosConfig_live_site . $mosConfig_favicon;
		}

		// outputs link tag for page
		?>
		<link rel="shortcut icon" href="<?php echo $icon;?>" />
		<?php
	}
}
?>