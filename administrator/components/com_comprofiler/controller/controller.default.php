<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;
use CBLib\Registry\Registry;
use CB\Database\CBDatabaseChecker;
use CB\Database\Table\FieldTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\ListTable;
use CB\Database\Table\PluginTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBController_default {
	// dummy for now
}	// class CBController_default

global $_CB_framework, $_CB_adminpath, $ueConfig;

$option				=	$_CB_framework->getRequestVar( 'option' );
$task				=	$_CB_framework->getRequestVar( 'task' );
$taskPart1			=	strtok( $task, '.' );
$cid				=	cbGetParam( $_REQUEST, 'cid', array( 0 ) );
$uid				=	cbGetParam( $_REQUEST, 'uid', array( 0 ) );

if ( ! is_array( $cid ) ) {
	$cid			=	array ( (int) $cid );
}
$taskPart1			=	strtok( $task, '.' );

if ( ! is_array( $uid ) ) {
	$uid			=	array ( (int) $uid );
}

cbimport( 'language.all' );
cbimport( 'cb.tabs' );
cbimport( 'cb.imgtoolbox' );
cbimport( 'cb.adminfilesystem' );
cbimport( 'cb.installer' );
cbimport( 'cb.params' );
cbimport( 'cb.pagination' );

switch ( $taskPart1 ) {
	case 'loadSampleData':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( array( 'core.admin' ) );
		loadSampleData();
		break;
	case 'loadCanvasLayout':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( array( 'core.admin' ) );
		loadCanvasLayout();
		break;
	case 'syncUsers':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		syncUsers();
        break;
	case 'checkcbdb':
		cbSpoofCheck( 'plugin', 'REQUEST' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		checkcbdb( (int) cbGetParam( $_REQUEST, 'databaseid', 0 ) );
		break;
	case 'fixcbdb':
		cbSpoofCheck( 'plugin', 'REQUEST' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		fixcbdb( (int) cbGetParam( $_REQUEST, 'dryrun', 1 ), (int) cbGetParam( $_REQUEST, 'databaseid', 0 ) );
		break;
	case 'fixacldb':
		cbSpoofCheck( 'plugin', 'REQUEST' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		fixacldb();
		break;
	case 'fixcbmiscdb':
		cbSpoofCheck( 'plugin', 'REQUEST' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		fixcbmiscdb();
		break;
	case 'fixcbdeprecdb':
		cbSpoofCheck( 'plugin', 'REQUEST' );
		checkCanAdminPlugins( array( 'core.admin', 'core.edit' ) );
		fixcbdeprecdb();
		break;
	case 'cancelPlugin':
		checkCanAdminPlugins( 'core.edit' );
		cancelPlugin( $option );
		break;
	case 'cancelPluginAction':
		checkCanAdminPlugins( 'core.edit' );
		cancelPluginAction( $option );
		break;
	case 'installPluginUpload':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.admin' );
		installPluginUpload();
		break;
	case 'installPluginDir':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.admin' );
		installPluginDir();
		break;
	case 'installPluginURL':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.admin' );
		installPluginURL();
		break;
	case 'installPluginDisc':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.admin' );
		installPluginDisc();
		break;
	case 'latestVersion':
		latestVersion();
		break;
	case 'fieldclass':
	case 'tabclass':
	case 'pluginclass':
		tabClass( $option, $task, (int) cbGetParam( $_REQUEST, 'user', 0 ) );
		break;
	case 'finishinstallation':
		finishInstallation( $option );
		break;
	default:
		break;
}

function deleteUsers( $cid, $inComprofilerOnly = false ) {
	global $_CB_framework;

	$msg		=	null;

	if ( ! Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.admin', 'com_comprofiler' ) ) {
		$msg	=	CBTxt::T('You cannot delete a user. Only higher-level users have this power.');
	}

	if (!$msg && is_array( $cid ) && count( $cid )) {
		new cbTabs( 0, 2, null, false );		// loads plugins
		foreach ($cid as $id) {
			$obj		=	null;
			if ( ! $inComprofilerOnly ) {
				$obj	=&	$_CB_framework->_getCmsUserObject( (int) $id );
			}
			if ( ( $obj !== null ) || $inComprofilerOnly ) {

				// Just a double-check as framework checks that too:
				if ( ( $_CB_framework->myId() != $id ) && ( ( $obj === null ) || ! ( Application::User( (int) $id )->isSuperAdmin() && ! Application::MyUser()->isSuperAdmin() ) ) ) {
					// delete user
					$result = cbDeleteUser( $id, null, $inComprofilerOnly );
					if ( $result === null ) {
						$msg .= CBTxt::T('User not found');
					} elseif (is_string( $result ) && ( $result != "" ) ) {
						$msg .= $result;
					}
				} else {
					// cannot delete Super Admin where it is the only one that exists
					$msg .= CBTxt::T('You cannot delete yourself nor a Super Administrator without being Super Administrator');
				}
			} else {
				$msg .= CBTxt::T('User not found');
			}
		}
	}

	return $msg;
}

function _cbAdaptNameFieldsPublished( &$newConfig ) {
	global $_CB_database;

	if ( ! isset( $newConfig['this_is_default_config'] ) ) {
		// checks and adapts only if it's not the default configuration:
		switch ( $newConfig['name_style'] ) {
			case 2:
				$sqlArray	=	array( 'name'	=>	0, 'firstname'	=>	1, 'middlename' => 0,	'lastname' => 1 );
				break;
			case 3:
				$sqlArray	=	array( 'name'	=>	0, 'firstname'	=>	1, 'middlename' => 1,	'lastname' => 1 );
				break;
			case 1:
			default:
				$sqlArray	=	array( 'name'	=>	1, 'firstname'	=>	0, 'middlename' => 0,	'lastname' => 0 );
				break;
		}
		foreach ( $sqlArray as $name => $published ) {
			$sql			=	'UPDATE #__comprofiler_fields SET '
							.	$_CB_database->NameQuote( 'published' )
							.	' = '
							.	(int) $published
							.	' WHERE '
							.	$_CB_database->NameQuote( 'name' )
							.	' = '
							.	$_CB_database->Quote( $name )
							;
			$_CB_database->setQuery( $sql );
			// This will raise an exception in case of error:
			$_CB_database->query();
		}
	}
}

/**
 * Commented CBT calls for language parser pickup: Moved to cb.core.php so they get picked-up in front-end language file and not in backend one.
 */

function loadSampleData() {
	global $_CB_Backend_Title;

	@set_time_limit( 240 );

	$_CB_Backend_Title	=	array( 0 => array( 'fa fa-wrench', CBTxt::T( 'TOOLS_SAMPLE_DATA_TITLE', 'CB Tools: Sample Data: Results' ) ) );

	$return				=	null;
	$affected			=	false;

	$tab				=	new TabTable();

	$tab->load( array( 'title' => '_UE_ADDITIONAL_INFO_HEADER' ) );

	if ( ! $tab->tabid ) {
		$affected		=	true;

		$tab->set( 'title', '_UE_ADDITIONAL_INFO_HEADER' );
		$tab->set( 'displaytype', 'menunested' );
		$tab->set( 'position', 'canvas_main_middle' );
		$tab->set( 'viewaccesslevel', 1 );
		$tab->set( 'enabled', 1 );
		$tab->set( 'ordering', 1 );

		if ( $tab->getError() || ( ! $tab->store() ) ) {
			$return		.=	'<div class="form-group cb_form_line clearfix text-danger">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_TAB_NOT_OK', 'Tab [title] failed to add. Error: [error]', array( '[title]' => $tab->get( 'title' ), '[error]' => $tab->getError() ) )
						.	'</div>';
		}
	}

	if ( $affected ) {
		$return			.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_TAB_OK', 'Tab Added Successfully!' )
						.	'</div>';
	}

	$affected			=	false;

	$fields				=	array(	'cb_website' => array( 'title' => '_UE_Website', 'type' => 'webaddress', 'registration' => 0, 'ordering' => 1 ),
									'cb_location' => array( 'title' => '_UE_Location', 'type' => 'text', 'maxlength' => 50, 'size' => 25, 'registration' => 0, 'ordering' => 2 ),
									'cb_occupation' => array( 'title' => '_UE_Occupation', 'type' => 'text', 'registration' => 0, 'ordering' => 3 ),
									'cb_interests' => array( 'title' => '_UE_Interests', 'type' => 'text', 'registration' => 0, 'ordering' => 4 ),
									'cb_company' => array( 'title' => '_UE_Company', 'type' => 'text', 'ordering' => 5 ),
									'cb_city' => array( 'title' => '_UE_City', 'type' => 'text', 'ordering' => 6 ),
									'cb_state' => array( 'title' => '_UE_State', 'type' => 'text', 'maxlength' => 10, 'size' => 4, 'ordering' => 7 ),
									'cb_zipcode' => array( 'title' => '_UE_ZipCode', 'type' => 'text', 'ordering' => 8 ),
									'cb_country' => array( 'title' => '_UE_Country', 'type' => 'text', 'ordering' => 9 ),
									'cb_address' => array( 'title' => '_UE_Address', 'type' => 'text', 'ordering' => 10 ),
									'cb_phone' => array( 'title' => '_UE_PHONE', 'type' => 'text', 'ordering' => 11 ),
									'cb_fax' => array( 'title' => '_UE_FAX', 'type' => 'text', 'ordering' => 12 )
								);

	foreach ( $fields as $fieldName => $fieldSettings ) {
		$field			=	new FieldTable();

		$field->load( array( 'name' => $fieldName ) );

		if ( ! $field->fieldid ) {
			$affected	=	true;

			$field->set( 'name', $fieldName );
			$field->set( 'registration', 1 );
			$field->set( 'profile', 1 );
			$field->set( 'edit', 1 );
			$field->set( 'published', 1 );

			foreach ( $fieldSettings as $column => $value ) {
				$field->set( $column, $value );
			}

			$field->set( 'tabid', $tab->tabid );
			$field->set( 'pluginid', 1 );

			if ( $field->getError() || ( ! $field->store() ) ) {
				$return	.=	'<div class="form-group cb_form_line clearfix text-danger">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_FIELD_NOT_OK', 'Field [name] failed to add. Error: [error]', array( '[name]' => $field->get( 'name' ), '[error]' => $field->getError() ) )
						.	'</div>';
			}
		}
	}

	if ( $affected ) {
		$return			.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_FIELD_OK', 'Fields Added Successfully!' )
						.	'</div>';
	}

	$affected			=	false;

	$list				=	new ListTable();

	$list->load( array( 'title' => 'Members List' ) );

	if ( ! $list->listid ) {
		$affected		=	true;

		$list->set( 'title', 'Members List' );
		$list->set( 'viewaccesslevel', 1 );
		$list->set( 'usergroupids', '1|*|6|*|7|*|2|*|3|*|4|*|5|*|8' );
		$list->set( 'default', 1 );
		$list->set( 'published', 1 );
		$list->set( 'ordering', 1 );

		$listParams		=	new Registry();

		$listParams->set( 'sort_mode', '0' );
		$listParams->set( 'basic_sort', array( array( 'column' => 'username', 'direction' => 'ASC' ) ));
		$listParams->set( 'columns', array(	array( 'title' => 'User', 'size' => '3', 'fields' => array(
												array( 'field' => '17', 'display' => '4' ),
												array( 'field' => '29', 'display' => '4' ),
												array( 'field' => '42', 'display' => '4' ),
												array( 'field' => '26', 'display' => '4' )
											)),
											array( 'title' => 'Info', 'size' => '9', 'fields' => array(
												array( 'field' => '27', 'display' => '1' ),
												array( 'field' => '49', 'display' => '1' ),
												array( 'field' => '28', 'display' => '1' )
											))
										));
		$listParams->set( 'list_grid_layout', '1' );

		$list->set( 'params', $listParams->asJson() );

		if ( $list->getError() || ( ! $list->store() ) ) {
			$return		.=	'<div class="form-group cb_form_line clearfix text-danger">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_LIST_NOT_OK', 'List [title] failed to add. Error: [error]', array( '[title]' => $list->get( 'title' ), '[error]' => $tab->getError() ) )
						.	'</div>';
		}
	}

	if ( $affected ) {
		$return			.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_LIST_OK', 'List Added Successfully!' )
						.	'</div>';
	}

	if ( ! $return ) {
		$return			.=	'<div class="form-group cb_form_line clearfix">'
						.		CBTxt::T( 'TOOLS_SAMPLE_DATA_ALREADY_CONFIGURED', 'Sample Data is already loaded!' )
						.	'</div>';
	}

	echo $return;
}

function loadCanvasLayout() {
	global $_CB_database, $_CB_Backend_Title, $ueConfig;

	$_CB_Backend_Title	=	array( 0 => array( 'fa fa-wrench', CBTxt::T( 'CB Tools: Canvas layout: Results' ) ) );

	$return				=	null;
	$affected			=	false;

	// TABS
	$tabs				=	array(	17 => array( 'displaytype' => 'html', 'position' => 'canvas_menu', 'ordering' => 1 ),			// Menu
									18 => array( 'displaytype' => 'html', 'position' => 'cb_head', 'ordering' => 1 ),				// Connections Path
									11 => array( 'displaytype' => 'menu', 'position' => 'canvas_main_middle', 'ordering' => 1 ),	// Contact Info
									10 => array( 'displaytype' => 'menu', 'position' => 'canvas_main_middle', 'ordering' => 2 ),	// Articles
									8 => array( 'displaytype' => 'menu', 'position' => 'canvas_main_middle', 'ordering' => 3 ),		// Blogs
									9 => array( 'displaytype' => 'menu', 'position' => 'canvas_main_middle', 'ordering' => 4 ),		// Forums
									7 => array( 'displaytype' => 'html', 'position' => 'canvas_background', 'ordering' => 1 ),		// Canvas
									6 => array( 'displaytype' => 'html', 'position' => 'canvas_stats_bottom', 'ordering' => 1 ),	// Statistics
									19 => array( 'displaytype' => 'html', 'position' => 'canvas_title_middle', 'ordering' => 1 ),	// Page Title
									20 => array( 'displaytype' => 'html', 'position' => 'canvas_photo', 'ordering' => 1 ),			// Portrait
									21 => array( 'displaytype' => 'html', 'position' => 'canvas_main_right', 'ordering' => 1 ),		// Status
								);

	// Move and adjust canvas specific tabs as needed:
	foreach ( $tabs as $tabId => $tabSettings ) {
		$set			=	array();
		$where			=	array();

		foreach ( $tabSettings as $column => $value ) {
			$set[]		=	$_CB_database->NameQuote( $column ) . " = " . $_CB_database->Quote( $value );
			$where[]	=	$_CB_database->NameQuote( $column ) . " != " . $_CB_database->Quote( $value );
		}

		$query			=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_tabs' )
						.	"\n SET " . implode( ', ', $set )
						.	"\n WHERE " . $_CB_database->NameQuote( 'tabid' ) . " = " . (int) $tabId
						.	"\n AND ( ( " . implode( ' ) OR ( ', $where ) . " ) )";

		$_CB_database->setQuery( $query );

		if ( $_CB_database->query() && $_CB_database->getAffectedRows() ) {
			$affected	=	true;

			$return		.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_CANVAS_TAB_OK', 'Tab [tabid] configured for canvas display successfully.', array( '[tabid]' => (int) $tabId ) )
						.	'</div>';
		}
	}

	// Move remaining tabs to canvas menu nested (excludes column positions):
	$query				=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_tabs' )
						.	"\n SET " . $_CB_database->NameQuote( 'displaytype' ) . " = " . $_CB_database->Quote( 'menunested' )
						.	', ' . $_CB_database->NameQuote( 'position' ) . " = " . $_CB_database->Quote( 'canvas_main_middle' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'position' ) . " IN " . $_CB_database->safeArrayOfStrings( array( 'cb_left', 'cb_middle', 'cb_right', 'cb_tabmain' ) );
	$_CB_database->setQuery( $query );

	if ( $_CB_database->query() && $_CB_database->getAffectedRows() ) {
		$affected		=	true;

		$return			.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_CANVAS_REMAINING_TABS_OK', 'Remaining tabs configured for canvas display successfully.' )
						.	'</div>';
	}

	// FIELDS
	$fields				=	array(	17 => array( 'tabid' => 7, 'profile' => 4, 'ordering' => 1 ),	// Canvas
									24 => array( 'tabid' => 6, 'profile' => 2, 'ordering' => 1 ),	// Connections
									25 => array( 'tabid' => 6, 'profile' => 2, 'ordering' => 2 ),	// Hits
									29 => array( 'tabid' => 20, 'profile' => 4, 'ordering' => 1 ),	// Avatar
									26 => array( 'tabid' => 20, 'profile' => 4, 'ordering' => 2 ),	// Online Status
									28 => array( 'tabid' => 21, 'profile' => 2, 'ordering' => 1 ),	// Member Since
									27 => array( 'tabid' => 21, 'profile' => 2, 'ordering' => 2 ),	// Last Online
									49 => array( 'tabid' => 21, 'profile' => 2, 'ordering' => 3 ),	// Last Updated
								);

	// Move and adjust canvas specific fields as needed:
	foreach ( $fields as $fieldId => $fieldSettings ) {
		$set			=	array();
		$where			=	array();

		foreach ( $fieldSettings as $column => $value ) {
			$set[]		=	$_CB_database->NameQuote( $column ) . " = " . $_CB_database->Quote( $value );
			$where[]	=	$_CB_database->NameQuote( $column ) . " != " . $_CB_database->Quote( $value );
		}

		$query			=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_fields' )
						.	"\n SET " . implode( ', ', $set )
						.	"\n WHERE " . $_CB_database->NameQuote( 'fieldid' ) . " = " . (int) $fieldId
						.	"\n AND ( ( " . implode( ' ) OR ( ', $where ) . " ) )";
		$_CB_database->setQuery( $query );

		if ( $_CB_database->query() && $_CB_database->getAffectedRows() ) {
			$affected	=	true;

			$return		.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_CANVAS_FIELD_OK', 'Field [fieldid] configured for canvas display successfully.', array( '[fieldid]' => (int) $fieldId ) )
						.	'</div>';
		}
	}

	// Set remaining fields in canvas positions expecting 2 line field display to 2 line
	$query				=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_fields' ) . ' AS f'
						.	', ' . $_CB_database->NameQuote( '#__comprofiler_tabs' ) . ' AS t'
						.	"\n SET f." . $_CB_database->NameQuote( 'profile' ) . " = 2"
						.	"\n WHERE t." . $_CB_database->NameQuote( 'tabid' ) . ' = f.' . $_CB_database->NameQuote( 'tabid' )
						.	"\n AND t." . $_CB_database->NameQuote( 'position' ) . " IN " . $_CB_database->safeArrayOfStrings( array( 'canvas_stats_top', 'canvas_stats_middle', 'canvas_stats_bottom', 'canvas_main_left', 'canvas_main_right' ) )
						.	"\n AND f." . $_CB_database->NameQuote( 'profile' ) . " != 0";
	$_CB_database->setQuery( $query );

	if ( $_CB_database->query() && $_CB_database->getAffectedRows() ) {
		$affected		=	true;

		$return			.=	'<div class="form-group cb_form_line clearfix text-success">'
						.		CBTxt::T( 'TOOLS_CANVAS_REMAINING_FIELD_OK', 'Remaining fields configured for canvas display successfully.' )
						.	'</div>';
	}

	if ( $ueConfig['use_divs'] != 1 ) {
		$affected		=	true;

		$ueConfig['use_divs']	=	"1";

		$newConfig				=	json_encode( $ueConfig );

		$query					=	"UPDATE " . $_CB_database->NameQuote( '#__comprofiler_plugin' )
								.	"\n SET " . $_CB_database->NameQuote( 'params' ) . " = " . $_CB_database->Quote( $newConfig )
								.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = 1";
		$_CB_database->setQuery( $query );

		if ( $_CB_database->query() && $_CB_database->getAffectedRows() ) {
			$return				.=	'<div class="form-group cb_form_line clearfix text-success">'
								.		CBTxt::T( 'TOOLS_CANVAS_TEMPLATE_OUTPUT_OK', 'Template output configured for canvas display successfully.' )
								.	'</div>';
		}
	}

	if ( ! $affected ) {
		$return			=	'<div class="form-group cb_form_line clearfix">'
						.		CBTxt::T( 'TOOLS_CANVAS_ALREADY_CONFIGURED', 'Canvas display already configured.' )
						.	'</div>';
	}

	echo $return;
}

function syncUsers() {
    global $_CB_database, $_CB_Backend_Title, $ueConfig, $_PLUGINS;

	$_CB_Backend_Title	=	array( 0 => array( 'fa fa-wrench', CBTxt::T('CB Tools: Synchronize users: Results') ) );

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

   	$_PLUGINS->loadPluginGroup('user');
	$messages	=	$_PLUGINS->trigger( 'onBeforeSyncUser', array( true ) );
	foreach ( $messages as $msg ) {
		if ( $msg ) {
			echo '<div class="form-group cb_form_line clearfix">' . $msg . '</div>';
		}
	}
	// 0a. delete user table for bad rows
	$sql = "DELETE FROM #__users WHERE id = 0";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s not allowed user id 0 entry.'), $affected) . '</div>';
	}

	// 0b. delete comprofiler table for bad rows
	$sql = "DELETE FROM #__comprofiler WHERE id = 0";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s not allowed user id 0 entry.'), $affected) . '</div>';
	}


    // 1. add missing comprofiler entries, guessing naming depending on CB's name style:
	switch ( $ueConfig['name_style'] ) {
		case 2:
			// firstname + lastname:
 			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,lastname,firstname) "
 				  ." SELECT id,id, SUBSTRING_INDEX(name,' ',-1), "
 								 ."SUBSTRING( name, 1, length( name ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ) "
 				  ." FROM #__users";
		break;
		case 3:
			// firstname + middlename + lastname:
			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,middlename,lastname,firstname) "
				 . " SELECT id,id,SUBSTRING( name, INSTR( name, ' ' ) +1,"
				 						  ." length( name ) - INSTR( name, ' ' ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ),"
				 		 ." SUBSTRING_INDEX(name,' ',-1),"
				 		 ." IF(INSTR(name,' '),SUBSTRING_INDEX( name, ' ', 1 ),'') "
				 . " FROM #__users";
    		break;
    	default:
 			// name only:
			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id) SELECT id,id FROM #__users";
   			break;
    }
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Added %s new entries to Community Builder from users Table.'), $affected) . '</div>';
	}

	$sql = "UPDATE #__comprofiler SET `user_id`=`id`";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Fixed %s existing entries in Community Builder: fixed wrong user_id.'), $affected) . '</div>';
	}

	// 2. remove excessive comprofiler entries (e.g. if admin used mambo/joomla delete user function:
	$sql = "SELECT c.id FROM #__comprofiler c LEFT JOIN #__users u ON u.id = c.id WHERE u.id IS NULL";
	$_CB_database->setQuery($sql);

	try {
		$users	=	$_CB_database->loadResultArray();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	if (count($users)) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Removing %s entries from Community Builder missing in users Table.'), count($users)) . '</div>';
		$msg = deleteUsers($users, true);
		print '<div class="form-group cb_form_line clearfix">'.$msg.'</div>';
	}

	print '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('Joomla User Table and Joomla Community Builder User Table now in sync!') . '</div>';

	$messages	=	$_PLUGINS->trigger( 'onAfterSyncUser', array( true ) );

	foreach ( $messages as $msg ) {
		if ( $msg ) {
			echo '<div class="form-group cb_form_line clearfix">' . $msg . '</div>';
		}
	}
}

