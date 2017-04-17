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

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$memMax			=	trim( @ini_get( 'memory_limit' ) );
if ( $memMax ) {
	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
	switch( $last ) {
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'g':
			$memMax	*=	1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'm':
			$memMax	*=	1024;
		case 'k':
			$memMax	*=	1024;
	}
	if ( $memMax < 16000000 ) {
		@ini_set( 'memory_limit', '16M' );
	}
	if ( $memMax < 32000000 ) {
		@ini_set( 'memory_limit', '32M' );
	}
	if ( $memMax < 48000000 ) {
		@ini_set( 'memory_limit', '48M' );
	}
	if ( $memMax < 64000000 ) {
		@ini_set( 'memory_limit', '64M' );
	}
	if ( $memMax < 80000000 ) {
		@ini_set( 'memory_limit', '80M' );
	}
}
/**
 * CB framework
 * @global CBframework
 */
global $_CB_framework;

/**
 * @global string
 */
global $_CB_adminpath;

$_CB_adminpath		=	JPATH_ADMINISTRATOR . '/components/com_comprofiler';
/** @noinspection PhpIncludeInspection */
include_once $_CB_adminpath . '/plugin.foundation.php';

if($_CB_framework->getCfg( 'debug' )) {
	ini_set( 'display_errors', true );
	error_reporting( E_ALL );	// | E_STRICT );
}

cbimport( 'language.all' );

cbimport( 'cb.tabs' );

if ( ! Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler' ) ) {
	cbRedirect( $_CB_framework->backendUrl( 'index.php' ), CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ), 'error' );
}

// We don't use view so lets map it to task before we grab task:
cbMapViewToTask();

/** Backend menu: 'show' : only displays close button, 'edit' : special close button
 *  @global stdClass $_CB_Backend_Menu */
global $_CB_Backend_Menu;
$_CB_Backend_Menu	=	new stdClass();

$option				=	$_CB_framework->getRequestVar( 'option' );
$task				=	$_CB_framework->getRequestVar( 'view' );
$cid				=	cbGetParam( $_REQUEST, 'cid', array( 0 ) );
if ( ! is_array( $cid )) {
	$cid			=	array ( (int) $cid );
}

global $_CB_Backend_Title, $_CB_Backend_task;
$_CB_Backend_Title	=	array();
$_CB_Backend_task	=	$task;

$oldignoreuserabort	=	ignore_user_abort( true );

$taskPart1			=	strtok( $task, '.' );

$_CB_framework->document->outputToHeadCollectionStart();
ob_start();

// remind step 2 if forgotten/failed:
$tgzFile			=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/pluginsfiles.tgz';
if ( file_exists( $tgzFile ) ) {
	if ( in_array( $taskPart1, array( 'showusers', 'showconfig', 'showTab', 'showField', 'showLists', 'tools', 'showPlugins', '' ) ) ) {
		echo '<div class="alert alert-danger"> ' . sprintf( CBTxt::Th('Warning: file %s still exists. This is probably due to the fact that first installation step did not complete, or second installation step did not take place. If you are sure that first step has been performed, you need to execute second installation step before using CB. You can do this now by clicking here:') , $tgzFile )
		. ' <a href="' . $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=finishinstallation' ) . '">' . CBTxt::Th('please click here to continue next and last installation step') . '</a>.</div>';
	}
}

function _CBloadController( $name ) {
	global $_CB_framework, $ueConfig;

	/** @noinspection PhpIncludeInspection */
	require_once $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/controller/controller.' . $name . '.php';
	$controllerClass		=	'CBController_' . $name;
	return new $controllerClass( $ueConfig );
}
function _CBloadView( $name ) {
	global $_CB_framework, $ueConfig;

	/** @noinspection PhpIncludeInspection */
	require_once $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/view/view.' . $name . '.php';
	$viewClass				=	'CBView_' . $name;
	return new $viewClass( $ueConfig );
}

function _CBsecureAboveForm( ) {
	global $_CB_framework;
	ob_start();
?>
if(self!=top) {
	parent.document.body.innerHTML='Iframes not allowed, could be hack attempt..., sorry!';
	self.top.location=self.location;
}
<?php
	$js		=	 ob_get_contents();
	ob_end_clean();
	$_CB_framework->document->addHeadScriptDeclaration( $js );
	return null;
}

global $_PLUGINS;

$pluginId					=	null;

