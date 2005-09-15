<?php
/**
 * @version $Id: admin.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Mambo
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @param string THe template position
*/
function mosCountAdminModules(  $position='left' ) {
	global $database, $my, $Itemid;

	$query = "SELECT COUNT( m.id )"
	. "\n FROM #__modules AS m"
	. "\n WHERE m.published = '1'"
	. "\n AND m.position = '$position'"
	. "\n AND m.client_id = '1'"
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
	. "\n WHERE m.published = '1'"
	. "\n AND m.position = '$position'"
	. "\n AND ( m.client_id = 1 )"
	. "\n ORDER BY m.ordering";

	$database->setQuery( $query );
	$modules = $database->loadObjectList();
	if( $database->getErrorNum() ) {
		echo 'MA '. $database->stderr(true);
		return;
	}

	switch ($style) {
	    case 0:
	    default:
			foreach ($modules as $module) {
				$params = new mosParameters( $module->params );
				// check to see whether a custom or normal module is being loaded
				if ( $module->module == '' || $module->module == 'custom' ) {
					mosLoadCustomModule( $module, $params );
				} else {
					mosLoadAdminModule( substr( $module->module, 4 ), $params );
				}
			}
			break;

		case 1:
		    // Tabs
			$tabs = new mosTabs(0);
			$tabs->startPane( 'modules-' . $position );
			foreach ($modules as $module) {
				$params = new mosParameters( $module->params );
				$editAllComponents 	= $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' );
				// special handling for components module
				if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
					$tabs->startTab( $_LANG->_( $module->title ), 'module' . $module->id );
					// check to see whether a custom or normal module is being loaded
					if ( $module->module == '' || $module->module == 'custom' ) {
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
				// check to see whether a custom or normal module is being loaded
				if ( $module->module == '' || $module->module == 'custom' ) {
					mosLoadCustomModule( $module, $params );
				} else {
					mosLoadAdminModule( substr( $module->module, 4 ), $params );
				}
				echo '</div>';
			}
			break;
	}
}
/**
* Loads an admin module
*/
function mosLoadAdminModule( $name, $params=NULL ) {
	global $mosConfig_absolute_path, $mosConfig_live_site;
	global $database, $acl, $my, $mainframe, $option;
    global $_LANG;

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
	global $mosConfig_absolute_path;
    global $_LANG;

    // params
	$rssurl 			= $params->get( 'rssurl', '' );
	$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', '' );

	// html output
	?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $moduleclass_sfx; ?>">
	<?php
	if ( $module->showtitle ) {
		// module title
		?>
		<tr>
			<th valign="top">
			<?php echo $module->title; ?>
			</th>
		</tr>
		<?php
	}
	if ($module->content) {
		// module html contents
		?>
		<tr>
			<td>
			<?php echo $module->content; ?>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php

	// feed output
	if ( $rssurl ) {
		// load RSS module file
		// kept for backward compatability
		mosFS::load( 'modules/mod_rss.php' );
	}
}

function mosShowSource( $filename, $withLineNums=false ) {
	global $_LANG;

    ini_set('highlight.html', '000000');
	ini_set('highlight.default', '#800000');
	ini_set('highlight.keyword','#0000ff');
	ini_set('highlight.string', '#ff00ff');
	ini_set('highlight.comment','#008000');

	if (!($source = @highlight_file( $filename, true ))) {
		return $_LANG->_( 'Operation Failed' );
	}
	$source = explode("<br />", $source);

	$ln = 1;

	$txt = '';
	foreach( $source as $line ) {
		$txt .= "<code>";
		if ($withLineNums) {
			$txt .= "<font color=\"#aaaaaa\">";
			$txt .= str_replace( ' ', '&nbsp;', sprintf( "%4d:", $ln ) );
			$txt .= "</font>";
		}
		$txt .= "$line<br /><code>";
		$ln++;
	}
	return $txt;
}

/**
* Assembles head tags
*/
function mosShowHead_Admin() {
	global $database, $option, $my, $mainframe;
	global $mosConfig_MetaDesc, $mosConfig_MetaKeys, $mosConfig_live_site, $mosConfig_sef, $mosConfig_absolute_path, $mosConfig_sitename, $mosConfig_favicon, $mosConfig_caching;
	global $_LANG, $_VERSION, $_MAMBOTS;

	// Content-Type must be first tag after the head
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
	<?php
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
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/browser.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/objects.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/events.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/element.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/document.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core/observable.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/controllers/submit.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/controllers/popup.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/components/mosToolbar.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/components/mosList.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/core.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/framework/init.js"></script>
		<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/includes/js/ThemeOffice/theme.js"></script>

		<link type="text/css" rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/includes/js/tabs/tabpane<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" id="luna-tab-style-sheet" />
		<?php

		// menu script
		if($mosConfig_caching) {
			$disabled = $mainframe->get('disableMenu', false);
			$filename = !$disabled ? 'cache_mod_fullmenu_enabled_'.$my->gid.'.js' : 'cache_mod_fullmenu_disabled.js';

			?>
			<script type="text/javascript" src="<?php echo $mosConfig_live_site ?>/cache/<?php echo $filename ?>"></script>
			<?php
		}

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

/**
 * @package Mambo
 */
class mosAdminHTML {

	/**
	 *
	 */
	function accessProcessing( &$row, $i ) {
		if ( !$row->access ) {
			$color_access = 'style="background-color: green;"';
			$task_access = 'accessregistered';
		} else if ( $row->access == 1 ) {
			$color_access = 'style="background-color: red;"';
			$task_access = 'accessspecial';
		} else {
			$color_access = 'style="background-color: black;"';
			$task_access = 'accesspublic';
		}

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')"  class="access'. $row->access .'">
		'. $row->groupname .'
		</a>'
		;

		return $href;
	}

	/**
	 *
	 */
	function checkedOut( &$row, $overlib=1, $task='checkin' ) {
		global $my, $acl;
		global $_LANG;

		$canConfig	= $acl->acl_check( 'com_config', 'manage', 'users', $my->usertype );

		$hover = '';
		if ( $overlib ) {
			$date 	= mosFormatDate( $row->checked_out_time, $_LANG->_( 'DATE_FORMAT_LC' ) );
			$time	= mosFormatDate( $row->checked_out_time, '%H:%M' );

			$text 	= '<table>';
			$text 	.= '<tr><td>'. $row->editor .'</td></tr>';
			$text 	.= '<tr><td>'. $date .'</td></tr>';
			$text 	.= '<tr><td>'. $time .'</td></tr>';
			if ( $canConfig || ( $row->checked_out == $my->id ) ) {
				$text 	.= '<tr><td><br/><small><strong>* '. $_LANG->_( 'Click to Check In' ) .' *</strong></small></td></tr>';
			}
			$text 	.= '</table>';

			$hover = 'onMouseOver="return overlib(\''. $text .'\', CAPTION, \''. $_LANG->_( 'Checked out' ) .'\', BELOW, RIGHT);" onMouseOut="return nd();"';
		}

		// individual item checkin functionality
		$checked = '<img src="images/checked_out.png" '. $hover .' border="0"/>';
		if ( $canConfig || ( $row->checked_out == $my->id ) ) {
			$checkin = '<a href="javascript:submitbutton(\''. $task .'\');">';
			$checkin .= $checked;
			$checkin .= '<a>';
			$checkin .= '<input type="hidden" name="id" value="'. $row->id .'" />';

			$checked = $checkin;
		}

		return $checked;
	}

	/**
	 *
	 */
	function checkedOutProcessing( &$row, $i, $task='checkin' ) {
		global $my;

		if ( $row->checked_out ) {
			$checked = mosAdminHTML::checkedOut( $row, 1, $task );
		} else {
			$checked = mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
		}

		return $checked;
	}

	/**
	 * Checks to see if an image exists in the current templates image directory
 	 * if it does it loads this image.  Otherwise the default image is loaded.
	 * Also can be used in conjunction with the menulist param to create the chosen image
	 * load the default or use no image
	 */
	function imageCheck( $file, $directory='/administrator/images/', $param=NULL, $param_directory='/administrator/images/', $alt=NULL, $name=NULL, $type=1, $align='middle', $width=NULL, $height=NULL) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;
		$cur_template = $mainframe->getTemplate();
		$size = '';

		if ( $name ) {
			$name = 'name="'. $name .'"';
		}

		if ( $width) {
			$size .= 'width="' . $width . ' "';
		}

		if ( $height) {
			$size .= 'height="' . $height . ' "';
		}

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" title="'. $alt .'" border="0" align="'. $align .'" '. $name . $size .'/>';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = $mosConfig_live_site .'/administrator/templates/'. $cur_template .'/images/'. $file;
			} else {
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" title="'. $alt .'" border="0" align="'. $align .'" '. $name . $size .'/>';
			}
		}

		return $image;
	}

	/**
	 *
	 */
	function publishedProcessing( &$row, $i ) {
		$img 	= $row->published ? 'tick.png' : 'publish_x.png';
		$task 	= $row->published ? 'unpublish' : 'publish';
		$alt 	= $row->published ? 'Published' : 'Unpublished';
		$action	= $row->published ? 'Unpublish Item' : 'Publish item';

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'" style="display: block;">
			<img src="images/'. $img .'" border="0" alt="'. $alt .'" />
		</a>'
		;

		return $href;
	}

	/**
	 * Shows order save icon
	 */
	function saveOrderIcon( &$rows ) {
		global $_LANG;
		?>
		<a href="javascript:saveorder(<?php echo count( $rows )-1; ?>)">
			<img src="images/filesave.png" border="0" width="16" height="16" alt="<?php echo $_LANG->_( 'Save Order' ); ?>"  title="<?php echo $_LANG->_( 'Save Order' ); ?>"/>
		</a>
		<?php
	}

	/**
	 * Select list of states
	 */
	function stateList( $name, $active=NULL, $javascript=NULL ) {
		global $_LANG;

		$states[] 	= mosHTML::makeOption( '', '- ' . $_LANG->_( 'State' ) . ' -' );
		$states[] 	= mosHTML::makeOption( '1', $_LANG->_( 'Published' ) );
		$states[] 	= mosHTML::makeOption( '0', $_LANG->_( 'Unpublished' ) );

		$state		= mosHTML::selectList( $states, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $state;
	}

	/**
	 * Select list of states
	 */
	function accessList( $name, $active=NULL, $javascript=NULL ) {
		global $_LANG;

		$rows[] 	= mosHTML::makeOption( '', '- ' . $_LANG->_( 'Access' ) . ' -' );
		$rows[] 	= mosHTML::makeOption( '0', $_LANG->_( 'Public' ) );
		$rows[] 	= mosHTML::makeOption( '1', $_LANG->_( 'Registered' ) );
		$rows[] 	= mosHTML::makeOption( '2', $_LANG->_( 'Special' ) );

		$access		= mosHTML::selectList( $rows, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $access;
	}

	/**
	 * Select list of active users that can legally edit content
	 * @param string The control name
	 * @param string the selected item
	 * @param boolean True to add the 'no user' option
	 * @param string Additional javascript for the control
	 * @param string The field to order by
	 */
	function userSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name' ) {
		global $database;
		global $_LANG;

		$users = array();

		if ( $nouser ) {
			$users[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'No User' ) .' -' );
		}

		$user = new mosUser( $database );
		$users = array_merge( $users, $user->getUserListFromGroup( 'author', null, 'RECURSE', $order ) );
		$users = array_merge( $users, $user->getUserListFromGroup( 'manager', null, 'RECURSE', $order ) );

		$userList = mosHTML::selectList( $users, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );
		unset( $users );

		return $userList;
	}
}

class moduleScreens_admin {
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
		$root = $mosConfig_absolute_path .'/administrator/modules/tmpl';

		if ( $mosConfig_internal_templates ) {
		// Loading of Custom Internal pT Templates
			$template 	= $mainframe->getTemplate();
			$path		= $mosConfig_absolute_path .'/administrator/templates/'. $template .'/modules';
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
?>