function checkcbdb( $dbId = 0 ) {
	global $_CB_database, $_CB_framework, $ueConfig, $_PLUGINS;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm('checkcbdb');

	outputCbTemplate( 2 );
	outputCbJs( 2 );

	global $_CB_Backend_Title;
	$_CB_Backend_Title	=	array( 0 => array( 'fa fa-wrench', CBTxt::T('CB Tools: Check database: Results') ) );

	$cbSpoofField			=	cbSpoofField();
	$cbSpoofString			=	cbSpoofString( null, 'plugin' );

	$version				=	$_CB_database->getVersion();
	$version				=	substr( $version, 0, strpos( $version, '-' ) );

	if ( $dbId == 0 ) {

		echo '<div class="text-left"><div class="form-group cb_form_line clearfix">'. CBTxt::T('Checking Community Builder Database') .':</div>';

		// 1. check comprofiler_field_values table for bad rows
		$sql = "SELECT fieldvalueid,fieldid FROM #__comprofiler_field_values WHERE fieldid=0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in Community Builder comprofiler_field_values have bad fieldid values.'), count($bad_rows)) . '</div>';
	   		foreach ($bad_rows as $bad_row) {
				if ( $bad_row->fieldvalueid == 0 ) {
					echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ZERO fieldvalueid illegal: fieldvalueid=%s fieldid=0'), $bad_row->fieldvalueid) . '</div>';
				} else {
					echo '"<div class="form-group cb_form_line clearfix text-danger">fieldvalueid="' . $bad_row->fieldvalueid . " fieldid=0</div>";
				}
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('This one can be fixed by <strong>first backing up database</strong>') . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbmiscdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T('then by clicking here') . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All Community Builder comprofiler_field_values table fieldid rows all match existing fields.') . '</div>';
		}

		// 2.	check if comprofiler_field_values table has entries where corresponding fieldtype value in comprofiler_fields table
		//		does not allow values
		$sql = "SELECT v.fieldvalueid, v.fieldid, f.name, f.type FROM #__comprofiler_field_values as v, #__comprofiler_fields as f WHERE v.fieldid = f.fieldid AND f.type NOT IN ('checkbox','multicheckbox','select','multiselect','radio')";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in Community Builder comprofiler_field_values link back to fields of wrong fieldtype.'), count($bad_rows)) . '</div>';
			foreach ($bad_rows as $bad_row) {
				echo '<div class="form-group cb_form_line clearfix text-danger">fieldvalueid=' . $bad_row->fieldvalueid . ' fieldtype=' . $bad_row->type .'</div>';
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('This one can be fixed in SQL using a tool like phpMyAdmin.') . '</div>';
			// not done automatically since some fields might have field values ! echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&task=fixcbmiscdb&$cbSpoofField=$cbSpoofString" ) . '">by clicking here</a>.</font></p>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All Community Builder comprofiler_field_values table rows link to correct fieldtype fields in comprofiler_field table.') . '</div>';
		}

		// 5.	check if all cb defined fields have corresponding comprofiler columns
		$sql = "SELECT * FROM #__comprofiler";
		$_CB_database->setQuery($sql, 0, 1);
		$all_comprofiler_fields_and_values = $_CB_database->loadAssoc();

		$all_comprofiler_fields = array();
		if ( $all_comprofiler_fields_and_values === null ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif ( is_array( $all_comprofiler_fields_and_values ) ) {
			while ( false != ( list( $_cbfield ) = each( $all_comprofiler_fields_and_values ) ) ) {
				array_push( $all_comprofiler_fields, $_cbfield );
			}
		}

		$sql							=	"SELECT * FROM #__comprofiler_fields WHERE `name` != 'NA' AND `table` = '#__comprofiler'";
		$_CB_database->setQuery( $sql );
		$field_rows						=	$_CB_database->loadObjectList( null, '\CB\Database\Table\FieldTable', array( &$_CB_database ) );
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} else {
			$html_output					=	array();
			$cb11							=	true;
			foreach ( $field_rows as $field_row ) {
				if ( $field_row->tablecolumns !== null ) {
					// CB 1.2 way:
					if ( $field_row->tablecolumns != '' ) {
						$tableColumns			=	explode( ',', $field_row->tablecolumns );
						foreach ( $tableColumns as $col ) {
							if ( ! in_array( $col, $all_comprofiler_fields ) ) {
								$html_output[]	=	'<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T(' - Field %s - Column %s is missing from comprofiler table.'), $field_row->name, $col) . '</div>';
							}
						}
					}
					$cb11					=	false;
				} else {
					// cb 1.1 way
					if ( ! in_array( $field_row->name, $all_comprofiler_fields ) ) {
						$html_output[] = '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T(' - Column %s is missing from comprofiler table.'), $field_row->name) . '</div>';
					}
				}
			}
			if ( count( $html_output ) > 0 ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('There are %s column(s) missing in the comprofiler table, which are defined as fields (rows in comprofiler_fields):'), count( $html_output )) . '</div>';
				echo implode( '', $html_output );
				echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('This one can be fixed by deleting and recreating the field(s) using components / Community Builder / Field Management.') . '<br />' . CBTxt::T('Please additionally make sure that columns in comprofiler table <strong>are not also duplicated in users table</strong>.') . '</div>';
			} elseif ( $cb11 ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('All Community Builder fields from comprofiler_fields are present as columns in the comprofiler table, but comprofiler_fields table is not yet upgraded to CB 1.2 table structure. Just going to Community Builder Fields Management will fix this automatically.') . '</div>';
			} else {
				echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All Community Builder fields from comprofiler_fields are present as columns in the comprofiler table.') . '</div>';
			}
		}
		// 9. Check if images/comprofiler is writable:
		$folder = 'images/comprofiler/';
		echo '<div class="form-group cb_form_line clearfix">' . CBTxt::T( 'Checking Community Builder folders:' ) . '</div>';
		if ( ! is_writable( $_CB_framework->getCfg('absolute_path'). '/' . $folder ) ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Avatars and thumbnails folder: %s/%s is NOT writeable by the webserver.'), $_CB_framework->getCfg('absolute_path'), $folder) . ' </div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('Avatars and thumbnails folder is Writeable.') . '</div>';
		}

		// 10. check if depreciated core plugins are still core plugins
		$sql = "SELECT `name`, `id` FROM `#__comprofiler_plugin` WHERE `element` IN ( 'winclassic', 'webfx', 'osx', 'luna', 'dark', 'yanc', 'cb.mamblogtab', 'cb.simpleboardtab', 'cb.authortab' ) AND `iscore` = 1";
		$_CB_database->setQuery( $sql );
		$bad_rows = $_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder _comprofiler_plugin have bad iscore values.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">plugin=' . $bad_row->name . ' pluginid=' . $bad_row->id . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdeprecdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder _comprofiler_plugin table iscore values are correct.' ) . '</div>';
		}

		// 11. check if depreciated core tabs are still system tabs
		$sql = "SELECT `title`, `tabid` FROM `#__comprofiler_tabs` WHERE `pluginclass` IN ( 'getNewslettersTab', 'getBlogTab', 'getForumTab', 'getAuthorTab' ) AND `sys` = 1";
		$_CB_database->setQuery( $sql );
		$bad_rows = $_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder _comprofiler_tabs have bad sys values.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">tab=' . $bad_row->title . ' tabid=' . $bad_row->tabid . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdeprecdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder _comprofiler_tabs table sys values are correct.' ) . '</div>';
		}

		// 12. check if depreciated core fields are still system fields
		$sql = "SELECT `title`, `fieldid` FROM `#__comprofiler_fields` WHERE `type` IN ( 'forumstats', 'forumsettings' ) AND `sys` = 1";
		$_CB_database->setQuery( $sql );
		$bad_rows = $_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder _comprofiler_fields have bad sys values.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">field=' . $bad_row->title . ' fieldid=' . $bad_row->fieldid . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdeprecdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder _comprofiler_fields table sys values are correct.' ) . '</div>';
		}

		// 13. check if new core plugins are core
		$sql = "SELECT `name`, `id` FROM `#__comprofiler_plugin` WHERE `element` IN ( 'cbarticles', 'cbforums', 'cbblogs' ) AND `iscore` != 1";
		$_CB_database->setQuery( $sql );
		$bad_rows = $_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder _comprofiler_plugin have bad iscore values.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">plugin=' . $bad_row->name . ' pluginid=' . $bad_row->id . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdeprecdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder _comprofiler_plugin table iscore values are correct.' ) . '</div>';
		}

		// 13. check if new core tabs are core
		$sql = "SELECT `title`, `tabid` FROM `#__comprofiler_tabs` WHERE `pluginclass` IN ( 'cbarticlesTab', 'cbforumsTab', 'cbblogsTab' ) AND `sys` != 1";
		$_CB_database->setQuery( $sql );
		$bad_rows = $_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder _comprofiler_tabs have bad sys values.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">plugin=' . $bad_row->name . ' pluginid=' . $bad_row->id . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdeprecdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder _comprofiler_tabs table sys values are correct.' ) . '</div>';
		}

		// 14. check if there are duplicate plugins
		$sql					=	'SELECT p1.' . $_CB_database->NameQuote( 'name' )
								.	', p1.' . $_CB_database->NameQuote( 'id' )
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin' ) . " AS p1"
								.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__comprofiler_plugin' ) . " AS p2"
								.	"\n WHERE p1." . $_CB_database->NameQuote( 'id' ) . " > p2." . $_CB_database->NameQuote( 'id' )
								.	"\n AND p1." . $_CB_database->NameQuote( 'element' ) . " = p2." . $_CB_database->NameQuote( 'element' );
		$_CB_database->setQuery( $sql );
		$bad_rows				=	$_CB_database->loadObjectList();

		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'ERROR: sql query: %s : returned error: %s' ), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() ) ) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf( CBTxt::T( 'Warning: %s entries in Community Builder __comprofiler_plugin are duplicates.' ), count( $bad_rows ) ) . '</div>';

			foreach ( $bad_rows as $bad_row ) {
				echo '<div class="form-group cb_form_line clearfix text-danger">plugin=' . $bad_row->name . ' pluginid=' . $bad_row->id . '</div>';
			}

			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T( 'This one can be fixed by <strong>first backing up database</strong>' ) . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbmiscdb&$cbSpoofField=$cbSpoofString" ) . '"> ' . CBTxt::T( 'then by clicking here' ) . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'All Community Builder __comprofiler_plugin table rows are unique.' ) . '</div>';
		}

		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkCBMandatoryDb( false );
		$dbName					=	CBTxt::T('Core CB mandatory basics');
		$messagesAfter			=	array();
		$messagesBefore			=	array();
		CBDatabaseChecker::renderDatabaseResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );

		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkDatabase( false );

	   	$_PLUGINS->loadPluginGroup('user');
		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterCheckCbDb', array( true ) );

		$dbName					=	CBTxt::T('Core CB');
		$messagesBefore			=	array();
		CBDatabaseChecker::renderDatabaseResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
		echo '</div>';
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	} elseif ( $dbId == 1 ) {
		// Check plugins db:
		$dbName					=	CBTxt::T('CB plugin');
		$messagesBefore			=	array();
		$messagesAfter			=	array();
		$result					=	true;

		cbimport( 'cb.installer' );
		$sql					=	'SELECT `id`, `name` FROM `#__comprofiler_plugin` ORDER BY `ordering`';
		$_CB_database->setQuery( $sql );
		$plugins				=	$_CB_database->loadObjectList();
		if ( ! $_CB_database->getErrorNum() ) {
			$cbInstaller		=	new cbInstallerPlugin();
			foreach ( $plugins as $plug ) {
				$result			=	$cbInstaller->checkDatabase( $plug->id, false );
				if ( is_bool( $result ) ) {
					CBDatabaseChecker::renderDatabaseResults( $cbInstaller, false, false, $result, $messagesBefore, $messagesAfter, $dbName . ' "' . $plug->name . '"', $dbId, false );
				} elseif ( is_string( $result ) ) {
					echo '<div class="form-group cb_form_line clearfix text-warning">' . $dbName . ' "' . $plug->name . '"' . ': ' . $result . '</div>';
				} else {
					echo '<div class="form-group cb_form_line clearfix">' . sprintf(CBTxt::T('%s "%s": no database or no database description.'),$dbName ,$plug->name) . '</div>';
				}
			}
		}
		$dbName					=	CBTxt::T('CB plugins');
		$null					=	null;
		CBDatabaseChecker::renderDatabaseResults( $null, false, false, $result, array(), array(), $dbName, $dbId, true );

	} elseif ( $dbId == 2 ) {

		echo '<div class="text-left"><div class="form-group cb_form_line clearfix">' . CBTxt::T('Checking Users Database') . ':</div>';

		// 3.	check if comprofiler table is in sync with users table
		$sql = "SELECT c.id FROM #__comprofiler c LEFT JOIN #__users u ON u.id = c.id WHERE u.id IS NULL";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in Community Builder comprofiler table without corresponding user table rows.'), count($bad_rows)) . '</div>';
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Following comprofiler id: %s are missing in user table'), implode( ', ', $badids )) . ( isset( $badids[0] ) ? " " . CBtxt::T('This comprofiler entry with id 0 should be removed, as it\'s not allowed.') : "" ) . '</div>';
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th( 'This one can be fixed using menu Components / Community Builder / tools and then click "Synchronize users".' ) . '</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All Community Builder comprofiler table rows have links to user table.') . '</div>';
		}

		// 4.	check if users table is in sync with comprofiler table
		$sql = "SELECT u.id FROM #__users u LEFT JOIN #__comprofiler c ON c.id = u.id WHERE c.id IS NULL";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in users table without corresponding comprofiler table rows.'), count($bad_rows)) . '</div>';
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('users id: %s are missing in comprofiler table'), implode( ', ', $badids )) . '</div>';
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th( 'This one can be fixed using menu Components / Community Builder / tools and then click "Synchronize users".' ) . '</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All users table rows have links to comprofiler table.') . '</div>';
		}

		// 6.	check if users table has id=0 in it
		$sql = "SELECT u.id FROM #__users u WHERE u.id = 0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in users table with id=0.'), count($bad_rows)) . '</div>';
			foreach ($bad_rows as $bad_row) {
				echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('users id=%s is not allowed.'), $bad_row->id) . '</div>';
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th( 'This one can be fixed using menu Components / Community Builder / tools and then click "Synchronize users".' ) . '</div>';
			// echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('This one can be fixed in SQL using a tool like phpMyAdmin.') . " <strong><u>" . CBTxt::T('You also need to check in SQL if id is autoincremented.') . "<u><strong></font></p>";
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('users table has no zero id row.') . '</div>';
		}
		// 7.	check if comprofiler table has id=0 in it
		$sql = "SELECT c.id FROM #__comprofiler c WHERE c.id = 0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in comprofiler table with id=0.'), count($bad_rows)) . '</div>';
			foreach ($bad_rows as $bad_row) {
				echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('comprofiler id=%s is not allowed.'), $bad_row->id) . '</div>';
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th('This one can be fixed using menu Components / Community Builder / Tools and then click "Synchronize users".' ) . '</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('comprofiler table has no zero id row.') . '</div>';
		}
		// 8.	check if comprofiler table has user_id != id in it
		$sql = "SELECT c.id, c.user_id FROM #__comprofiler c WHERE c.id <> c.user_id";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in comprofiler table with user_id <> id.'), count($bad_rows)) . '</div>';
			foreach ($bad_rows as $bad_row) {
				echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('comprofiler id=%s is different from user_id=%s.'), $bad_row->id, $bad_row->user_id) . '</div>';
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th( 'This one can be fixed using menu Components / Community Builder / tools and then click "Synchronize users".' ) . '</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All rows in comprofiler table have user_id columns identical to id columns.') . '</div>';
		}

		// 10.	check if #__user_usergroup_map table is in sync with users table	: A: user -> aro
		if ( ! cbStartOfStringMatch( $version, '3.23' ) ) {
			$sql = "SELECT u.id FROM #__users u LEFT JOIN #__user_usergroup_map a ON a.user_id = CAST( u.id AS CHAR ) WHERE a.user_id IS NULL";
		} else {
			$sql = "SELECT u.id FROM #__users u LEFT JOIN #__user_usergroup_map a ON a.user_id = u.id WHERE a.user_id IS NULL";
		}
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif ( count( $bad_rows ) != 0 ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('Warning: %s entries in the users table without corresponding user_usergroup_map table rows.'), count($bad_rows)) .'</div>';
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('user id: %s are missing in user_usergroup_map table'), implode( ', ', $badids ));
			echo ( isset( $badids[0] ) ? " " . CBTxt::T('This user entry with id 0 should be removed, as it\'s not allowed.') : "" ) . '</div>';
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::T('This one can be fixed by <strong>first backing up database</strong>') . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixacldb&$cbSpoofField=$cbSpoofString" ) . '">' . CBTxt::T('then by clicking here') . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('All users table rows have ACL entries in user_usergroup_map table.') . '</div>';
		}

		// 11.	check if #__user_usergroup_map table is in sync with users table	: B: aro -> user
		$sql = "SELECT a.user_id AS id FROM #__user_usergroup_map a LEFT JOIN #__users u ON u.id = a.user_id WHERE u.id IS NULL";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $_CB_database->getErrorNum() ) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::Th('ERROR: sql query: %s : returned error: %s'), htmlspecialchars( $sql ), stripslashes( $_CB_database->getErrorMsg() )) .  '</div>';
		} elseif (count($bad_rows)!=0) {
			echo '<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::Th('Warning: %s entries in the __user_usergroup_map table without corresponding users table rows.'), count($bad_rows)) . '</div>';
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]		=	"user id=" . $bad_row->id;
			}
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th( 'DATABASE_CHECK_ENTRIES_OF_TABLE_MISSING_IN_TABLE', 'Following entries of [tablename1] table are missing in [tablename2] table: [badids].', array( '[tablename1]' => 'user_usergroup_map', '[tablename2]' => 'users', '[badids]' => implode( ', ', $badids ))) . ( isset( $badids[0] ) ? "<br /> " . CBTxt::T('This user_usergroup_map entry with (user) value 0 should be removed, as it\'s not allowed.') : "" ) . '</div>';
			echo '<div class="form-group cb_form_line clearfix text-danger">' . CBTxt::Th('This one can be fixed by <strong>first backing up database</strong>') . ' <a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixacldb&$cbSpoofField=$cbSpoofString" ) . '">' . CBTxt::T('then by clicking here') . '</a>.</div>';
		} else {
			echo '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::Th( 'DATABASE_CHECK_ALL_ENTRIES_OF_TABLE_HAVE_CORRESPONDANCE_IN_TABLE', 'All [tablename1] table rows have corresponding entries in [tablename2] table.', array( '[tablename1]' => 'ACL user_usergroup_map', '[tablename2]' => 'users') ) . '</div>';
		}

		$dbName					=	CBTxt::T('Users');
		echo '</div>';

	} elseif ( $dbId == 3 ) {
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

		$strictcolumns			=	( cbGetParam( $_REQUEST, 'strictcolumns', 0 ) == 1 );

		// Check fields db:
		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkAllCBfieldsDb( false, false, $strictcolumns );
		$dbName					=	CBTxt::T('CB fields data storage');
		$messagesBefore			=	array();

		$_PLUGINS->loadPluginGroup('user');
		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterCheckCbFieldsDb', array( true ) );

		if ( $strictcolumns ) {
			$dbId				=	$dbId . '&strictcolumns=1';
		}

		CBDatabaseChecker::renderDatabaseResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
	}
	else {
		$dbName					=	CBTxt::T( 'DATABASE_CHECK_NO_DATABASE_SPECIFIED', 'No Database Specified' );
	}

	global $_CB_Backend_Title;
	$_CB_Backend_Title			=	array( 0 => array( 'fa fa-wrench', sprintf(CBTxt::T("CB Tools: Check %s database: Results"), $dbName) ) );
}