switch ( $taskPart1 ) {
	case 'newPlugin':
	case 'editPlugin':
	case 'savePlugin':
	case 'applyPlugin':
		if ( $cid ) {
			$pluginId		=	$cid[0];
		}

		if ( ! $pluginId ) {
			$pluginId		=	cbGetParam( $_REQUEST, 'cid', 0 );
		}

		if ( ! $pluginId ) {
			$pluginId		=	cbGetParam( $_REQUEST, 'pluginid', 0 );
		}
		break;
	case 'pluginmenu':
		$pluginId			=	cbGetParam( $_REQUEST, 'pluginid', 0 );

		if ( $cid ) {
			$pluginId		=	$cid[0];
		}

		if ( ! $pluginId ) {
			$pluginId		=	cbGetParam( $_REQUEST, 'cid', 0 );
		}
		break;
}

if ( $pluginId ) {
	$savedPluginId			=	$_PLUGINS->_loading;

	$_PLUGINS->loadPluginGroup( 'user', array( (int) $pluginId ), false );

	$_PLUGINS->_loading		=	(int) $pluginId;

	$pluginObject			=	$_PLUGINS->getCachedPluginObject( $pluginId );
	if ( $pluginObject ) {
		$pluginClass		=	'get' . $pluginObject->element . 'Tab';
		if ( $pluginClass == 'getcbpaidsubscriptionsTab' && is_callable( $pluginClass, 'settingsParams' ) ) {
			/** @noinspection PhpUndefinedMethodInspection */
			$pluginParams	=	$pluginClass::settingsParams();
			$pluginObject->params	=	$pluginParams;

			$_CB_framework->document->addHeadStyleSheet( '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/templates/default/cbpaidsubscriptions.admin.css' );
		}
	}
} else {
	$savedPluginId			=	null;
}

// Then try to automagically use CBLib:
try {
	echo CBLib\Core\CBLib::execute();
} catch ( \Exception $e ) {
	echo $e->getMessage();
	if ( $_CB_framework->getCfg( 'debug' ) ) {
		echo "\n<br >\n";
		echo $e->getTraceAsString();
	}
	exit();
}

if ( $pluginId ) {
	$_PLUGINS->_loading		=	$savedPluginId;
}

