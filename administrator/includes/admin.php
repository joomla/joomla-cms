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

/**
* @param string THe template position
*/
function mosCountAdminModules(  $position='left' ) {
	global $database;

	$query = "SELECT COUNT( m.id )"
	. "\n FROM #__modules AS m"
	. "\n WHERE m.published = '1'"
	. "\n AND m.position = '$position'"
	. "\n AND m.client_id = 1"
	;
	$database->setQuery( $query );

	return $database->loadResult();
}
/**
* Loads admin modules via module position
* @param string The position
* @param int 0 = no style, 1 = tabbed
*/
function mosLoadAdminModules( $position='left', $style=0 ) {
	global $database, $acl, $my;
    global $_LANG;

	$cache =& mosCache::getCache( 'com_content' );

	$query = "SELECT id, title, module, position, content, showtitle, params"
	. "\n FROM #__modules AS m"
	. "\n WHERE m.published = 1"
	. "\n AND m.position = '$position'"
	. "\n AND m.client_id = 1"
	. "\n ORDER BY m.ordering"
	;
	$database->setQuery( $query );
	$modules = $database->loadObjectList();
	if($database->getErrorNum()) {
		echo "MA ".$database->stderr(true);
		return;
	}

	switch ($style) {
		case 1:
			// Tabs
			$tabs = new mosTabs(1);
			$tabs->startPane( 'modules-' . $position );
			foreach ($modules as $module) {
				$params = new mosParameters( $module->params );
				$editAllComponents 	= $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' );
				// special handling for components module
				if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
					$tabs->startTab( $_LANG->_( $module->title ), 'module' . $module->id );
					if ( $module->module == '' ) {
						mosLoadCustomModule( $module, $params );
					} else {
						mosLoadAdminModule( substr( $module->module, 4 ), $params );
					}
					$tabs->endTab();
				}
			}
			$tabs->endPane();
			break;

		case 2:
			// Div'd
			foreach ($modules as $module) {
				$params = new mosParameters( $module->params );
				echo '<div>';
				if ( $module->module == '' ) {
					mosLoadCustomModule( $module, $params );
				} else {
					mosLoadAdminModule( substr( $module->module, 4 ), $params );
				}
				echo '</div>';
			}
			break;

		case 0:
		default:
			foreach ($modules as $module) {
				$params = new mosParameters( $module->params );
				if ( $module->module == '' ) {
					mosLoadCustomModule( $module, $params );
				} else {
					mosLoadAdminModule( substr( $module->module, 4 ), $params );
				}
			}
			break;
	}
}
/**
* Loads an admin module
*/
function mosLoadAdminModule( $name, $params=NULL ) {
	global $mosConfig_absolute_path, $mosConfig_live_site;
	global $database, $acl, $my, $mainframe, $option, $_LANG;

	$task = mosGetParam( $_REQUEST, 'task', '' );
	// legacy support for $act
	$act = mosGetParam( $_REQUEST, 'act', '' );

	$name = str_replace( '/', '', $name );
	$name = str_replace( '\\', '', $name );
	$path = "$mosConfig_absolute_path/administrator/modules/mod_$name.php";
	if (file_exists( $path )) {
		require $path;
	}
}

function mosLoadCustomModule( &$module, &$params ) {
	global $mosConfig_absolute_path, $_LANG;

	$rssurl 			= $params->get( 'rssurl', '' );
	$rssitems 			= $params->get( 'rssitems', '' );
	$rssdesc 			= $params->get( 'rssdesc', '' );
	$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', '' );

	echo '<table cellpadding="0" cellspacing="0" class="moduletable' . $moduleclass_sfx . '">';

	if ($module->content) {
		echo '<tr>';
		echo '<td>' . $module->content . '</td>';
		echo '</tr>';
	}

	// feed output
	if ( $rssurl ) {
		$cacheDir = $mosConfig_absolute_path .'/cache/';
		if (!is_writable( $cacheDir )) {
			echo '<tr>';
			echo '<td>'. $_LANG->_( 'Please make cache directory writable.' ) .'</td>';
			echo '</tr>';
		} else {
			$LitePath = $mosConfig_absolute_path .'/includes/Cache/Lite.php';
			require_once( $mosConfig_absolute_path .'/includes/domit/xml_domit_rss_lite.php');
			$rssDoc = new xml_domit_rss_document_lite();
			$rssDoc->useCacheLite(true, $LitePath, $cacheDir, 3600);
			$rssDoc->loadRSS( $rssurl );
			$totalChannels = $rssDoc->getChannelCount();

			for ($i = 0; $i < $totalChannels; $i++) {
				$currChannel =& $rssDoc->getChannel($i);
				echo '<tr>';
				echo '<td><strong><a href="'. $currChannel->getLink() .'" target="_child">';
				echo $currChannel->getTitle() .'</a></strong></td>';
				echo '</tr>';
				if ($rssdesc) {
					echo '<tr>';
					echo '<td>'. $currChannel->getDescription() .'</td>';
					echo '</tr>';
				}

				$actualItems = $currChannel->getItemCount();
				$setItems = $rssitems;

				if ($setItems > $actualItems) {
					$totalItems = $actualItems;
				} else {
					$totalItems = $setItems;
				}

				for ($j = 0; $j < $totalItems; $j++) {
					$currItem =& $currChannel->getItem($j);

					echo '<tr>';
					echo '<td><strong><a href="'. $currItem->getLink() .'" target="_child">';
					echo $currItem->getTitle() .'</a></strong> - '. $currItem->getDescription() .'</td>';
					echo '</tr>';
				}
			}
		}
	}
	echo '</table>';
}

/**
* Assembles head tags
*/
function mosShowHead_Admin() {
	global $database, $option, $my, $mainframe;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename, $mosConfig_favicon, $mosConfig_caching;
	global $_LANG, $_VERSION, $_MAMBOTS;

	$template 	= $mainframe->getTemplate();

	$mainframe->SetPageTitle( $mosConfig_sitename .' :: '. $_LANG->_( 'Administration' ) .'  [Mambo]' );
	$mainframe->appendMetaTag( 'description', $mosConfig_MetaDesc );
	$mainframe->appendMetaTag( 'keywords', $mosConfig_MetaKeys );
	$mainframe->addMetaTag( 'Generator', $_VERSION->PRODUCT . " - " . $_VERSION->COPYRIGHT);
	$mainframe->addMetaTag( 'robots', 'noindex, nofollow' );

	echo $mainframe->getHead();

	if ( $my->id ) {
		?>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/includes/js/JSCookMenu.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/includes/js/mambojavascript.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/ThemeOffice/theme.js"></script>
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

function mosMainBody_Admin() {
	echo $GLOBALS['_MOS_OPTION']['buffer'];
}
?>