function fixacldb() {
	global $_CB_database;
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	// 2. delete #__user_usergroup_map table entries which are not in users table	: B: aro -> user
	$sql = "DELETE a FROM #__user_usergroup_map a LEFT JOIN #__users u ON u.id = a.user_id WHERE u.id IS NULL";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s __user_usergroup_map entries which didn\'t correspond to users table.'), $affected) .'</div>';
	}

	// 3. add missing #__user_usergroup_map table entries to put in sync with #__user_usergroup_map table	A: aro -> groups
	$sql = "INSERT INTO #__user_usergroup_map (user_id,group_id) SELECT u.id AS user_id, 2 AS group_id FROM #__users u LEFT JOIN #__user_usergroup_map g ON g.user_id = u.id WHERE g.user_id IS NULL";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Added %s new entries to __user_usergroup_map table from __user_usergroup_map Table.'),$affected) . '</div>';
	}

	print '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('Joomla User Table and Joomla ACL Table should now be in sync!') . '</div>';

}

function fixcbdb( $dryRun, $dbId = 0 ) {
	global $_CB_database, $ueConfig, $_PLUGINS;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	$dryRun						=	( $dryRun == 1 );

	if ( $dbId == 0 ) {
		// Fix mandatory basics of core CB:
		cbimport( 'cb.dbchecker' );
		$dbName					=	CBTxt::T('Core CB mandatory basics');
		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkCBMandatoryDb( true, $dryRun );
		$messagesAfter			=	array();
		$messagesBefore			=	array();

		ob_start();
		CBDatabaseChecker::renderDatabaseResults( $dbChecker, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
		$html					=	ob_get_contents();
		ob_end_clean();


		// Fix core CB:

	   	$_PLUGINS->loadPluginGroup('user');
		$dbName					=	CBTxt::T('Core CB');
		$messagesBefore			=	$_PLUGINS->trigger( 'onBeforeFixDb', array( $dryRun ) );
		$messagesBefore[]		=	$html;
		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkDatabase( true, $dryRun );

		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterFixDb', array( $dryRun ) );

		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	} elseif ( $dbId == 1 ) {
		// Fix plugin $dbId:
		$dbName					=	CBTxt::T('CB plugin');
		$messagesBefore			=	array();
		$messagesAfter			=	array();
		$result					=	true;

		cbimport( 'cb.installer' );
		$sql					=	'SELECT `id`, `name` FROM `#__comprofiler_plugin` ORDER BY `ordering`';
		$_CB_database->setQuery( $sql );
		$plugins				=	$_CB_database->loadObjectList();
		if ( ! $_CB_database->getErrorNum() ) {
			$cbInstaller		=	new cbInstallerPlugin();
			foreach ( $plugins as $plug ) {
				$result			=	$cbInstaller->checkDatabase( $plug->id, true, $dryRun );
				if ( is_bool( $result ) ) {
					CBDatabaseChecker::renderDatabaseResults( $cbInstaller, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName . ' "' . $plug->name . '"', $dbId, false );
				} elseif ( is_string( $result ) ) {
					echo '<div class="form-group cb_form_line clearfix text-warning">' . $dbName . ' "' . $plug->name . '"' . ': ' . $result . '</div>';
				} else {
					echo '<div class="form-group cb_form_line clearfix">' . sprintf(CBTxt::T('%s "%s": no database or no database description.'),$dbName ,$plug->name) . '</div>';
				}
			}
		}
		$dbName					=	CBTxt::T('CB plugins');

	} elseif ( $dbId == 3 ) {
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	   	$_PLUGINS->loadPluginGroup('user');
		$messagesBefore			=	$_PLUGINS->trigger( 'onBeforeFixFieldsDb', array( $dryRun ) );

		$strictcolumns			=	( cbGetParam( $_REQUEST, 'strictcolumns', 0 ) == 1 );

		// Check fields db:
		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBDatabaseChecker();
		$result					=	$dbChecker->checkAllCBfieldsDb( true, $dryRun, $strictcolumns );
		$dbName					=	CBTxt::T('CB fields data storage');
		$messagesAfter			=	array();

		if ( $strictcolumns ) {
			$dbId				=	$dbId . '&strictcolumns=1';
		}
	}
	else {
		$dbName					=	CBTxt::T( 'DATABASE_CHECK_NO_DATABASE_SPECIFIED', 'No Database Specified' );
		$result					=	$dbName;
		$messagesBefore			=	array();
		$messagesAfter			=	array();
	}

	_CBsecureAboveForm('fixcbdb');

	outputCbTemplate( 2 );
	outputCbJs( 2 );

	global $_CB_Backend_Title;
	$_CB_Backend_Title			=	array( 0 => array( 'fa fa-wrench', sprintf(CBTxt::T("CB Tools: Fix %s database: "),$dbName) . ( $dryRun ? CBTxt::T('Dry-run:') : CBTxt::T('Fixed:') ) . " " .CBTXT::T("Results") ) );

	CBDatabaseChecker::renderDatabaseResults( $dbChecker, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
}

function fixcbmiscdb() {
	global $_CB_database, $_CB_Backend_Title;
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	$_CB_Backend_Title			=	array( 0 => array( 'fa fa-wrench', CBTxt::T( 'CB Tools: Fix Misc: Results' ) ) );

	// 1. delete comprofiler_field_values table for bad rows
	$sql = "DELETE FROM #__comprofiler_field_values WHERE fieldid=0";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s comprofiler_field_values entries which didn\'t match any field.'), $affected) . '</div>';
	}

	// 2. delete comprofiler_field_values table has entries where corresponding fieldtype value in comprofiler_fields table
	//		does not allow values