switch ( $taskPart1 ) {
	case "emailusers":
	case "startemailusers":
	case "ajaxemailusers":
	case "resendconfirmationemails":
		// Try to grab the IDs from XML POST to ensure legacy usage still functions:
		if ( ! $cid ) {
			$xmlPost		=	cbGetParam( $_POST, 'usersbrowser', null );

			if ( $xmlPost ) {
				$cid		=	cbGetParam( $xmlPost, 'idcid', array( 0 ) );

				if ( ! is_array( $cid ) ) {
					$cid	=	array ( (int) $cid );
				}
			}
		}

		checkCanAdminPlugins( array( 'core.admin', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete' ), null, 'com_users' );
		$cbController	=	_CBloadController( 'users' );
		/** @var CBController_users $cbController */
		$cbController->showUsers( $option, $task, $cid );
		break;
	case "new":
		checkCanAdminPlugins( 'core.create', null, 'com_users' );
		$cbController	=	_CBloadController( 'user' );
		/** @var CBController_user $cbController */
		$cbController->editUser( 0, $option );
		break;

	case "edit":
		// Try to grab the IDs from XML POST to ensure legacy usage still functions:
		if ( ! $cid ) {
			$xmlPost		=	cbGetParam( $_POST, 'usersbrowser', null );

			if ( $xmlPost ) {
				$cid		=	cbGetParam( $xmlPost, 'idcid', 0 );

				if ( ! is_array( $cid ) ) {
					$cid	=	array ( (int) $cid );
				}
			}
		}

		if ( $cid[0] == $_CB_framework->myId() ) {
			checkCanAdminPlugins( 'core.edit.own', $cid[0], 'com_users' );
		} else {
			checkCanAdminPlugins( 'core.edit', $cid[0], 'com_users' );
		}
		$cbController	=	_CBloadController( 'user' );
		/** @var CBController_user $cbController */
		$cbController->editUser( intval( $cid[0] ), $option );
		break;

	case "save":
	case "apply":
		cbSpoofCheck( 'user' );
		$userIdPosted	=	(int) cbGetParam($_POST, "id", 0 );
		if ( $userIdPosted == 0 ) {
			checkCanAdminPlugins( 'core.create', null, 'com_users' );
		} elseif ( $userIdPosted == $_CB_framework->myId() ) {
			checkCanAdminPlugins( 'core.edit.own', $userIdPosted, 'com_users' );
		} else {
			checkCanAdminPlugins( 'core.edit', $userIdPosted, 'com_users' );
		}
		$cbController	=	_CBloadController( 'user' );
	/** @var CBController_user $cbController */
		$cbController->saveUser( $option, $task );
		break;

	case 'editPlugin':
		checkCanAdminPlugins( 'core.edit', $pluginId );
		$cbController	=	_CBloadController( 'plugin' );
		/** @var CBController_plugin $cbController */
		$cbController->editPlugin( $option, $task, $pluginId );
		break;

	case 'savePlugin':
	case 'applyPlugin':
		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.edit' );
		$cbController	=	_CBloadController( 'plugin' );
	/** @var CBController_plugin $cbController */
		$cbController->savePlugin( $option, $task );
		break;

	case 'pluginmenu':
		$cbController	=	_CBloadController( 'plugin' );
		/** @var CBController_plugin $cbController */
		$cbController->pluginMenu( $option, $pluginId );
		break;

	default:
		_CBloadController( 'default' );

		break;
}

ob_start();
/** @noinspection PhpIncludeInspection */
include $_CB_adminpath . '/comprofiler.toolbar.php';
$toolbars	=	trim( ob_get_contents() );
ob_end_clean();

$_CB_framework->getAllJsPageCodes();

$html		=	ob_get_contents();
ob_end_clean();

if ( in_array( $taskPart1, array( 'fieldclass', 'tabclass', 'pluginclass' ) ) || ( cbGetParam( $_GET, 'no_html', 0 ) == 1 ) || ( cbGetParam( $_GET, 'format' ) == 'raw' ) ) {
	echo $html;
} else {
	echo $_CB_framework->document->outputToHead();
?>
<div class="cbAdminMain cb_template cb_template_<?php echo selectTemplate( 'dir' ); ?>" style="margin:0; border-width: 0; padding: 0;width: 100% ;text-align: left;">
	<div class="cbAdminMainInner" id="cbAdminMainWrapper" style="margin: 0; border-width: 0; padding: 0; float: none; width: auto;">
<?php
	if ( checkJversion() >= 2 && ( ! checkJversion( 'j3.0+' ) ) ) {
		/** @noinspection PhpDeprecationInspection */
		JSubMenuHelper::addEntry( CBTxt::T( 'Control Panel' ), 'index.php?option=com_comprofiler', ( $taskPart1 == '' ) );
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'User Management' ), 'index.php?option=com_comprofiler&view=showusers', ( $taskPart1 == 'showusers' ) );
		}
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler.tabs' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'Tab Management' ), 'index.php?option=com_comprofiler&view=showTab', ( $taskPart1 == 'showTab' ) );
		}
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler.fields' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'Field Management' ), 'index.php?option=com_comprofiler&view=showField', ( $taskPart1 == 'showField' ) );
		}
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler.lists' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'List Management' ), 'index.php?option=com_comprofiler&view=showLists', ( $taskPart1 == 'showLists' ) );
		}
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler.plugins' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'Plugin Management' ), 'index.php?option=com_comprofiler&view=showPlugins', ( $taskPart1 == 'showPlugins' ) );
		}
		if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.admin', 'com_comprofiler' ) ) {
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'Tools' ), 'index.php?option=com_comprofiler&view=tools', ( $taskPart1 == 'tools' ) );
			/** @noinspection PhpDeprecationInspection */
			JSubMenuHelper::addEntry( CBTxt::T( 'Configuration' ), 'index.php?option=com_comprofiler&view=showconfig', ( $taskPart1 == 'showconfig' ) );
		}
	}

	if ( count( $_CB_Backend_Title ) > 0 ) {
		$pageTitle				=	( isset( $_CB_Backend_Title[0][1] ) && $_CB_Backend_Title[0][1] ? $_CB_Backend_Title[0][1] : CBTxt::T( 'Community Builder' ) );

		if ( preg_match('/>([\w\s]+)</i', $pageTitle, $matches ) ) {
			$cleanTitle			=	trim( $matches[1] );
		} else {
			if ( is_callable( array( 'JFilterInput', 'clean' ) ) ) {
				$cleanTitle		=	JFilterInput::getInstance()->clean( $pageTitle );
			} else {
				$cleanTitle		=	$pageTitle;
			}
		}

		if ( isset( $_CB_Backend_Title[0][0] ) ) {
			$icon				=	$_CB_Backend_Title[0][0];

			// Map legacy core header icon to fontawesome to remove need for core header icons:
			if ( $icon == 'cbicon-48-plugins' ) {
				$icon			=	'fa fa-puzzle-piece';
			}

			$title				=	'<span class="icon-cb cb_template">'
								.		'<span class="' . $icon . '"></span>'
								.	'</span>'
								.	$cleanTitle;

			JToolbarHelper::title( $title, 'communitybuilder' );
		} else {
			JToolbarHelper::title( $cleanTitle );
		}

		$_CB_framework->setPageTitle( $_CB_framework->getCfg( 'sitename' ) . ' - ' . JText::_( 'JADMINISTRATION' ) . ' - ' . $cleanTitle );
	}

	echo '<div style="width:100%;">';
	echo $html;
	echo '</div>';
	echo '<div style="clear:both;">';
	echo '</div>';
