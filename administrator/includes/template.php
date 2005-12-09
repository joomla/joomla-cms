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
	global $mosConfig_sitename, $mosConfig_lang;
	global $mainframe, $database, $my, $Itemid;

	$tp = mosGetParam( $_GET, 'tp', 0 );
	if ($tp) {
		echo '<div style="height:50px;background-color:#eee;margin:2px;padding:10px;border:1px solid #f00;color:#700;">';
		echo $position;
		echo '</div>';
		return;
	}
	$style = intval( $style );
	$cache =& JFactory::getCache( 'com_content' );

	require_once( JPATH_ADMINISTRATOR . '/includes/template.html.php' );

	$allModules =& initModules();
	if (isset( $GLOBALS['_MOS_MODULES'][$position] )) {
		$modules = $GLOBALS['_MOS_MODULES'][$position];
	} else {
		$modules = array();
	}

	foreach ($modules as $module) {

		global $mainframe;

		$lang =& $mainframe->getLanguage();
		$lang->load($module->module);

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
	global $database, $acl, $my, $mainframe, $option;

	$lang =& $mainframe->getLanguage();
	$lang->load('mod_'.$name);

	$task = mosGetParam( $_REQUEST, 'task', '' );

	$name = str_replace( '/', '', $name );
	$name = str_replace( '\\', '', $name );

	$path = JPATH_ADMINISTRATOR . '/modules/mod_' .$name. '.php';

	if (file_exists( $path )) {
		require $path;
	}
}

/**
* Assembles head tags
*/
function mosShowHead_Admin() {
	global $database, $my, $mainframe, $_VERSION;

	$page =& $mainframe->getPage();

	$template =  $mainframe->getTemplate();
	$lang     =& $mainframe->getLanguage();

	$page->setMetaContentType();
	$page->setTitle( $GLOBALS['mosConfig_sitename'] .' :: '. JText::_( 'Administration' ) .'  [Joomla!]' );
	$page->setMetaData( 'description', $GLOBALS['mosConfig_MetaDesc'] );
	$page->setMetaData( 'keywords', $GLOBALS['mosConfig_MetaKeys'] );
	$page->setMetaData( 'Generator', $_VERSION->PRODUCT . " - " . $_VERSION->COPYRIGHT);
	$page->setMetaData( 'robots', 'noindex, nofollow' );

	$suffix = ($lang->isRTL()) ? '_rtl': '';

	$page->addStyleSheet('templates/'.$template.'/css/template_css'.$suffix.'.css');
	$page->addStyleSheet('templates/'.$template.'/css/theme'.$suffix.'.css');

	if ( $my->id ) {
		$page->addScript( JURL_SITE.'/includes/js/JSCookMenu.js');
		$page->addScript( JURL_SITE.'/includes/js/joomla.javascript.js');
		$page->addScript( JURL_SITE.'/administrator/includes/js/ThemeOffice/theme'.$suffix.'.js');
	}

	$dirs = array(
		'/templates/'.$template.'/',
		'/',
	);

	foreach ($dirs as $dir ) {
		$icon =   $dir . 'favicon.ico';

		if(file_exists( JPATH_ADMINISTRATOR . $icon )) {
			$page->addFavicon(JURL_SITE . '/administrator'. $icon);
			break;
		}
	}

	echo $page->renderHead();
	
	// load editor
	initEditor();
}
?>