/* not done ! as some new fields might not be listed in here ! :
	$sql = "DELETE v FROM #__comprofiler_field_values as v, #__comprofiler_fields as f WHERE v.fieldid = f.fieldid AND f.type NOT IN ('checkbox','multicheckbox','select','multiselect','radio')";
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s comprofiler_field_values entries which didn\'t match any field.'), $affected) . '</div>';
	}
*/

	// 3. delete duplicate comprofiler_plugin table rows
	$sql					=	'DELETE p1'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin' ) . " AS p1"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__comprofiler_plugin' ) . " AS p2"
							.	"\n WHERE p1." . $_CB_database->NameQuote( 'id' ) . " > p2." . $_CB_database->NameQuote( 'id' )
							.	"\n AND p1." . $_CB_database->NameQuote( 'element' ) . " = p2." . $_CB_database->NameQuote( 'element' );
	$_CB_database->setQuery($sql);

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf(CBTxt::T('Deleted %s __comprofiler_plugin duplicate entries.'), $affected) . '</div>';
	}

	print '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T('Misc database adjustments complete!') . '</div>';

}

function fixcbdeprecdb() {
	global $_CB_database, $_CB_Backend_Title;

	@set_time_limit( 240 );

	$_CB_Backend_Title			=	array( 0 => array( 'fa fa-wrench', CBTxt::T( 'CB Tools: Fix Deprecated: Results' ) ) );

	$sql = "UPDATE `#__comprofiler_plugin` SET `iscore` = 0 WHERE `element` IN ( 'winclassic', 'webfx', 'osx', 'luna', 'dark', 'yanc', 'cb.mamblogtab', 'cb.simpleboardtab', 'cb.authortab' )";
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();

	if ( $affected ) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf( CBTxt::T( 'Fixed %s _comprofiler_plugin entries with incorrect iscore values.' ), $affected ) . '</div>';
	}

	$sql = "UPDATE `#__comprofiler_tabs` SET `sys` = 0 WHERE `pluginclass` IN ( 'getNewslettersTab', 'getBlogTab', 'getForumTab', 'getAuthorTab' )";
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();

	if ( $affected ) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf( CBTxt::T( 'Fixed %s _comprofiler_tabs entries with incorrect sys values.' ), $affected ) . '</div>';
	}

	$sql = "UPDATE `#__comprofiler_fields` SET `sys` = 0 WHERE `type` IN ( 'forumstats', 'forumsettings' )";
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();

	if ( $affected ) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf( CBTxt::T( 'Fixed %s _comprofiler_fields entries with incorrect sys values.' ), $affected ) . '</div>';
	}

	$sql = "SELECT `id` FROM `#__comprofiler_plugin` WHERE `element` = 'cbarticles' ORDER BY `id` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$fieldid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_plugin` WHERE `element` = 'cbarticles' AND `id` <> " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_plugin` SET `id` = 17, `iscore` = 1 WHERE `id` = " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();

	$sql = "SELECT `id` FROM `#__comprofiler_plugin` WHERE `element` = 'cbforums' ORDER BY `id` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$fieldid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_plugin` WHERE `element` = 'cbforums' AND `id` <> " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_plugin` SET `id` = 18, `iscore` = 1 WHERE `id` = " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$affected + $_CB_database->getAffectedRows();

	$sql = "SELECT `id` FROM `#__comprofiler_plugin` WHERE `element` = 'cbblogs' ORDER BY `id` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$fieldid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_plugin` WHERE `element` = 'cbblogs' AND `id` <> " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_plugin` SET `id` = 19, `iscore` = 1 WHERE `id` = " . (int) $fieldid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$affected + $_CB_database->getAffectedRows();

	if ( $affected ) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf( CBTxt::T( 'Fixed %s _comprofiler_plugin entries with incorrect iscore values.' ), $affected ) . '</div>';
	}

	$sql = "SELECT `tabid` FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbarticlesTab' ORDER BY `tabid` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$tabid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbarticlesTab' AND `tabid` <> " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_tabs` SET `tabid` = 10, `pluginid` = 17, `sys` = 1 WHERE `tabid` = " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$_CB_database->getAffectedRows();

	$sql = "SELECT `tabid` FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbforumsTab' ORDER BY `tabid` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$tabid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbforumsTab' AND `tabid` <> " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_tabs` SET `tabid` = 9, `pluginid` = 18, `sys` = 1 WHERE `tabid` = " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$affected + $_CB_database->getAffectedRows();

	$sql = "SELECT `tabid` FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbblogsTab' ORDER BY `tabid` DESC LIMIT 1";
	$_CB_database->setQuery( $sql );
	$tabid = $_CB_database->loadResult();

	$sql = "DELETE FROM `#__comprofiler_tabs` WHERE `pluginclass` = 'cbblogsTab' AND `tabid` <> " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$sql = "UPDATE `#__comprofiler_tabs` SET `tabid` = 8, `pluginid` = 19, `sys` = 1 WHERE `tabid` = " . (int) $tabid;
	$_CB_database->setQuery( $sql );

	try {
		$_CB_database->query();
	}
	catch ( RuntimeException $e ) {
		print('<div class="form-group cb_form_line clearfix text-danger">' . sprintf(CBTxt::T('SQL error %s'), $e->getMessage() ) . '</div>');
		return;
	}

	$affected		=	$affected + $_CB_database->getAffectedRows();

	if ( $affected ) {
		print '<div class="form-group cb_form_line clearfix text-warning">' . sprintf( CBTxt::T( 'Fixed %s _comprofiler_tabs entries with incorrect sys values.' ), $affected ) . '</div>';
	}

	print '<div class="form-group cb_form_line clearfix text-success">' . CBTxt::T( 'Plugin migration complete! Depreciated plugins, tabs, and fields should now be removable!' ) . '</div>';
}