?>
	</div>
</div>
<?php
}
if ( ! is_null( $oldignoreuserabort ) ) {
	ignore_user_abort($oldignoreuserabort);
}

// END OF MAIN.

/**
 * Checks if operation is allowed, and exits to previous page if not, as it should not be possible at all.
 *
 * @since 1.8
 *
 * @param  string     $actions    Action to perform: core.admin, core.manage, core.create, core.delete, core.edit, core.edit.state, core.edit.own, ...
 * @param  array|int  $cid        Plugin-id
 * @param  string     $assetname  OPTIONAL: asset name e.g. com_comprofiler.plugin.$pluginId
 * @return void
 */
function checkCanAdminPlugins( $actions, /** @noinspection PhpUnusedParameterInspection */ $cid = null, $assetname = 'com_comprofiler' ) {
	$allowed			=	false;

	foreach ( (array) $actions as $action ) {
		$allowed		=	Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetname );

		if ( $allowed ) {
			break;
		}
	}
	if ( ! $allowed ) {
		echo "<script type=\"text/javascript\"> alert('" . addslashes( CBTxt::T( 'Operation not allowed by the Permissions of your group(s).' ) ) . "'); window.history.go(-1); </script>\n";
		exit();
	}
}

/**
 * Cleans junk of html editors that's needed for clean translation
 *
 * @deprecated 1.2.3 and unused in 2.0 (but kept for backwards compatibility)
 *
 * @param  string $text
 * @return string
 */
function cleanEditorsTranslationJunk( $text ) {
	$matches					=	null;
	if ( preg_match( '/^<p>([^<]+)<\/p>$/i', $text, $matches ) ) {
		if ( trim( $matches[1] ) != CBTxt::T( trim( $matches[1] ) ) ) {
			$text				=	trim( $matches[1] );
		}
	}
	return $text;
}

function cbUpdateChecker() {
	global $_CB_framework, $ueConfig;

	$js				=	"function cbCheckVersion() {"
					.		"document.getElementById( 'cbLatestVersion' ).innerHTML = '" . addslashes( CBTxt::T( 'Checking latest version now...' ) ) . "';"
					.		"CBmakeHttpRequest( '" . $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=latestVersion', false, 'raw' ) . "', 'cbLatestVersion', '" . addslashes( CBTxt::T( 'There was a problem with the request.' ) ) . "', null );"
					.		"return false;"
					.	"};"
					.	"function cbInitAjax() {"
					.		"CBmakeHttpRequest( '" . $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=latestVersion', false, 'raw' ) . "', 'cbLatestVersion', '" . addslashes( CBTxt::T( 'There was a problem with the request.' ) ) . "', null );"
					.	"};";

	if ( ! ( isset( $ueConfig['noVersionCheck'] ) && $ueConfig['noVersionCheck'] == '1' ) ) {
		$js			.=	"cbAddEvent( window, 'load', cbInitAjax );";
	}

	$_CB_framework->document->addHeadScriptDeclaration( $js );

	$return			=	'<table class="table table-noborder table-responsive">'
					.		'<tbody>'
					.			'<tr>'
					.				'<td class="titleCell" style="width: 25%;">' . CBTxt::Th( 'Your version is' ) . '</td>'
					.				'<td class="fieldCell" style="width: 75%;">' . $ueConfig['version'] . '</td>'
					.			'</tr>'
					.			'<tr>'
					.				'<td class="titleCell" style="width: 25%;">' . CBTxt::Th( 'Latest version' ) . '</td>'
					.				'<td class="fieldCell" style="width: 75%;">';

	if ( isset( $ueConfig['noVersionCheck'] ) && $ueConfig['noVersionCheck'] == '1' ) {
		$return		.=					'<div id="cbLatestVersion">'
					.						'<a href="check_now" onclick="return cbCheckVersion();" style="cursor: pointer; text-decoration:underline;">' . htmlspecialchars( CBTxt::T( 'check now' ) ) . '</a>'
					.					'</div>';
	} else {
		$return		.=					'<div id="cbLatestVersion" style="color: #CCC;">...</div>';
	}

	$return			.=				'</td>'
					.			'</tr>'
					.		'</tbody>'
					.	'</table>';

	return $return;
}
?>