/**
* Cancels an edit operation
*/
function cancelPlugin( $option) {
	global $_CB_framework, $_POST;

	$row = new PluginTable();
	$row->bind( $_POST );
	$row->checkin();

	cbRedirect( $_CB_framework->backendUrl( "index.php?option=$option&view=showPlugins" ) );
}

function cancelPluginAction( $option) {
	global $_CB_framework, $_POST;

	$pluginId	=	(int) cbGetParam( $_POST, 'cid' );
	if ( $pluginId ) {
		cbRedirect( $_CB_framework->backendUrl( "index.php?option=$option&view=editPlugin&cid=$pluginId" ) );
	} else {
		cbRedirect( $_CB_framework->backendUrl( "index.php?option=$option&view=showPlugins" ) );
	}
}

function installPluginUpload() {
	global $_FILES;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option		=	"com_comprofiler";
	$task		=	"showPlugins";
	$client		=	0;
	//echo "installPluginUpload";

	$installer	=	new cbInstallerPlugin();

	// Check if file uploads are enabled
	if ( ! (bool) ini_get( 'file_uploads' ) ) {
		cbInstaller::renderInstallMessage( CBTxt::T('The installer cannot continue before file uploads are enabled. Please use the install from directory method.'),
			CBTxt::T('Installer - Error'), $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	// Check that the zlib is available
	if( ! extension_loaded( 'zlib' ) ) {
		cbInstaller::renderInstallMessage( CBTxt::T('The installer cannot continue before zlib is installed'),
			CBTxt::T('Installer - Error'), $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$userfile				=	cbGetParam( $_FILES, 'userfile', null );

	if ( ! $userfile || ( $userfile == null ) ) {
		cbInstaller::renderInstallMessage( CBTxt::T('No file selected'), CBTxt::T('Upload new plugin - error'),
			$installer->returnTo( $option, $task, $client ));
		exit();
	}

//	$userfile['tmp_name']	=	stripslashes( $userfile['tmp_name'] );
//	$userfile['name']		=	stripslashes( $userfile['name'] );

	$msg		=	'';
	$localName	=	$_FILES['userfile']['name'];
	$resultdir	=	uploadFile( $_FILES['userfile']['tmp_name'], $localName , $msg );		// $localName is updated here

	if ( $resultdir !== false ) {
		if ( ! $installer->upload( $localName ) ) {
			if ( $installer->unpackDir() ) {
				$installer->cleanupInstall( $localName, $installer->unpackDir() );
			}
			cbInstaller::renderInstallMessage( $installer->getError(), sprintf(CBTxt::T('Upload %s - Upload Failed'), $task),
				$installer->returnTo( $option, $task, $client ) );
		}
		$ret	=	$installer->install();

		$installer->cleanupInstall( $localName, $installer->unpackDir() );

		cbInstaller::renderInstallMessage( $installer->getError(), sprintf(CBTxt::T('Upload %s - '), $task) . ( $ret ? CBTxt::T('Success') : CBTxt::T('Failed') ),
			$installer->returnTo( $option, $task, $client ) );
		$installer->cleanupInstall( $localName, $installer->unpackDir() );
	} else {
		cbInstaller::renderInstallMessage( $msg, sprintf(CBTxt::T('Upload %s - Upload Error'), $task),
			$installer->returnTo( $option, $task, $client ) );
	}

}

function _cbAdmin_chmod( $filename ) {
	global $_CB_framework;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=	cbAdminFileSystem::getInstance();

	$origmask			=	null;
	if ( $_CB_framework->getCfg( 'dirperms' ) == '' ) {
		// rely on umask
		// $mode			=	0777;
		return true;
	} else {
		$origmask		=	@umask( 0 );
		$mode			=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
	}

	$ret				=	$adminFS->chmod( $filename, $mode );

	if ( isset( $origmask ) ) {
		@umask( $origmask );
	}
	return $ret;
}

function uploadFile( $filename, &$userfile_name, &$msg ) {
	global $_CB_framework;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=	cbAdminFileSystem::getInstance();

	$baseDir			=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	$userfile_name		=	$baseDir . $userfile_name;		// WARNING: this parameter is returned !

	if ( $adminFS->file_exists( $baseDir ) ) {
		if ( $adminFS->is_writable( $baseDir ) ) {
			if ( move_uploaded_file( $filename, $userfile_name ) ) {
//			    if ( _cbAdmin_chmod( $userfile_name ) ) {
			        return true;
//				} else {
//					$msg = CBTxt::T('Failed to change the permissions of the uploaded file.');
//				}
			} else {
				$msg = sprintf( CBTxt::T('Failed to move uploaded file to %s directory.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
			}
		} else {
		    $msg = sprintf( CBTxt::T('Upload failed as %s directory is not writable.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
	} else {
	    $msg = sprintf( CBTxt::T('Upload failed as %s directory does not exist.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
	}
	return false;
}

function installPluginDir() {
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option="com_comprofiler";
	$task="showPlugins";
	$client=0;
	// echo "installPluginDir";

	$installer = new cbInstallerPlugin();

	$userfile = cbGetParam( $_REQUEST, 'userfile', null );

	// Check if file name exists
	if (!$userfile) {
		cbInstaller::renderInstallMessage( CBTxt::T('No file selected'), CBTxt::T('Install new plugin from directory - error'),
			$installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$path = _cbPathName( $userfile );
	if (!is_dir( $path )) {
		$path = dirname( $path );
	}

	$ret = $installer->install( $path);

	cbInstaller::renderInstallMessage( $installer->getError(), sprintf( CBTxt::T('Install new plugin from directory %s'), $userfile ) . ' - ' . ( $ret ? CBTxt::T('Success') : CBTxt::T('Failed') ),
		$installer->returnTo( $option, $task, $client ) );
}


function installPluginURL() {
	global $_CB_framework;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option="com_comprofiler";
	$task="showPlugins";
	$client=0;
	// echo "installPluginURL";

	$installer = new cbInstallerPlugin();

	// Check that the zlib is available
	if(!extension_loaded('zlib')) {
		cbInstaller::renderInstallMessage( CBTxt::T('The installer cannot continue before zlib is installed'),
			CBTxt::T('Installer - Error'), $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$userfileURL = cbGetParam( $_REQUEST, 'userfile', null );

	if (!$userfileURL) {
		cbInstaller::renderInstallMessage( CBTxt::T('No URL selected'), CBTxt::T('Upload new plugin - error'),
			$installer->returnTo( $option, $task, $client ));
		exit();
	}


	cbimport( 'cb.adminfilesystem' );
	$adminFS			=	cbAdminFileSystem::getInstance();

	if ( $adminFS->isUsingStandardPHP() ) {
		$baseDir		=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	} else {
		$baseDir		=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp/';
	}
	$userfileName		=	$baseDir . 'comprofiler_temp.zip';


	$msg			=	'';
	//echo "step-uploadfile<br />";
	$resultdir		=	uploadFileURL( $userfileURL, $userfileName, $msg );

	if ($resultdir !== false) {
		//echo "step-upload<br />";
		if (!$installer->upload( $userfileName )) {
			cbInstaller::renderInstallMessage( $installer->getError(), sprintf(CBTxt::T('Download %s - Upload Failed'), $userfileURL),
				$installer->returnTo( $option, $task, $client ) );
		}
		//echo "step-install<br />";
		$ret = $installer->install();

		if ( $ret ) {
			cbInstaller::renderInstallMessage( $installer->getError(), sprintf( CBTxt::T('Download %s'), $userfileURL ) . ' - ' . ( $ret ? CBTxt::T('Success') : CBTxt::T('Failed') ),
													$installer->returnTo( $option, $task, $client ) );
		}
		$installer->cleanupInstall( $userfileName, $installer->unpackDir() );
	} else {
		cbInstaller::renderInstallMessage( $msg, sprintf(CBTxt::T('Download %s - Download Error'), $userfileURL),
												$installer->returnTo( $option, $task, $client ) );
	}

}

function installPluginDisc() {
	global $_CB_framework;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm( 'showPlugins' );

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option		=	'com_comprofiler';
	$task		=	'showPlugins';
	$client		=	0;

	$installer	=	new cbInstallerPlugin();

	$plgFile	=	cbGetParam( $_REQUEST, 'plgfile', null );

	// Check if file xml exists
	if ( ! $plgFile ) {
		cbInstaller::renderInstallMessage( CBTxt::T( 'No file selected' ), CBTxt::T( 'Install new plugin from discovery - error' ), $installer->returnTo( $option, $task, $client ) );
		return;
	}

	$path		=	_cbPathName( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/' . $plgFile );

	if ( ! is_dir( $path ) ) {
		$path	=	dirname( $path );
	}

	if ( ! is_dir( $path ) ) {
		cbInstaller::renderInstallMessage( CBTxt::T( 'FILE_DOES_NOT_EXIST_FILE', 'File does not exist - [file]', array( '[file]' => $path ) ), CBTxt::T( 'Install new plugin from discovery - error' ), $installer->returnTo( $option, $task, $client ) );
		return;
	}

	$ret		=	$installer->install( $path, true );

	cbInstaller::renderInstallMessage( $installer->getError(), CBTxt::T( 'INSTALL_NEW_PLUGIN_FROM_DISCOVERY_FILE_STATUS', 'Install new plugin from discovery - [file] - [status]', array( '[file]' => $path, '[status]' => ( $ret ? CBTxt::T( 'Success' ) : CBTxt::T( 'Failed' ) ) ) ), $installer->returnTo( $option, $task, $client ) );
}

function uploadFileURL( $userfileURL, $userfile_name, &$msg ) {
	global $_CB_framework;

	cbimport( 'cb.snoopy' );
	cbimport( 'cb.adminfilesystem' );
	$adminFS					=	cbAdminFileSystem::getInstance();

	if ( $adminFS->isUsingStandardPHP() ) {
		$baseDir				=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	} else {
		$baseDir				=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp';
	}

	if ( file_exists( $baseDir ) ) {
		if ( $adminFS->is_writable( $baseDir ) || ! $adminFS->isUsingStandardPHP() ) {

			$s					=	new CBSnoopy();
			$fetchResult		=	@$s->fetch( $userfileURL );

			if ( $fetchResult && ! $s->error && ( $s->status == 200 ) ) {
				cbimport( 'cb.adminfilesystem' );
				$adminFS		=	cbAdminFileSystem::getInstance();
				if ( $adminFS->file_put_contents( $baseDir . $userfile_name, $s->results ) ) {
					if ( _cbAdmin_chmod( $baseDir . $userfile_name ) ) {
						return true;
					} else {
						$msg = sprintf(CBTxt::T('Failed to change the permissions of the uploaded file %s'), $baseDir.$userfile_name);
					}
				} else {
					$msg = sprintf(CBTxt::T('Failed to create and write uploaded file in %s'), $baseDir.$userfile_name);
				}
			} else {
				$msg = ( $s->error ? sprintf( CBTxt::T('Failed to download package file from <code>%s</code> to webserver due to following error: %s'),  $userfileURL, $s->error ) :
					   				 sprintf( CBTxt::T('Failed to download package file from <code>%s</code> to webserver due to following status: %s'), $userfileURL, $s->status . ': ' . $s->response_code ) );
			}
		} else {
		    $msg = sprintf( CBTxt::T('Upload failed as %s directory is not writable.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
	} else {
	    $msg = sprintf( CBTxt::T('Upload failed as %s directory does not exist.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
	}
	return false;
}


// Ajax: administrator/index.php?option=com_comprofiler&task=latestVersion :
function latestVersion(){
	global $_CB_framework, $ueConfig;

	cbimport( 'cb.snoopy' );

	$s = new CBSnoopy();
	$s->read_timeout = 90;
	$s->referer = $_CB_framework->getCfg( 'live_site' );
	@$s->fetch('http://www.joomlapolis.com/versions/comprofilerversion.php?currentversion='.urlencode($ueConfig['version']));
	$version_info = $s->results;
	$version_info_pos = strpos($version_info, ":");
	if ($version_info_pos === false) {
		$version = $version_info;
		$info = null;
	} else {
		$version = substr( $version_info, 0, $version_info_pos );
		$info = substr( $version_info, $version_info_pos + 1 );
	}
	if($s->error || $s->status != 200){
    	echo '<span class="text-danger">' . CBTxt::T('Connection to update server failed') . ': ' . CBTxt::T('ERROR') . ': ' . $s->error . ($s->status == -100 ? CBTxt::T('Timeout') : $s->status).'</span>';
    } else if($version == $ueConfig['version']){
    	echo '<span class="text-success">' . $version . '</span>' . $info;
    } else {
    	echo '<span class="text-danger">' . $version . '</span>' . $info;
    }
}

// NB for now duplicated in frontend and admin backend:
function tabClass( /** @noinspection PhpUnusedParameterInspection */ $option, $task, $uid ) {
	global $_PLUGINS, $_REQUEST, $_POST;

	if ( $uid ) {
		$cbUser				=&	CBuser::getInstance( (int) $uid );
		if ( $cbUser ) {
			$user			=&	$cbUser->getUserData();
		} else {
			$cbUser			=&	CBuser::getInstance( null );
			$user			=	null;
		}
	} else {
		$cbUser				=&	CBuser::getInstance( null );
		$user				=	null;
	}

	$unsecureChars			=	array( '/', '\\', ':', ';', '{', '}', '(', ')', "\"", "'", '.', ',', "\0", ' ', "\t", "\n", "\r", "\x0B" );
	if ( $task == 'fieldclass' ) {
		if ( $user && $user->id ) {
			$uid			=	$user->id;
		} else {
			$uid			=	0;
		}

		$msg				=	checkCBpermissions( array($uid), "edit", true );
		$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( $uid, &$msg, 2 ) );
		if ( $msg ) {
			echo $msg;
			return;
		}

		$fieldName			=	trim( substr( str_replace( $unsecureChars, '', urldecode( stripslashes( cbGetParam( $_REQUEST, "field" ) ) ) ), 0, 50 ) );
		if ( ! $fieldName ) {
			echo CBTxt::T('no field');
			return;
		}

		$pluginName			=	null;
		$tabClassName		=	null;
		$method				=	null;
	}
	elseif ( $task == 'tabclass' ) {
		$tabClassName		=	urldecode( stripslashes( cbGetParam( $_REQUEST, "tab" ) ) );
		if ( ! $tabClassName ) {
			return;
		}
		$pluginName			=	null;
		$tabClassName		=	substr( str_replace( $unsecureChars, '', $tabClassName ), 0, 32 );
		$method				=	'getTabComponent';

		$fieldName			=	null;
	}
	elseif ( $task == 'pluginclass' ) {
		$pluginName			=	urldecode( stripslashes( cbGetParam( $_REQUEST, "plugin" ) ) );
		if ( ! $pluginName ) {
			return;
		}
		$tabClassName		=	'CBplug_' . strtolower( substr( str_replace( $unsecureChars, '', $pluginName ), 0, 32 ) );
		$method				=	'getCBpluginComponent';

		$fieldName			=	null;
	}
	else {
		throw new LogicException( 'Unexpected task for CB tabClass' );
	}

	$tabs					=	$cbUser->_getCbTabs( false );
	if ( $task == 'fieldclass' ) {
		$result				=	$tabs->fieldCall( $fieldName, $user, $_POST, 'edit' );
	} else {
		$result				=	$tabs->tabClassPluginTabs( $user, $_POST, $pluginName, $tabClassName, $method );
	}
	if ( $result === false ) {
	 	if( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"" . $_PLUGINS->getErrorMSG() . "\"); </script>\n";
	 	}
	} elseif ( $result !== null ) {
		echo $result;
	}
}

function finishInstallation( $option ) {
	global $_CB_framework, $ueConfig, $task;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	_CBsecureAboveForm( 'finishInstallation' );

	$tgzFile				=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/pluginsfiles.tgz';
	$installerFile			=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/';

	if ( file_exists( $installerFile . 'comprofiler.xml' ) ) {
		$installerFile		.=	'comprofiler.xml';
	} elseif ( file_exists( $installerFile . 'comprofilej.xml' ) ) {
		$installerFile		.=	'comprofilej.xml';
	} elseif ( file_exists( $installerFile . 'comprofileg.xml' ) ) {
		$installerFile		.=	'comprofileg.xml';
	}

	if ( ! file_exists( $tgzFile ) ) {
		echo CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
		return;
	}

	$installer				=	new cbInstallerPlugin();
	$client					=	2;

	// Check that the zlib is available
	if ( ! extension_loaded( 'zlib' ) ) {
		cbInstaller::renderInstallMessage( CBTxt::T( 'The installer cannot continue before zlib is installed' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, $task, $client ) );
		return;
	}

	if ( ! $installer->upload( $tgzFile, true, false ) ) {
		cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Uncompressing %s failed.' ), $tgzFile ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
		return;
	}

	$adminFS				=	cbAdminFileSystem::getInstance();
	$installFrom			=	$installer->installDir();
	$filesList				=	cbReadDirectory( $installFrom, '.', true );

	// check if core directories exist as are needed to install plugins:
	$baseDir				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler';

	if ( ! $adminFS->is_dir( $baseDir . '/plugin' ) ) {
		if ( ! $adminFS->mkdir( $baseDir . '/plugin' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create directory "%s"' ), $baseDir . '/plugin' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}

		if ( ! $adminFS->copy( $baseDir . '/index.html', $baseDir . '/plugin/index.html' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create index "%s"' ), $baseDir . '/plugin/index.html' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}
	}

	if ( ! $adminFS->is_dir( $baseDir . '/plugin/language' ) ) {
		if ( ! $adminFS->mkdir( $baseDir . '/plugin/language' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create directory "%s"' ), $baseDir . '/plugin/language' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}

		if ( ! $adminFS->copy( $baseDir . '/index.html', $baseDir . '/plugin/language/index.html' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create index "%s"' ), $baseDir . '/plugin/language/index.html' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}
	}

	if ( ! $adminFS->is_dir( $baseDir . '/plugin/templates' ) ) {
		if ( ! $adminFS->mkdir( $baseDir . '/plugin/templates' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create directory "%s"' ), $baseDir . '/plugin/templates' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}

		if ( ! $adminFS->copy( $baseDir . '/index.html', $baseDir . '/plugin/templates/index.html' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create index "%s"' ), $baseDir . '/plugin/templates/index.html' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}
	}

	if ( ! $adminFS->is_dir( $baseDir . '/plugin/user' ) ) {
		if ( ! $adminFS->mkdir( $baseDir . '/plugin/user' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create directory "%s"' ), $baseDir . '/plugin/user' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}

		if ( ! $adminFS->copy( $baseDir . '/index.html', $baseDir . '/plugin/user/index.html' ) ) {
			cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Failed to create index "%s"' ), $baseDir . '/plugin/user/index.html' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
			return;
		}
	}

	// install core plugins:
	foreach ( $filesList as $file ) {
		if ( preg_match( '/^.+\.xml$/i', $file ) ) {
			$plgPath			=	$installFrom . ( substr( $installFrom, -1, 1 ) == '/' ? '' : '/' ) . $file;
			$plgXml				=	new \CBLib\Xml\SimpleXMLElement( trim( file_get_contents( $plgPath ) ) );

			if ( $plgXml->getName() == 'cbinstall' ) {
				$plgDir			=	dirname( $plgPath ) . '/';
				$plgInstaller	=	new cbInstallerPlugin();

				if ( ! $plgInstaller->install( $plgDir ) ) {
					cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Installing plugin failed with error: %s : %s' ), ( $plgInstaller->i_elementname ? $plgInstaller->i_elementname : $file ), $plgInstaller->getError() ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
					return;
				}
			}
		}
	}

	$result					=	$adminFS->deldir( _cbPathName( $installFrom . '/' ) );

	if ( $result === false ) {
		cbInstaller::renderInstallMessage( CBTxt::T( 'Deleting expanded tgz file directory failed with an error.' ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
	}

	$tgzFileOS				=	_cbPathName( $tgzFile, false );
	$result					=	$adminFS->unlink( $tgzFileOS );

	if ( $result === false ) {
		cbInstaller::renderInstallMessage( sprintf( CBTxt::T( 'Deleting file %s failed with an error.' ), $tgzFileOS ), CBTxt::T( 'Installer - Error' ), $installer->returnTo( $option, '', 2 ) );
	}

	// adapt published fields to global CB config (regarding name type)
	_cbAdaptNameFieldsPublished( $ueConfig );

	$htmlToDisplay			=	$_CB_framework->getUserState( 'com_comprofiler_install' );

	// clears the session buffer memory after installaion done:
	$_CB_framework->setUserState( 'com_comprofiler_install', '' );

	$installerXml			=	new SimpleXMLElement( file_get_contents( $installerFile ) );

	if ( is_object( $installerXml ) ) {
		$description		=	$installerXml->getElementByPath( 'description' );

		if ( $description !== false ) {
			echo '<h2>' . $description->data() . '</h2>';
		}
	}

	echo $htmlToDisplay;

	echo '<div style="color:green;font-size:18px;font-weight:bold;margin-top:15px;margin-bottom:15px;">' . CBTxt::Th( 'Installation done.' ) . '</div>'
		.	'<div style="color:green;font-size:18px;font-weight:bold;margin-top:15px;margin-bottom:15px;">' . CBTxt::Th( 'Now is a great time to checkout the <a href="[help_url]" target="_blank">Getting Started</a> resources.', null, array( '[help_url]' => 'http://www.joomlapolis.com/documentation/community-builder/getting-started?pk_campaign=in-cb&amp;pk_kwd=installedwelcomescreen' ) ) . '</div>'
		.	'<div style="margin-bottom:10px;">'
		.		'<div style="font-size:12px;"><a href="http://www.joomlapolis.com/cb-solutions?pk_campaign=in-cb&amp;pk_kwd=installedwelcomescreen" target="_blank">' . CBTxt::Th( 'Click here to see more CB Plugins (Languages, Fields, Tabs, Signup-Connect, Paid Memberships and over 30 more) by CB Team at joomlapolis.com' ) . '</a></div>'
		.		'<div style="font-size:12px;"><a href="http://extensions.joomla.org/extensions/clients-a-communities/communities/210" target="_blank">' . CBTxt::Th( 'Click here to see our CB listing on the Joomla! Extensions Directory (JED) and find third-party add-ons for your website.' ) . '</a></div>'
		.		'<div style="font-size:12px;margin:10px 0 25px;">or &nbsp; <a href="index.php?option=com_comprofiler&view=showconfig" class="btn btn-primary">' . CBTxt::Th( 'Start to Configure Community Builder' ) . '</a></div>'
		.	'</div>';

	$_CB_framework->setUserState( "com_comprofiler_install", '' );
}
