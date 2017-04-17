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
use CBLib\Registry\Registry;
use CB\Database\Table\ListTable;
use CB\Database\Table\UserTable;
use CB\Database\Table\FieldTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_comprofiler {

	static function emailUser( /** @noinspection PhpUnusedParameterInspection */ $option, $rowFrom, $rowTo, $allowPublic = 0, $name = '', $email = '', $subject = '', $message = '' ) {
		global $_CB_framework, $_PLUGINS, $ueConfig;

		$beforeResults		=	$_PLUGINS->trigger( 'onBeforeEmailUserForm', array( &$rowFrom, &$rowTo, 1, &$allowPublic, &$name, &$email, &$subject, &$message ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $allowPublic && ( ! $rowFrom->id ) ) {
			$warning		=	CBTxt::T( 'IMPORTANT:<ol><li>Please be aware that emails may not be received by the intended users due to their email settings and spam filter.</li></ol>' );
		} else {
			$warning		=	CBTxt::Th( 'UE_EMAILFORMWARNING', 'IMPORTANT:<ol><li>Your email address on your profile is: <strong>%s</strong>.</li><li>Make sure that it is accurate and check your spam filter before sending, because the receiver will use it for his reply.</li><li>Please be aware that emails may not be received by the intended users due to their email settings and spam filter.</li></ol>' );
		}

		$pageTitle			=	CBTxt::T( 'SEND_MESSAGE_TO_NAME', 'Send message to [name]', array( '[name]' => getNameFormat( $rowTo->name, $rowTo->username, $ueConfig['name_format'] ) ) );

		if ( $pageTitle ) {
			$_CB_framework->setPageTitle( $pageTitle );
			$_CB_framework->appendPathWay( $pageTitle );
		}

		$afterResults		=	$_PLUGINS->trigger( 'onAfterEmailUserForm', array( &$rowFrom, &$rowTo, &$warning, 1, &$allowPublic, &$name, &$email, &$subject, &$message ) );

		outputCbTemplate( 1 );
		cbValidator::loadValidation();

		$pageClass			=	$_CB_framework->getMenuPageClass();

		$return				=	'<div class="cbEmailUser cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( $rowFrom->id == $rowTo->id ) {
			$return			.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_NOSELFEMAIL', 'You are not allowed to send an email to yourself!' ) . '</h3></div>';
		} else {
			$salt			=	cbMakeRandomString( 16 );
			$key			=	'cbmv1_' . md5( $salt . $rowTo->id . $rowTo->password . $rowTo->lastvisitDate . $rowFrom->password . $rowFrom->lastvisitDate ) . '_' . $salt;

			$toUser			=	CBuser::getInstance( (int) $rowTo->id );

			$return			.=		( CBTxt::Th( 'UE_EMAILFORMTITLE', 'Send a message via email to %s' ) ? '<div class="page-header"><h3>' . sprintf( CBTxt::Th( 'UE_EMAILFORMTITLE', 'Send a message via email to %s' ), $toUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) ) . '</h3></div>' : null )
							.		'<form action="' . $_CB_framework->viewUrl( 'senduseremail' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">';

			if ( is_array( $beforeResults ) ) {
				$return		.=			implode( '', $beforeResults );
			}

			if ( $allowPublic && ( ! $rowFrom->id ) ) {
				$return		.=			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="emailName" class="col-sm-3 control-label">' . CBTxt::T( 'Name' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<input type="text" name="emailName" id="emailName" class="form-control required" size="50" maxlength="255" value="' . htmlspecialchars( $name ) . '" />'
							.					getFieldIcons( 1, 1, null )
							.				'</div>'
							.			'</div>'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="emailAddress" class="col-sm-3 control-label">' . CBTxt::T( 'Email Address' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<input type="text" name="emailAddress" id="emailAddress" class="form-control required" size="50" maxlength="255" value="' . htmlspecialchars( $email ) . '" />'
							.					getFieldIcons( 1, 1, null )
							.				'</div>'
							.			'</div>';
			}

			$return			.=			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="emailSubject" class="col-sm-3 control-label">' . CBTxt::T( 'Subject' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<input type="text" name="emailSubject" id="emailSubject" class="form-control required" size="50" maxlength="255" value="' . htmlspecialchars( $subject ) . '" />'
							.					getFieldIcons( 1, 1, null )
							.				'</div>'
							.			'</div>'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="checkemail" class="col-sm-3 control-label">' . CBTxt::T( 'Message' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<textarea name="emailBody" id="emailBody" class="form-control required" cols="50" rows="15">' . htmlspecialchars( $message ) . '</textarea>'
							.					getFieldIcons( 1, 1, null )
							.				'</div>'
							.			'</div>';

			if ( is_array( $afterResults ) ) {
				$return		.=			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					implode( '', $afterResults )
							.				'</div>'
							.			'</div>';
			}

			$return			.=			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					sprintf( $warning, $rowFrom->email )
							.				'</div>'
							.			'</div>'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					'<input type="submit" class="btn btn-primary cbEmailUserSubmit" value="' . htmlspecialchars( CBTxt::T( 'UE_SENDEMAIL', 'Send Email' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
							.					' <input type="button" class="btn btn-default cbEmailUserCancel" value="' . htmlspecialchars( CBTxt::T( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl( (int) $rowTo->id ) . '\'; return false;" />'
							.				'</div>'
							.			'</div>'
							.			'<input type="hidden" name="fromID" value="' . (int) $rowFrom->id . '" />'
							.			'<input type="hidden" name="toID" value="' . (int) $rowTo->id . '" />'
							.			'<input type="hidden" name="protect" value="' . $key . '" />'
							.			cbGetSpoofInputTag( 'emailuser' )
							.			cbGetAntiSpamInputTag( null, null, $allowPublic )
							.		'</form>'
							.	'</div>';
		}

		echo $return;

		$_CB_framework->setMenuMeta();
	}

/******************************
Profile Functions
******************************/

	static function userEdit( $user, /** @noinspection PhpUnusedParameterInspection */ $option, $submitvalue, $regErrorMSG = null ) {
		global $_CB_framework, $ueConfig, $_REQUEST, $_PLUGINS;

		$results					=	$_PLUGINS->trigger( 'onBeforeUserProfileEditDisplay', array( &$user, 1 ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".$_PLUGINS->getErrorMSG()."\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $regErrorMSG ) {
			$_CB_framework->enqueueMessage( $regErrorMSG, 'error' );
		}

		if ( $user->id != $_CB_framework->myId() ) {
			$_CB_framework->enqueueMessage( sprintf( CBTxt::T( 'UE_WARNING_EDIT_OTHER_USER_PROFILE', 'WARNING: This is not your profile. As a moderator, you are editing the profile of user: %s.' ), getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ) ) );
		}

		$output						=	'htmledit';
		$formatting					=	( isset( $ueConfig['use_divs'] ) && ( ! $ueConfig['use_divs'] ) ? 'table' : 'divs' );
		$layout						=	( isset( $ueConfig['profile_edit_layout'] ) ? $ueConfig['profile_edit_layout'] : 'tabbed' );
		$tabbed						=	( in_array( $layout, array( 'tabbed', 'stepped' ) ) ? true : false );

		$cbTemplate					=	HTML_comprofiler::_cbTemplateLoad();

		outputCbTemplate( 1 );
		initToolTip( 1 );

		$title						=	cbSetTitlePath( $user, CBTxt::T( 'UE_EDIT_TITLE', 'Edit Your Details' ), CBTxt::T( 'UE_EDIT_OTHER_USER_TITLE', 'Edit %s\'s Details' ) );

		$tabs						=	new cbTabs( 0, 1, null, ( $tabbed ? true : false ) );

		$tabcontent					=	$tabs->getEditTabs( $user, null, $output, $formatting, 'edit', ( $tabbed ? true : false ) );

		$topIcons					=	null;
		$bottomIcons				=	null;

		if ( isset( $ueConfig['profile_edit_show_icons_explain'] ) && ( $ueConfig['profile_edit_show_icons_explain'] > 0 ) ) {
			$icons					=	getFieldIcons( 1, true, true, '', '', true );

			if ( in_array( $ueConfig['profile_edit_show_icons_explain'], array( 1, 3 ) ) ) {
				$topIcons			=	$icons;
			}

			if ( in_array( $ueConfig['profile_edit_show_icons_explain'], array( 2, 3 ) ) ) {
				$bottomIcons		=	$icons;
			}
		}

		$js							=	"$( '#cbbtncancel' ).click( function() {"
									.		"window.location = '" . addslashes( $_CB_framework->userProfileUrl( $user->id, false, null, 'html', 0, array( 'reason' => 'canceledit' ) ) ) . "';"
									.	"});";

		$_CB_framework->outputCbJQuery( $js );
		cbValidator::loadValidation();

		$pageClass					=	$_CB_framework->getMenuPageClass();

		$return						=	'<div class="cbEditProfile ' . ( $tabbed ? 'cbEditProfileTabbed' : 'cbEditProfileFlat' ) . ' cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( is_array( $results ) ) {
			$return					.=		implode( '', $results );
		}

		$return						.=		( $title ? '<div class="page-header"><h3>' . $title . '</h3></div>' : null )
									.		'<form action="' . $_CB_framework->viewUrl( 'saveuseredit' ) . '" method="post" id="cbcheckedadminForm" name="adminForm" enctype="multipart/form-data" class="cb_form form-auto cbValidation">'
									.			'<input type="hidden" name="id" value="' . (int) $user->id . '" />'
									.			cbGetSpoofInputTag( 'userEdit' )
									.			$_PLUGINS->callTemplate( $cbTemplate, 'Profile', 'drawEditProfile', array( &$user, $tabcontent, $submitvalue, CBTxt::T( 'UE_CANCEL', 'Cancel' ), $bottomIcons, $topIcons ), $output )
									.		'</form>'
									.	'</div>'
									.	cbPoweredBy();

		echo $return;

		$_PLUGINS->trigger( 'onAfterUserProfileEditDisplay', array( $user, $tabcontent ) );

		$_CB_framework->setMenuMeta();
	}
	
	static function userProfile( $user, /** @noinspection PhpUnusedParameterInspection */ $option, /** @noinspection PhpUnusedParameterInspection */ $submitvalue ) {
		global $_CB_framework, $ueConfig,$_POST,$_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$_PLUGINS->trigger( 'onBeforeUserProfileRequest', array( &$user, 1 ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".$_PLUGINS->getErrorMSG()."\"); window.history.go(-1); </script>\n";
			exit();
		}

		$cbTemplate					=	HTML_comprofiler::_cbTemplateLoad();

		$cbMyIsModerator			=	Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) );
		$cbUserIsModerator			=	Application::User( (int) $user->id )->isGlobalModerator();

		$showProfile				=	1;

		if ( ( $user->banned != 0 ) || ( ( $user->block == 1 ) && $user->confirmed && $user->approved ) ) {
			if ( $user->banned != 0 ) {
				if ( $_CB_framework->myId() != $user->id ) {
					$_CB_framework->enqueueMessage( CBTxt::T( 'UE_USERPROFILEBANNED', 'This profile has been banned by a moderator.' ) . ( $user->bannedreason && $cbMyIsModerator ? '<p>' . nl2br( $user->bannedreason ) . '</p>' : null ), 'error' );
				} else {
					$_CB_framework->enqueueMessage( CBTxt::T( 'UE_BANNED_CHANGE_PROFILE', 'Your Profile is banned. Only you and moderators can see it.<br />Please follow the request of the moderator, then choose moderation / unban to submit a request for unbanning your profile.' ) . ( $user->bannedreason ? '<p>' . nl2br( $user->bannedreason ) . '</p>' : null ), 'error' );
				}
			}

			if ( $user->block == 1 ) {
				$_CB_framework->enqueueMessage( CBTxt::T( 'UE_USERPROFILEBLOCKED', 'This profile is no longer available.' ), 'error' );
			}

			if ( ( $_CB_framework->myId() != $user->id ) && ( $cbMyIsModerator != 1 ) ) {
				$showProfile		=	0;
			}
		}

		if ( ! $user->confirmed ) {
			$_CB_framework->enqueueMessage( CBTxt::T( 'UE_USER_NOT_CONFIRMED', 'This user has not yet confirmed his email address and account!' ), 'error' );
		}

		if ( ! $user->approved ) {
			$_CB_framework->enqueueMessage( CBTxt::T( 'UE_USER_NOT_APPROVED', 'This user has not yet been approved by a moderator!' ), 'error' );
		}

		if ( ( ( ! $user->confirmed ) || ( ! $user->approved ) ) && ( $cbMyIsModerator != 1 ) ) {
			$showProfile			=	0;
		}

		if ( $showProfile == 1 ) {
			$results				=	$_PLUGINS->trigger( 'onBeforeUserProfileDisplay', array( &$user, 1, $cbUserIsModerator, $cbMyIsModerator ) );

			if ( $_PLUGINS->is_errors() ) {
				echo "<script type=\"text/javascript\">alert(\"".$_PLUGINS->getErrorMSG()."\"); window.history.go(-1); </script>\n";
				exit();
			}

			$output					=	'html';

			$cbUser					=&	CBuser::getInstance( $user->id );

			$_CB_framework->displayedUser( (int) $user->id );

			$userViewTabs			=	$cbUser->getProfileView();

			$_CB_framework->setPageTitle( cbUnHtmlspecialchars( getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ) ) );
			$_CB_framework->appendPathWay( getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ) );

			outputCbTemplate( 1 );
			initToolTip( 1 );

			$pageClass				=	$_CB_framework->getMenuPageClass();

			$return					=	'<div class="cbProfile cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

			if ( is_array( $results ) ) {
				$return				.=		implode( '', $results );
			}

			$return					.=		$_PLUGINS->callTemplate( $cbTemplate, 'Profile', 'drawProfile', array( &$user, &$userViewTabs ), $output )
									.	'</div>'
									.	cbPoweredBy();

			echo $return;

			if ( $_CB_framework->myId() != $user->id ) {
				recordViewHit( $_CB_framework->myId(), $user->id, getenv( 'REMOTE_ADDR' ) );
			}

			$_PLUGINS->trigger( 'onAfterUserProfileDisplay', array( $user, true ) );

			$_CB_framework->setMenuMeta();
		}
	}

	/**
	 * Loads CB template rendering engine...
	 *
	 */
	static function _cbTemplateLoad() {
		global $_PLUGINS;

		static $loaded			=	array();

		$element				=	selectTemplate( 'dir' );
		$templatePhpFile		=	selectTemplate( 'absolute_path' ) . '/' . $element . '.php';
		if ( ! is_readable( $templatePhpFile ) ) {
			$element			=	'default';
		}

		if ( ! isset( $loaded[$element] ) ) {
			$_PLUGINS->loadPluginGroup( 'templates', $element );
			$loaded[$element]	=	true;
		}
		return $element;
	}
	/**
	 * Invokes CB template rendering engine...
	 *
	 * @param  mixed      $cbTemplate
	 * @param  UserTable  $user
	 * @param  string     $view
	 * @param  string     $method
	 * @param  array      $paramsArray
	 * @param  string     $output       'html'
	 * @return string
	 */
	static function _cbTemplateRender( $cbTemplate, /** @noinspection PhpUnusedParameterInspection */ $user, $view, $method, $paramsArray, $output = 'html' ) {
		global $_PLUGINS;

		$element				=	$cbTemplate;		// for now as this...
		if ( ( $output == 'html' ) || ( $output == 'htmledit' ) ) {
			return '<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">' . $_PLUGINS->callTemplate( $element, $view, $method, $paramsArray, $output ) . '</div>';
		} else {
			return $_PLUGINS->callTemplate( $element, $view, $method, $paramsArray, $output );
		}
	}



/******************************
List Functions
******************************/

	/**
	 * @param ListTable     $row
	 * @param UserTable[]   $users
	 * @param array         $columns
	 * @param FieldTable[]  $fields
	 * @param array         $input
	 * @param string|null   $search
	 * @param int           $searchmode
	 * @param cbPageNav     $pageNav
	 * @param UserTable     $myUser
	 * @param FieldTable[]  $searchableFields
	 * @param stdClass      $searchValues
	 * @param cbTabs        $tabs
	 * @param string|null   $errorMsg
	 * @param bool          $listAll
	 * @param int           $random
	 */
	static function usersList( &$row, &$users, &$columns, &$fields, &$input, $search, $searchmode, $pageNav, &$myUser, &$searchableFields, &$searchValues, &$tabs, $errorMsg, $listAll = true, $random = 0 ) {
		global $_CB_framework, $_PLUGINS, $_POST, $_GET, $_REQUEST;

		$params							=	new Registry( $row->params );

		// The Itemid for this userlist; kept for trigger B/C:
		$Itemid							=	getCBprofileItemid( null, 'userslist', '&listid=' . (int) $row->listid );

		$results						=	$_PLUGINS->trigger( 'onBeforeDisplayUsersList', array( &$row, &$users, &$columns, &$fields, &$input, $row->listid, &$search, &$Itemid, 1 ) );	// $uid = 1

		// Plugin content divided by location:
		$pluginAdditions				=	array( 'search', 'header', 'footer' );
		$pluginAdditions['search']		=	array();
		$pluginAdditions['header']		=	array();
		$pluginAdditions['footer']		=	array();

		if ( is_array( $results ) && ( count( $results ) > 0 ) ) foreach ( $results as $res ) {
			if ( is_array( $res ) ) foreach ( $res as $k => $v ) {
				$pluginAdditions[$k][]	=	$v;
			}
		}

		outputCbTemplate( 1 );
		outputCbJs();
		cbValidator::loadValidation();

		$cbTemplate						=	HTML_comprofiler::_cbTemplateLoad();

		if ( $errorMsg ) {
			$_CB_framework->enqueueMessage( $errorMsg, 'error' );
		}

		// Page title and pathway:
		$listTitleHtml					=	cbReplaceVars( $row->title, $myUser );
		$listTitleNoHtml				=	strip_tags( cbReplaceVars( $row->title, $myUser, false, false ) );
		$listDescription				=	cbReplaceVars( $row->description, $myUser );

		$_CB_framework->setPageTitle( $listTitleNoHtml );
		$_CB_framework->appendPathWay( $listTitleHtml );

		// Add row click JS:
		if ( $params->get( 'allow_profilelink', 1 ) ) {
			$allowProfileLink			=	true;
		} else {
			$allowProfileLink			=	false;
		}

		$js								=	"var cbUserURLs = [];";

		if ( is_array( $users ) && $allowProfileLink ) {
			// Ensures the jQuery array index matches the same as HTML ID index (e.g. cbU0, cbU1):
			$index						=	0;

			foreach( $users as $user ) {
				$js						.=	"cbUserURLs[$index] = '" . addslashes( $_CB_framework->userProfileUrl( (int) $user->id, false ) ) . "';";

				$index++;
			}
		}

		$js								.=	"$( '.cbUserListRow' ).click( function( e ) {"
										.		"if ( ! ( $( e.target ).is( 'a' ) || ( $( e.target ).is( 'img' ) && $( e.target ).parent().is( 'a' ) ) || $( e.target ).hasClass( 'cbClicksInside' ) || ( $( e.target ).parents( '.cbClicksInside' ).length > 0 ) || ( $( this ).attr( 'id' ) == '' ) ) ) {"
										.			"var index = $( this ).prop( 'id' ).substr( 3 );";

		if ( $allowProfileLink ) {
			$js							.=			"window.location = cbUserURLs[index];";
		}

		$js								.=			"return false;"
										.		"}"
										.	"});";

		$_CB_framework->outputCbJQuery( $js );

		// Search JS:
		$isSearching					=	( $search !== null );

		if ( $isSearching && $params->get( 'list_search_collapse', 0 ) && ( ! in_array( $searchmode, array( 1, 2 ) ) ) ) {
			$isCollapsed				=	true;
		} else {
			$isCollapsed				=	false;
		}

		if ( count( $searchableFields ) > 0 ) {
			cbUsersList::outputAdvancedSearchJs( ( $isCollapsed ? null : $search ) );
		}

		// Base form URL:
		$baseUrl						=	$_CB_framework->rawViewUrl( 'userslist', true, array( 'listid' => (int) $row->listid, 'searchmode' => 0 ), 'html', 0, '&listid=' . (int) $row->listid );

		// Searching attributes:
		$showAll						=	( $search === null );
		$criteriaTitle					=	cbReplaceVars( CBTxt::Th( 'UE_SEARCH_CRITERIA', 'Search criteria' ), $myUser );

		if ( ( $searchmode == 0 ) || ( ( $searchmode == 1 ) && count( get_object_vars( $searchValues ) ) ) || ( $searchmode == 2 ) ) {
			$resultsTitle				=	cbReplaceVars( CBTxt::Th( 'UE_SEARCH_RESULTS', 'Search results' ), $myUser );
		} else {
			$resultsTitle				=	null;
		}

		// Search content:
		$searchTabContent				=	$tabs->getSearchableContents( $searchableFields, $myUser, $searchValues, $params->get( 'list_compare_types', 0 ) );

		if ( count( $pluginAdditions['search'] ) ) {
			$searchTabContent			.=	'<div class="cbUserListSearchPlugins">'
										.		'<div>' . implode( '</div><div>', $pluginAdditions['search'] ) . '</div>'
										.	'</div>';
		}

		// User row content:
		$tableContent					=&	HTML_comprofiler::_getListTableContent( $users, $columns, $fields );

		if ( $params->get( 'list_grid_layout', 0 ) ) {
			$layout						=	'grid';
		} else {
			$layout						=	'list';
		}

		$gridHeight						=	(int) $params->get( 'list_grid_height', 200 );
		$gridWidth						=	(int) $params->get( 'list_grid_width', 200 );

		if ( $params->get( 'list_show_selector', 1 ) ) {
			$listSelector				=	true;
		} else {
			$listSelector				=	false;
		}

		$pageClass						=	$_CB_framework->getMenuPageClass();

		$return							=	'<div class="cbUsersList cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">'
										.		'<form action="' . $_CB_framework->rawViewUrl( 'userslist', true, array( 'listid' => (int) $row->listid ), 'html', 0, '&listid=' . (int) $row->listid ) . '" method="get" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
										.			'<input type="hidden" name="option" value="com_comprofiler" />'
										.			'<input type="hidden" name="view" value="userslist" />'
										.			( ! $listSelector ? '<input type="hidden" name="listid" value="' . (int) $row->listid . '" />' : null )
										.			'<input type="hidden" name="Itemid" value="' . (int) $Itemid . '" />'
										.			'<input type="hidden" name="limitstart" value="0" />'
										.			'<input type="hidden" name="searchmode" value="' . (int) $searchmode . '" />'
										.			'<input type="hidden" name="search" value="" />'
										.			( $random ? '<input type="hidden" name="rand" value="' . (int) $random . '" />' : null )
										.			cbGetSpoofInputTag( 'userslist' )
										.			$_PLUGINS->callTemplate( $cbTemplate, 'List', 'drawListHead', array( &$input, $row->listid, $pageNav->total, $showAll, $searchTabContent, $isSearching, $baseUrl, $listTitleHtml, $listDescription, $criteriaTitle, $resultsTitle, $listAll, $listSelector, $isCollapsed, $searchmode ), 'html' );

		if ( ( $searchmode == 0 ) || ( ( $searchmode == 1 ) && count( get_object_vars( $searchValues ) ) ) || ( $searchmode == 2 ) ) {
			$canPage					=	( $params->get( 'list_paging', 1 ) && ( ( $pageNav->limitstart != 0 ) || ( $pageNav->limit <= $pageNav->total ) ) );

			if ( count( $pluginAdditions['header'] ) ) {
				$return					.=			'<div class="cbUserListHeader">'
										.				'<div>' . implode( '</div><div>', $pluginAdditions['header'] ) . '</div>'
										.			'</div>';
			}

			$return						.=			$_PLUGINS->callTemplate( $cbTemplate, 'List', 'drawListBody', array( &$users, &$columns, &$tableContent, $row->listid, $allowProfileLink, $layout, $gridHeight, $gridWidth, $searchmode ), 'html' );

			if ( $canPage ) {
				$return					.=			'<div class="cbUserListPagination cbUserListPaginationBottom text-center">'
										.				$pageNav->getListLinks()
										.			'</div>';
			}

			if ( count( $pluginAdditions['footer'] ) ) {
				$return					.=			'<div class="cbUserListFooter">'
										.				'<div>' . implode( '</div><div>', $pluginAdditions['footer'] ) . '</div>'
										.			'</div>';
			}
		}

		$return							.=		'</form>'
										.	'</div>'
										.	cbPoweredBy();

		echo $return;

		$_CB_framework->setMenuMeta();
	}	// end function usersList

	static function & _getListTableContent( &$users, &$columns, &$fields ) {
		global $_PLUGINS;

		$tableContent									=	array();

		if ( is_array( $users ) && ( count( $users ) > 0 ) ) {
			foreach( $users as $userIdx => $user ) {
				$tableContent[$userIdx]					=	array();

				foreach ( $columns as $colIdx => $column ) {
					$tableContent[$userIdx][$colIdx]	=	array();

					foreach ( $column->fields as $fieldIdx => $colField ) {
						$fieldId						=	( isset( $colField['fieldid'] ) ? $colField['fieldid'] : null );

						if ( $fieldId && isset( $fields[$fieldId] ) ) {
							$field						=	$fields[$fieldId];

							$tableContent[$userIdx][$colIdx][$fieldIdx]		=	new stdClass();

							$fieldView					=&	$tableContent[$userIdx][$colIdx][$fieldIdx];
							$fieldView->name			=	$field->name;
							$fieldView->value			=	$_PLUGINS->callField( $field->type, 'getFieldRow', array( &$field, &$user, 'html', 'none', 'list', 0 ), $field );

							if ( is_string( $fieldView->value ) && ( trim( $fieldView->value ) == '' ) ) {
								$fieldView->value		=	null;
							}

							$fieldView->title			=	$_PLUGINS->callField( $field->type, 'getFieldTitle', array( &$field, &$user, 'html', 'list' ), $field );

							if ( is_string( $fieldView->title ) && ( trim( $fieldView->title ) == '' ) ) {
								$fieldView->title		=	null;
							}

							$fieldView->display			=	( isset( $colField['display'] ) ? $colField['display'] : 4 );
						}
					}
				}
			}
		}

		return $tableContent;
	}

/******************************
Registration Functions
******************************/

	static function confirmation() {
		outputCbTemplate( 1 );

		$htmlSuccess	=	CBTxt::Th( 'UE_SUBMIT_SUCCESS', 'Submission Success!' );

		$return		=	'<div class="cbRegConfirmation cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
					.		( $htmlSuccess ? '<div class="page-header"><h3>' . $htmlSuccess . '</h3></div>' : null )
					.		'<div>' . CBTxt::Th( 'UE_SUBMIT_SUCCESS_DESC', 'Your item has been successfully submitted to our administrators. It will be reviewed before being published on this site.' ) . '</div>'
					.	'</div>';

		echo $return;
	}

	static function lostPassForm( /** @noinspection PhpUnusedParameterInspection */ $option ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		$results				=	$_PLUGINS->trigger( 'onLostPassForm', array( 1 ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"" . $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		$regAntiSpamValues		=	cbGetRegAntiSpams();

		$usernameExists			=	( ( isset( $ueConfig['login_type'] ) ) && ( $ueConfig['login_type'] != 2 ) );
		$pageTitle				=	( $usernameExists ? CBTxt::Th( 'UE_LOST_USERNAME_OR_PASSWORD', 'Lost your Username or your Password ?' ) : CBTxt::T( 'UE_LOST_YOUR_PASSWORD', 'Lost your Password ?' ) );

		outputCbTemplate( 1 );
		cbValidator::loadValidation();
		initToolTip( 1 );

		$js						=	"$( '#checkusername,#checkemail' ).keyup( function() {"
								.		"$( this ).next( '.cb_result_container' ).remove();"
								.		"if ( $.trim( $( '#checkusername' ).val() ) != '' ) {"
								.			"if ( $.trim( $( '#checkemail' ).val() ) == '' ) {"
								.				"$( '.cbLostPassSend' ).prop( 'disabled', true );"
								.			"} else {"
								.				"$( '.cbLostPassSend' ).prop( 'disabled', false );"
								.			"}"
								.		"} else {"
								.			"if ( $.trim( $( '#checkemail' ).val() ) == '' ) {"
								.				"$( '.cbLostPassSend' ).prop( 'disabled', true );"
								.			"} else {"
								.				"$( '.cbLostPassSend' ).prop( 'disabled', false );"
								.			"}"
								.		"}"
								.	"});";

		if ( $usernameExists ) {
			$js					.=	"$( '#reminderUsername,#reminderPassword' ).click( function() {"
								.		"$( '#checkusername,#checkemail' ).next( '.cb_result_container' ).remove();"
								.		"$( '#checkusername,#checkemail' ).val( '' );"
								.		"$( '.cbLostPassSend' ).prop( 'disabled', true );"
								.		"$( '.cb_forgot_line,.cb_forgot_button' ).show();"
								.		"if ( $( '#reminderUsername' ).prop( 'checked' ) ) {"
								.			"if ( $( '#reminderPassword' ).prop( 'checked' ) ) {"
								.				"$( '.cbLostPassSend' ).val( '" . addslashes( CBTxt::Th( 'UE_BUTTON_SEND_USERNAME_PASS', 'Send Username/Password' ) ) . "' );"
								.				"$( '#lostusernamedesc,#lostpassdesc' ).hide();"
								.				"$( '#lostusernamepassdesc' ).show();"
								.			"} else {"
								.				"$( '.cbLostPassSend' ).val( '" . addslashes( CBTxt::Th( 'UE_BUTTON_SEND_USERNAME', 'Send Username' ) ) . "' );"
								.				"$( '#lostusernamepassdesc,#lostpassdesc' ).hide();"
								.				"$( '#lostusernamedesc' ).show();"
								.			"}"
								.			"$( '#lostpassusername' ).hide();"
								.			"$( '#lostpassemail' ).show();"
								.		"} else {"
								.			"if ( $( '#reminderPassword' ).prop( 'checked' ) ) {"
								.				"$( '.cbLostPassSend' ).val( '" . addslashes( CBTxt::Th( 'UE_BUTTON_SEND_PASS', 'Send Password' ) ) . "' );"
								.				"$( '#lostusernamepassdesc,#lostusernamedesc' ).hide();"
								.				"$( '#lostpassusername,#lostpassemail,#lostpassdesc' ).show();"
								.			"} else {"
								.				"$( '.cb_forgot_line,.cb_forgot_button,#lostusernamepassdesc,#lostusernamedesc,#lostpassdesc' ).hide();"
								.			"}"
								.		"}"
								.	"});"
								.	"$( '.cb_forgot_line,.cb_forgot_button,#lostusernamepassdesc,#lostusernamedesc,#lostpassdesc' ).hide();";
		}

		$_CB_framework->outputCbJQuery( $js );

		$pageClass				=	$_CB_framework->getMenuPageClass();

		$return					=	'<div class="cbLostPassForm cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">'
								.		( $pageTitle ? '<div class="page-header"><h3>' . $pageTitle . '</h3></div>' : null )
								.		'<form action="' . $_CB_framework->viewUrl( 'sendNewPass', true, null, 'html', ( checkCBPostIsHTTPS( true ) ? 1 : 0 ) ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">';

		if ( $usernameExists ) {
			$return				.=			'<div class="form-group cb_form_line clearfix" id="lostpassreminder">'
								.				'<label class="col-sm-3 control-label">' . CBTxt::Th( 'UE_REMINDER_NEEDED_FOR', 'Reminder needed for' ) . '</label>'
								.				'<div class="cb_field col-sm-9">'
								.					'<span class="cbSingleCntrl">'
								.						'<label for="reminderUsername" class="checkbox-inline">'
								.							'<input type="checkbox" id="reminderUsername" name="typeofloose[]" value="username" /> '
								.							CBTxt::Th( 'UE_LOST__USERNAME', 'Lost Username' )
								.						'</label>'
								.					'</span>'
								.					'<span class="cbSingleCntrl">'
								.						'<label for="reminderPassword" class="checkbox-inline">'
								.							'<input type="checkbox" id="reminderPassword" name="typeofloose[]" value="password" /> '
								.							CBTxt::Th( 'UE_LOST__PASSWORD', 'Lost Password' )
								.						'</label>'
								.					'</span>'
								.				'</div>'
								.			'</div>'
								.			'<div class="form-group cb_form_line clearfix" id="lostusernamedesc">'
								.				'<div class="cb_field col-sm-offset-3 col-sm-9">'
								.					CBTxt::Th( 'UE_LOST_USERNAME_ONLY_DESC', 'If you <strong>lost your username</strong>, please enter your E-mail Address, then click the Send Username button, and your username will be sent to your email address.' )
								.				'</div>'
								.			'</div>'
								.			'<div class="form-group cb_form_line clearfix" id="lostusernamepassdesc">'
								.				'<div class="cb_field col-sm-offset-3 col-sm-9">'
								.					CBTxt::Th( 'UE_LOST_USERNAME_PASSWORD_DESC', 'If you <strong>forgot both your username and your password</strong>, please recover the username first, then the password. To recover your username, please enter your E-mail Address, leaving Username field empty, then click the Send Username button, and your username will be sent to your email address. From there you can use this same form to recover your password.' )
								.				'</div>'
								.			'</div>';
		}

		$return					.=			'<div class="form-group cb_form_line clearfix" id="lostpassdesc">'
								.				'<div class="cb_field col-sm-offset-3 col-sm-9">';

		if ( $usernameExists ) {
			$return				.=					CBTxt::Th( 'UE_LOST_PASSWORD_DESC', 'If you <strong>lost your password</strong> but know your username, please enter your Username and your E-mail Address, press the Send Password button, and you will receive a new password shortly. Use this new password to access the site.' );
		} else {
			$return				.=					CBTxt::Th( 'UE_LOST_PASSWORD_EMAIL_ONLY_DESC', 'If you <strong>lost your password</strong>, please enter your E-mail Address, then click the Send Password button, and you will receive a new password shortly. Use this new password to access the site.' );
		}

		$return					.=				'</div>'
								.			'</div>';

		if ( $usernameExists ) {
			$usernameValidation	=	cbValidator::getRuleHtmlAttributes( 'cbfield', array( 'user' => 0, 'field' => 'username', 'reason' => 'register', 'function' => 'testexists' ) );

			$return				.=			'<div class="cb_forgot_line form-group cb_form_line clearfix" id="lostpassusername">'
								.				'<label for="checkusername" class="col-sm-3 control-label">' . CBTxt::Th( 'PROMPT_UNAME', 'Username:' ) . '</label>'
								.				'<div class="cb_field col-sm-9">'
								.					'<input type="text" name="checkusername" id="checkusername" class="form-control" size="30" maxlength="255"' . $usernameValidation . ' />'
								.				'</div>'
								.			'</div>';
		}

		$emailValidation		=	cbValidator::getRuleHtmlAttributes( 'cbfield', array( 'user' => 0, 'field' => 'email', 'reason' => 'register', 'function' => 'testexists' ) );

		$return					.=			'<div class="cb_forgot_line form-group cb_form_line clearfix" id="lostpassemail">'
								.				'<label for="checkemail" class="col-sm-3 control-label">' . CBTxt::Th( 'PROMPT_EMAIL', 'E-mail Address:' ) . '</label>'
								.				'<div class="cb_field col-sm-9">'
								.					'<input type="text" name="checkemail" id="checkemail" class="form-control" size="30" maxlength="255"' . $emailValidation . ' />'
								.				'</div>'
								.			'</div>';

		if ( is_array( $results ) ) foreach ( $results as $result ) {
			if ( $result ) {
				$return			.=			'<div class="cb_forgot_line form-group cb_form_line clearfix">'
								.				'<label' . ( isset( $result[2] ) ? ' for="' . htmlspecialchars( $result[2] ) . '"' : null ) . ' class="col-sm-3 control-label">' . ( isset( $result[0] ) ? $result[0] : null ) . '</label>'
								.				'<div class="cb_field col-sm-9">'
								.					( isset( $result[1] ) ? $result[1] : null )
								.				'</div>'
								.			'</div>';
			}
		}

		$return					.=			'<div class="cb_forgot_button form-group cb_form_line clearfix">'
								.				'<div class="col-sm-offset-3 col-sm-9">'
								.					'<input type="submit" class="btn btn-primary cbLostPassSend" value="'
								.						htmlspecialchars(
															$usernameExists ?
															CBTxt::Th( 'UE_BUTTON_SEND_USERNAME_PASS', 'Send Username/Password' )
															: CBTxt::Th( 'UE_BUTTON_SEND_PASS', 'Send Password' )
														)
								.					'" disabled="disabled"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
								.				'</div>'
								.			'</div>';

		if ( ! $usernameExists ) {
			$return				.= 			'<input type="hidden" name="typeofloose[]" value="password" />';
		}

		$return					.=			cbGetSpoofInputTag( 'lostPassForm' )
								.			cbGetRegAntiSpamInputTag( $regAntiSpamValues )
								.		'</form>'
								.	'</div>'
								.	cbPoweredBy();

		echo $return;

		$_CB_framework->setMenuMeta();
	}

	static function loginForm( /** @noinspection PhpUnusedParameterInspection */ $option, &$postvars, $regErrorMSG = null, $messagesToUser = null, $alertmessages = null ) {
		global $_CB_framework, $_CB_database, $_PLUGINS;

		$results					=	$_PLUGINS->trigger( 'onBeforeLoginFormDisplay', array( &$postvars, &$regErrorMSG, &$messagesToUser, &$alertmessages ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"" . $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $regErrorMSG ) {
			$_CB_framework->enqueueMessage( $regErrorMSG, 'error' );
		}

		outputCbTemplate( 1 );
		outputCbJs( 1 );
		initToolTip( 1 );

		$params						=	null;
		$moduleFile					=	$_CB_framework->getCfg( 'absolute_path' ) . '/modules/' . ( checkJversion() > 0 ? 'mod_cblogin/' : '' ) . 'mod_cblogin.php';

		if ( file_exists( $moduleFile ) ) {
			define( '_UE_LOGIN_FROM', 'loginform' );

			$query					=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__modules' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'module' ) . " = " . $_CB_database->Quote( 'mod_cblogin' )
									.	"\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1"
									.	"\n ORDER BY " . $_CB_database->NameQuote( 'ordering' );
			$_CB_database->setQuery( $query, 0, 1 );
			$module					=	null;
			$_CB_database->loadObject( $module );

			if ( $module ) {
				$moduleContent		=	JModuleHelper::renderModule( $module, array( 'style' => 'xhtml' ) );
			} else {
				$moduleContent		=	CBTxt::T( 'Error: CB Login module not created (required).' );
			}
		} else {
			$moduleContent			=	CBTxt::T( 'Error: CB Login module not installed (required).' );
		}

		$return						=	null;

		if ( ( is_array( $messagesToUser ) && $messagesToUser ) || ( is_array( $results ) && $results ) ) {
			$pageClass				=	$_CB_framework->getMenuPageClass();

			$return					.=	'<div class="cbLoginPage cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">'
									.		( is_array( $messagesToUser ) && $messagesToUser ? '<div>' . implode( '</div><div>', $messagesToUser ) . '</div>' : null )
									.		( is_array( $results ) && $results ? implode( '', $results ) : null )
									.	'</div>';
		}

		$return						.=	$moduleContent;

		echo $return;

		$_CB_framework->setMenuMeta();
	}

	static function registerForm( /** @noinspection PhpUnusedParameterInspection */ $option, $emailpass, $user, $postvars, $regErrorMSG = null, $stillDisplayLoginModule = false ) {
		global $_CB_framework, $_CB_database, $ueConfig, $_PLUGINS;

		$results						=	$_PLUGINS->trigger( 'onBeforeRegisterFormDisplay', array( &$user, $regErrorMSG ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".$_PLUGINS->getErrorMSG()."\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $regErrorMSG ) {
			$_CB_framework->enqueueMessage( $regErrorMSG, 'error' );
		}

		$cbTemplate						=	HTML_comprofiler::_cbTemplateLoad();

		outputCbTemplate( 1 );
		outputCbJs( 1 );
		initToolTip( 1 );

		$output							=	'htmledit';
		$layout							=	( isset( $ueConfig['reg_layout'] ) ? $ueConfig['reg_layout'] : 'flat' );
		$formatting						=	( isset( $ueConfig['use_divs'] ) && ( ! $ueConfig['use_divs'] ) ? ( $layout == 'flat' ? 'tabletrs' : 'table' ) : 'divs' );
		$tabbed							=	( in_array( $layout, array( 'tabbed', 'stepped' ) ) ? true : false );

		$translatedRegistrationTitle	=	CBTxt::T( 'UE_REGISTRATION', 'Sign up' );

		if ( $translatedRegistrationTitle ) {
			$_CB_framework->setPageTitle( $translatedRegistrationTitle );
			$_CB_framework->appendPathWay( $translatedRegistrationTitle );
		}

		$tabs							=	new cbTabs( 0, 1, null, ( $tabbed ? true : false ) );
		$tabcontent						=	$tabs->getEditTabs( $user, $postvars, $output, $formatting, 'register', ( $layout == 'tabbed' ? 1 : ( $layout == 'stepped' ? 2 : 0 ) ) );

		$topIcons						=	null;
		$bottomIcons					=	null;

		if ( isset( $ueConfig['reg_show_icons_explain'] ) && ( $ueConfig['reg_show_icons_explain'] > 0 ) ) {
			$icons						=	getFieldIcons( 1, true, true, '', '', true );

			if ( in_array( $ueConfig['reg_show_icons_explain'], array( 1, 3 ) ) ) {
				$topIcons				=	$icons;
			}

			if ( in_array( $ueConfig['reg_show_icons_explain'], array( 2, 3 ) ) ) {
				$bottomIcons			=	$icons;
			}
		}

		cbValidator::loadValidation();

		$moduleContent					=	null;

		if ( isset( $ueConfig['reg_show_login_on_page'] ) && ( $ueConfig['reg_show_login_on_page'] == 1 ) && ( $stillDisplayLoginModule || ( ! $regErrorMSG ) ) ) {
			$moduleFile					=	$_CB_framework->getCfg( 'absolute_path' ) . '/modules/' . ( checkJversion() > 0 ? 'mod_cblogin/' : null ) . 'mod_cblogin.php';

			if ( file_exists( $moduleFile ) ) {
				define( '_UE_LOGIN_FROM', 'loginform' );

				$query					=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__modules' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'module' ) . " = " . $_CB_database->Quote( 'mod_cblogin' )
										.	"\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1"
										.	"\n ORDER BY " . $_CB_database->NameQuote( 'ordering' );
				$_CB_database->setQuery( $query, 0, 1 );
				$module					=	null;
				$_CB_database->loadObject( $module );

				if ( $module ) {
					$moduleContent		=	JModuleHelper::renderModule( $module, array( 'style' => 'xhtml' ) );
				} else {
					$moduleContent		=	CBTxt::T( 'Error: CB Login module not created (required).' );
				}
			} else {
				$moduleContent			=	CBTxt::T( 'Error: CB Login module not installed (required).' );
			}
		}

		$headerMessage					=	( isset( $ueConfig['reg_intro_msg'] ) ? CBTxt::T( $ueConfig['reg_intro_msg'] ) : null );
		$footerMessage					=	( isset( $ueConfig['reg_conclusion_msg'] ) ? CBTxt::T( $ueConfig['reg_conclusion_msg'] ) : null );

		$registrationForm				=	'<form action="' . $_CB_framework->viewUrl( 'saveregisters', true, null, 'html', ( checkCBPostIsHTTPS( true ) ? 1 : 0 ) ) . '" method="post" id="cbcheckedadminForm" name="adminForm" enctype="multipart/form-data" class="cb_form form-auto cbValidation">'
										.		'<input type="hidden" name="id" value="0" />'
										.		'<input type="hidden" name="gid" value="0" />'
										.		'<input type="hidden" name="emailpass" value="' . htmlspecialchars( $emailpass ) . '" />'
										.		cbGetSpoofInputTag( 'registerForm' )
										.		cbGetRegAntiSpamInputTag();

		$return							=	$_PLUGINS->callTemplate( $cbTemplate, 'RegisterForm', 'drawProfile', array( &$user, $tabcontent, $registrationForm, $headerMessage, CBTxt::Th( 'LOGIN_REGISTER_TITLE', 'Welcome. Please log in or sign up:' ), CBTxt::Th( 'REGISTER_TITLE', 'Join us!' ), CBTxt::Th( 'UE_REGISTER', 'Sign up' ), $moduleContent, $topIcons, $bottomIcons, $footerMessage, $formatting, $results ), $output )
										.	cbPoweredBy();

		echo $return;

		$_PLUGINS->trigger( 'onAfterRegisterFormDisplay', array( $user, $tabcontent ) );

		$_CB_framework->setMenuMeta();
	}


/******************************
Moderation Functions
******************************/

	static function reportUserForm( /** @noinspection PhpUnusedParameterInspection */ $option, $uid, $reportedByUser, $reportedUser ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		$results			=	$_PLUGINS->trigger( 'onBeforeReportUserFormDisplay', array( $uid, &$reportedByUser, &$reportedUser ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $ueConfig['allowUserReports'] == 0 ) {
				echo CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
				return;
		}

		outputCbTemplate( 1 );
		cbValidator::loadValidation();

		$return				=	'<div class="cbReportUserForm cb_template cb_template_' . selectTemplate( 'dir' ) . '">';

		if ( is_array( $results ) ) {
			$return			.=		implode( '', $results );
		}

		$return				.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_REPORTUSER_TITLE', 'Report User' ) . '</h3></div>'
							.		'<form action="' . $_CB_framework->viewUrl( 'reportuser' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="reportexplaination" class="col-sm-3 control-label">' . CBTxt::Th( 'UE_REPORTUSERSACTIVITY', 'Describe User Activity' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<textarea name="reportexplaination" cols="50" rows="8" maxlength="4000" class="form-control required"></textarea>'
							.				'</div>'
							.			'</div>'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					'<input type="submit" class="btn btn-primary cbReportUsrSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_SUBMITFORM', 'Submit' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
							.					' <input type="button" class="btn btn-default cbReportUsrCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl( $uid ) . '\'; return false;" />'
							.				'</div>'
							.			'</div>'
							.			'<input type="hidden" name="reportedbyuser" value="' . (int) $_CB_framework->myId() . '" />'
							.			'<input type="hidden" name="reporteduser" value="' . (int) $uid . '" />'
							.			'<input type="hidden" name="reportform" value="0" />'
							.			cbGetSpoofInputTag( 'reportuser' )
							.		'</form>'
							.	'</div>';

		echo $return;
	}

	static function banUserForm( /** @noinspection PhpUnusedParameterInspection */ $option, $uid, $act, $orgBannedReason, $bannedByUser, $bannedUser ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		$results			=	$_PLUGINS->trigger( 'onBeforeBanUserFormDisplay', array( $uid, &$orgBannedReason, &$bannedByUser, &$bannedUser ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		if ( $ueConfig['allowUserBanning'] == 0 ) {
				echo CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
				return;
		}

		outputCbTemplate( 1 );
		cbValidator::loadValidation();

		$return				=	'<div class="cbBanUserForm cb_template cb_template_' . selectTemplate( 'dir' ) . '">';

		if ( is_array( $results ) ) {
			$return			.=		implode( '', $results );
		}

		$pageTitle			=	( $_CB_framework->myId() != $uid ? CBTxt::Th( 'UE_REPORTBAN_TITLE', 'Ban Report' ) : CBTxt::T( 'UE_REPORTUNBAN_TITLE', 'Unbanning Report' ) );

		$return				.=		( $pageTitle ? '<div class="page-header"><h3>' . $pageTitle . '</h3></div>' : null )
							.		'<form action="' . $_CB_framework->viewUrl( 'banProfile', true, array( 'act' => ( ( $_CB_framework->myId() != $uid ) ? 1 : 2 ), 'user' => (int) $uid ) ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<label for="bannedreason" class="col-sm-3 control-label">' . ( $_CB_framework->myId() != $uid ? CBTxt::Th( 'UE_BANREASON', 'Reason for Ban' ) : CBTxt::Th( 'UE_UNBANREQUEST', 'Unban Profile Request' ) ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					'<textarea name="bannedreason" cols="50" rows="8" maxlength="4000" class="form-control required"></textarea>'
							.				'</div>'
							.			'</div>'
							.			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					'<input type="submit" class="btn btn-primary cbBanUsrSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_SUBMITFORM', 'Submit' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
							.					' <input type="button" class="btn btn-default cbBanUsrCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl( $uid ) . '\'; return false;" />'
							.				'</div>'
							.			'</div>'
							.			'<input type="hidden" name="bannedby" value="' . (int) $_CB_framework->myId() . '" />'
							.			'<input type="hidden" name="uid" value="' . (int) $uid . '" />'
							.			'<input type="hidden" name="orgbannedreason" value="' . htmlspecialchars( $orgBannedReason ) . '" />'
							.			'<input type="hidden" name="reportform" value="0" />'
							.			cbGetSpoofInputTag( 'banUserForm' )
							.		'</form>'
							.	'</div>';

		echo $return;
	}

static function pendingApprovalUsers( /** @noinspection PhpUnusedParameterInspection */ $option, $users ) {
	global $_CB_framework, $_PLUGINS;

	$results					=	$_PLUGINS->trigger( 'onBeforePendingApprovalUsersFormDisplay', array( &$users ) );

	if ( $_PLUGINS->is_errors() ) {
		echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
		exit();
	}

	outputCbJs();
	outputCbTemplate();

	$pageClass					=	$_CB_framework->getMenuPageClass();

	$return						=	'<div class="cbPendingApprovalUsers cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

	if ( is_array( $results ) ) {
		$return					.=		implode( '', $results );
	}

	$return						.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_USERAPPROVAL_MODERATE', 'User Approval/Rejection' ) . '</h3></div>';

	if ( count( $users ) < 1 ) {
		$return					.=		CBTxt::Th( 'UE_NOUSERSPENDING', 'No Users Pending Approval' );
	} else {
		$toggleJs				=	"cbToggleAll( this, " . count( $users ) . ", 'uids' );";

		$return					.=		'<form action="' . $_CB_framework->viewUrl( 'pendingapprovaluser' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto">'
								.			'<table class="table table-hover table-responsive">'
								.				'<thead>'
								.					'<tr>'
								.						'<th style="width: 1%;" class="text-center"><input type="checkbox" name="toggle" value="" onclick="' . $toggleJs . '" /></th>'
								.						'<th style="width: 25%;" class="text-left">' . CBTxt::Th( 'UE_USER', 'User' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_EMAIL', 'Email' ) . '</th>'
								.						'<th style="width: 24%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_REGISTERDATE', 'Date Registered' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left">' . CBTxt::Th( 'UE_COMMENT', 'Reject Comment' ) . '</th>'
								.					'</tr>'
								.				'</thead>'
								.				'<tbody>';

		for ( $i = 0; $i < count( $users ); $i++ ) {
			$user				=	$users[$i];

			$return				.=					'<tr>'
								.						'<td style="width: 1%;" class="text-center"><input type="checkbox" id="uids' . $i . '" name="uids[]" checked="checked" value="' . (int) $user->id . '" /></td>'
								.						'<td style="width: 25%;" class="text-left">' . CBuser::getInstance( (int) $user->id, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
								.						'<td style="width: 25%;" class="text-left xs-hidden">' . $user->email . '</td>'
								.						'<td style="width: 24%;" class="text-left xs-hidden">' . cbFormatDate( $user->registerDate ) . '</td>'
								.						'<td style="width: 25%;" class="text-left"><textarea name="comment' . (int) $user->id . '" cols="20" rows="3" class="form-control"></textarea></td>'
								.					'</tr>';
		}

		$return					.=				'</tbody>'
								.			'</table>'
								.			'<div class="form-group cb_form_line clearfix">'
								.				'<input type="button" class="btn btn-success cbPendUserApprove" value="' . htmlspecialchars( CBTxt::Th( 'UE_APPROVE', 'Approve' ) ) . '" onclick="this.form.view.value=\'approveuser\'; this.form.submit();" />'
								.				' <input type="button" class="btn btn-danger cbPendUserReject" value="' . htmlspecialchars( CBTxt::Th( 'UE_REJECT', 'Reject' ) ) . '" onclick="this.form.view.value=\'rejectuser\'; this.form.submit();" />'
								.			'</div>'
								.			'<input type="hidden" name="view" value="" />'
								.			cbGetSpoofInputTag( 'pendingapprovaluser' )
								.		'</form>';
	}

	$return						.=	'</div>';

	echo $return;

	$_CB_framework->setMenuMeta();
}

/**
 * @param  array       $connections
 * @param  array       $actions
 * @param  int         $total
 * @param  cbTabs      $connMgmtTabs
 * @param  array       $pagingParams
 * @param  int         $perpage
 * @param  array|null  $connecteds
 */
static function manageConnections( $connections, $actions, $total, &$connMgmtTabs, &$pagingParams, $perpage, $connecteds = null ) {
	global $_CB_framework, $ueConfig, $_PLUGINS, $_REQUEST;

	$results					=	$_PLUGINS->trigger( 'onBeforeManageConnectionsFormDisplay', array( &$connections, &$actions, &$total, &$connMgmtTabs, &$pagingParams, &$perpage, &$connecteds ) );

	if ( $_PLUGINS->is_errors() ) {
		echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
		exit();
	}

	outputCbTemplate( 1 );
	initToolTip( 1 );
	cbValidator::loadValidation();

	$js							=	"if ( typeof confirmSubmit != 'function' ) {"
								.		"function confirmSubmit() {"
								.			"if ( confirm( '" . addslashes( CBTxt::T( 'UE_CONFIRMREMOVECONNECTION', 'Are you sure you want to remove this connection?' ) ) . "' ) ) {"
								.				"return true;"
								.			"} else {"
								.				"return false;"
								.			"}"
								.		"};"
								.	"}";

	$_CB_framework->document->addHeadScriptDeclaration( $js );

	$connectionCategories		=	explode( "\n", $ueConfig['connection_categories'] );
	$connectionTypes			=	array();

	if ( $connectionCategories ) foreach ( $connectionCategories as $connectionCategory ) {
		if ( ( trim( $connectionCategory ) != null ) && ( trim( $connectionCategory ) != "" ) ) {
			$connectionTypes[]	=	moscomprofilerHTML::makeOption( trim( $connectionCategory ) , CBTxt::T( trim( $connectionCategory ) ) );
		}
	}

	$tabs						=	new cbTabs( 0, 1 );

	$pageClass					=	$_CB_framework->getMenuPageClass();

	$return						=	'<div class="cbManageConnections cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

	if ( is_array( $results ) ) {
		$return					.=		implode( '', $results );
	}

	$return						.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_MANAGECONNECTIONS', 'Manage Connections' ) . '</h3></div>'
								.		$tabs->startPane( 'myCon' )
								.			$tabs->startTab( 'myCon', CBTxt::Th( 'UE_MANAGEACTIONS', 'Manage Actions' ) . ' <span class="badge">' . count( $actions ) . '</span>', 'action' );

	if ( ! count( $actions ) > 0 ) {
		$return					.=				'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::Th( 'UE_NOACTIONREQUIRED', 'No Pending Actions' ) . '</div>'
								.				'<div class="form-group cb_form_line clearfix">'
								.					'<input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.				'</div>';
	} else {
		$return					.=				'<form action="' . $_CB_framework->viewUrl( 'processconnectionactions' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
								.					'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::Th( 'UE_CONNECT_ACTIONREQUIRED', 'Below you see users proposing to connect with you. You have the choice to accept or decline their request.' ) . '</div>'
								.					'<div class="table">';

		foreach( $actions as $action ) {
			$cbUser				=	CBuser::getInstance( (int) $action->id, false );

			$tipField			=	'<b>' . CBTxt::Th( 'UE_CONNECTIONREQUIREDON', 'Connection Required on' ) . '</b>: '
								. $_CB_framework->getUTCDate( array( $ueConfig['date_format'], 'Y-m-d' ),  $action->membersince );

			if ( $action->reason != null ) {
				$tipField		.=	'<br /><b>' . CBTxt::Th( 'UE_CONNECTIONMESSAGE', 'Personal message included' ) . '</b>: <br />' . htmlspecialchars( $action->reason, ENT_QUOTES );
			}

			$tipTitle			=	CBTxt::Th( 'UE_CONNECTIONREQUESTDETAIL', 'Connection Request Details' );
			$htmlText			=	$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true );
			$tooltip			=	cbTooltip( 1, $tipField, $tipTitle, 300, null, $htmlText, null, 'style="display: inline-block; padding: 5px;"' );

			$return				.=						'<div class="containerBox img-thumbnail">'
								.							'<div class="containerBoxInner" style="min-height: 130px; min-width: 90px;">'
								.								$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.								' ' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true )
								.								'<br />' . $tooltip . '<br />'
								.								'<span class="fa fa-check" title="' . htmlspecialchars( CBTxt::T( 'UE_ACCEPTCONNECTION', 'Accept Connection' ) ) . '"></span>'
								.								' <input type="radio" name="' . (int) $action->id . 'action" value="a" checked="checked" />'
								.								' <span class="fa fa-times" title="' . htmlspecialchars( CBTxt::T( 'UE_DECLINECONNECTION', 'Decline Connection' ) ) . '"></span>'
								.								' <input type="radio" name="' . (int) $action->id . 'action" value="d" />'
								.								'<input type="hidden" name="uid[]" value="' . (int) $action->id . '" />'
								.							'</div>'
								.						'</div>';
		}

		$return					.=					'</div>'
								.					'<div class="form-group cb_form_line clearfix">'
								.						'<input type="submit" class="btn btn-primary cbMngConnSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_UPDATE', 'Update' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
								.						' <input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.					'</div>'
								.					cbGetSpoofInputTag( 'manageconnections' )
								.				'</form>';
	}

	$return						.=			$tabs->endTab()
								.			$tabs->startTab( 'myCon', CBTxt::Th( 'UE_MANAGECONNECTIONS', 'Manage Connections' ), 'connections' );

	if ( ! count( $connections ) > 0 ) {
		$return					.=				'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::Th( 'UE_NOCONNECTIONS', 'This user has no current connections.' ) . '</div>'
								.				'<div class="form-group cb_form_line clearfix">'
								.					'<input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.				'</div>';
	} else {
		$return					.=				'<form action="' . $_CB_framework->viewUrl( 'saveconnections' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
								.					'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::Th( 'UE_CONNECT_MANAGECONNECTIONS', 'Below you see users to whom you are connected directly. ' ) . '</div>'
								.					'<table class="table table-hover table-responsive">'
								.						'<thead>'
								.							'<tr>'
								.								'<th style="width: 25%;" class="text-center">' . CBTxt::Th( 'UE_CONNECTION', 'Connections' ) . '</th>'
								.								'<th style="width: 35%;" class="text-center">' . CBTxt::Th( 'UE_CONNECTIONTYPE', 'Type' ) . '</th>'
								.								'<th style="width: 40%;" class="text-center">' . CBTxt::Th( 'UE_CONNECTIONCOMMENT', 'Comment' ) . '</th>'
								.							'</tr>'
								.						'</thead>'
								.						'<tbody>';

		$i						=	1;

		foreach( $connections as $connection ) {
			$cbUser				=	CBuser::getInstance( (int) $connection->id, false );

			$tipField			=	'<b>' .CBTxt::Th( 'UE_CONNECTEDSINCE', 'Connected Since' ) . '</b>: '
								.	 $_CB_framework->getUTCDate( array( $ueConfig['date_format'], 'Y-m-d' ),  $connection->membersince );

			if ( $connection->type != null ) {
				$tipField		.=	'<br /><b>' . CBTxt::Th( 'UE_CONNECTIONTYPE', 'Type' ) . '</b>: ' . getConnectionTypes( $connection->type );
			}

			if ( $connection->description != null ) {
				$tipField		.=	'<br /><b>' . CBTxt::Th( 'UE_CONNECTEDCOMMENT', 'Comment' ) . '</b>: ' . htmlspecialchars( $connection->description );
			}

			$tipTitle			=	CBTxt::Th( 'UE_CONNECTEDDETAIL', 'Connection Details' );
			$htmlText			=	$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true );
			$tooltip			=	cbTooltip( 1, $tipField, $tipTitle, 300, null, $htmlText, null, 'style="display: inline-block; padding: 5px;"' );

			if ( ( $connection->accepted == 1 ) && ( $connection->pending == 1 ) ) {
				$actionImg		=	'<span class="fa fa-clock-o" title="' . htmlspecialchars( CBTxt::T( 'UE_CONNECTIONPENDING', 'Connection Pending' ) ) . '"></span>'
								.	' <a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
			} elseif ( ( $connection->accepted == 1 ) && ( $connection->pending == 0 ) ) {
				$actionImg		=	'<a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
			} elseif ( $connection->accepted == 0 ) {
				$actionImg		=	'<a href="' . $_CB_framework->viewUrl( 'acceptconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
								.		'<span class="fa fa-check-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_ACCEPTCONNECTION', 'Accept Connection' ) ) . '"></span>'
								.	'</a>'
								.	' <a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
			} else {
				$actionImg		=	null;
			}

			$return				.=							'<tr>'
								.								'<td class="text-center">'
								.									$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.									' ' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true )
								.									'<br />' . $tooltip . '<br />'
								.									$actionImg
								.									' <a href="' . $_CB_framework->userProfileUrl( (int) $connection->memberid ) . '">'
								.										'<span class="fa fa-user" title="' . htmlspecialchars( CBTxt::T( 'UE_VIEWPROFILE', 'View Profile' ) ) . '"></span>'
								.									'</a>'
								.									' ' . $cbUser->getField( 'email', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.									' ' . $cbUser->getField( 'pm', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.								'</td>'
								.								'<td class="text-center">'
								.									moscomprofilerHTML::selectList( $connectionTypes, $connection->id . 'connectiontype[]', 'class="form-control" multiple="multiple" size="5"', 'value', 'text', explode( '|*|', trim( $connection->type ) ), 0 )
								.								'</td>'
								.								'<td class="text-center">'
								.									'<textarea cols="25" class="form-control"  rows="5" name="' . (int) $connection->id . 'description">' . htmlspecialchars( $connection->description ) . '</textarea>'
								.									'<input type="hidden" name="uid[]" value="' . (int) $connection->id . '" />'
								.								'</td>'
								.							'</tr>';

			$i					=	( $i == 1 ? 2 : 1 );
		}

		$return					.=						'</tbody>'
								.					'</table>';

		if ( $perpage < $total ) {
			$return				.=					'<div class="form-group cb_form_line text-center clearfix">'
								.						$connMgmtTabs->_writePaging( $pagingParams, 'connections_', $perpage, $total, 'manageconnections' )
								.					'</div>';
		}

		$return					.=					'<div class="form-group cb_form_line clearfix">'
								.						'<input type="submit" class="btn btn-primary cbMngConnSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_UPDATE', 'Update' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
								.						' <input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.					'</div>'
								.					cbGetSpoofInputTag( 'manageconnections' )
								.				'</form>';
	}

	$return						.=			$tabs->endTab();

	if ( $ueConfig['autoAddConnections'] == 0 ) {
		$return					.=			$tabs->startTab( 'myCon', CBTxt::Th( 'UE_CONNECTEDWITH', 'Manage Connections With Me' ), 'connected' );

		if ( ! count( $connecteds ) > 0 ) {
			$return				.=				'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::Th( 'UE_NOCONNECTEDWITH', 'There are currently no users connected with you.' ) . '</div>'
								.				'<div class="form-group cb_form_line clearfix">'
								.					'<input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.				'</div>';
		} else {
			$htmlConnectedWidth	=	CBTxt::Th( 'UE_CONNECT_CONNECTEDWITH', '' );
			$return				.=				( $htmlConnectedWidth ? '<div class="form-group cb_form_line clearfix tab_description">' . $htmlConnectedWidth . '</div>' : null )
								.				'<div class="table">';

			foreach( $connecteds as $connected ) {
				$cbUser			=	CBuser::getInstance( (int) $connected->id, false );

				$tipField		=	'<b>' .CBTxt::Th( 'UE_CONNECTEDSINCE', 'Connected Since' ) . '</b>: '
								.	 $_CB_framework->getUTCDate( array( $ueConfig['date_format'], 'Y-m-d' ),  $connected->membersince );

				if ( $connected->type != null ) {
					$tipField	.=	'<br /><b>' . CBTxt::Th( 'UE_CONNECTIONTYPE', 'Type' ) . '</b>: ' . getConnectionTypes( $connected->type );
				}

				if ( $connected->description != null ) {
					$tipField	.=	'<br /><b>' . CBTxt::Th( 'UE_CONNECTEDCOMMENT', 'Comment' ) . '</b>: ' . htmlspecialchars( $connected->description );
				}

				$tipTitle		=	CBTxt::Th( 'UE_CONNECTEDDETAIL', 'Connection Details');
				$htmlText		=	$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true );
				$tooltip		=	cbTooltip( 1, $tipField, $tipTitle, 300, null, $htmlText, null, 'style="padding: 5px;"' );

				if ( ( $connected->accepted == 1 ) && ( $connected->pending == 1 ) ) {
					$actionImg	=	'<span class="fa fa-clock-o" title="' . htmlspecialchars( CBTxt::T( 'UE_CONNECTIONPENDING', 'Connection Pending' ) ) . '"></span>'
								.	' <a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connected->memberid ) ) . '" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
				} elseif ( ( $connected->accepted == 1 ) && ( $connected->pending == 0 ) ) {
					$actionImg	=	'<a href="' . $_CB_framework->viewUrl( 'denyconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connected->referenceid ) ) . '" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
				} elseif ( $connected->accepted == 0 ) {
					$actionImg	=	'<a href="' . $_CB_framework->viewUrl( 'acceptconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connected->referenceid ) ) . '" >'
								.		'<span class="fa fa-check-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_ACCEPTCONNECTION', 'Accept Connection' ) ) . '"></span>'
								.	'</a>'
								.	' <a href="' . $_CB_framework->viewUrl( 'denyconnection', true, array( 'act' => 'manage', 'connectionid' => (int) $connected->referenceid ) ) . '" >'
								.		'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
								.	'</a>';
				} else {
					$actionImg	=	null;
				}

				$return			.=					'<div class="containerBox img-thumbnail">'
								.						'<div class="containerBoxInner" style="min-height: 130px; min-width: 90px;">'
								.							$actionImg . '<br />'
								.							$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.							' ' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true )
								.							'<br />' . $tooltip . '<br />'
								.							' <a href="' . $_CB_framework->userProfileUrl( (int) $connected->referenceid ) . '">'
								.								'<span class="fa fa-user" title="' . htmlspecialchars( CBTxt::T( 'UE_VIEWPROFILE', 'View Profile' ) ) . '"></span>'
								.							'</a>'
								.							' ' . $cbUser->getField( 'email', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.							' ' . $cbUser->getField( 'pm', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
								.						'</div>'
								.					'</div>';
			}

			$return				.=				'</div>'
								.				'<div class="form-group cb_form_line clearfix">'
								.					'<input type="button" class="btn btn-default cbMngConnCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" onclick="window.location=\'' . $_CB_framework->userProfileUrl() . '\'; return false;" />'
								.				'</div>';
		}

		$return					.=			$tabs->endTab();
	}

	$return						.=		$tabs->endPane()
								.	'</div>'
								.	cbPoweredBy();

	echo $return;

	$_CB_framework->setMenuMeta();
}

}	// end class HTML_comprofiler

	function moderateBans( /** @noinspection PhpUnusedParameterInspection */ $option, $act, $uid ) {
		global $_CB_framework, $_CB_database, $_PLUGINS, $_REQUEST;

		$_PLUGINS->loadPluginGroup( 'user' );

		$results					=	$_PLUGINS->trigger( 'onBeforeModerateBansFormDisplay', array( $uid, $act ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		$isModerator				=	Application::MyUser()->isGlobalModerator();

		if ( ( ! $isModerator ) || ( ( $act == 2 ) && ( $uid == $_CB_framework->myId() ) ) ) {
			cbNotAuth();
			return;
		}

		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' );
		if ( $act == 2 ) {
			$query					.=	"\n WHERE NOT( ISNULL( " . $_CB_database->NameQuote( 'banned' ) . " ) )"
									.	"\n AND " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $uid;
		} else {
			$query					.=	"\n WHERE " . $_CB_database->NameQuote( 'banned' ) . " = 2"
									.	"\n AND " . $_CB_database->NameQuote( 'id' ) . " != " . (int) $_CB_framework->myId();
		}
		$query						.=	"\n AND " . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
		$_CB_database->setQuery( $query );
		$total						=	$_CB_database->loadResult();

		$limitstart					=	(int) getPagesLimitStart( $_REQUEST );
		$limit						=	20;

		if ( $limit > $total ) {
			$limitstart				=	0;
		}

		$query						=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' );
		if ( $act == 2 ) {
			$query					.=	"\n WHERE NOT( ISNULL( " . $_CB_database->NameQuote( 'banned' ) . " ) )"
									.	"\n AND " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $uid;
		} else {
			$query					.=	"\n WHERE " . $_CB_database->NameQuote( 'banned' ) . " = 2"
									.	"\n AND " . $_CB_database->NameQuote( 'id' ) . " != " . (int) $_CB_framework->myId();
		}
		$query						.=	"\n AND " . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
		$_CB_database->setQuery( $query, $limitstart, $limit );
		$rows						=	$_CB_database->loadObjectList();

		outputCbTemplate( 1 );

		$pageClass					=	$_CB_framework->getMenuPageClass();

		$return						=	'<div class="cbModerateBans cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( is_array( $results ) ) {
			$return					.=		implode( '', $results );
		}

		$return						.=		'<div class="page-header"><h3>' . CBTxt::T( 'UE_UNBAN_MODERATE', 'Unban Profile Requests' ) . '</h3></div>';

		if ( $total < 1 ) {
			$return					.=		CBTxt::T( 'UE_NOUNBANREQUESTS', 'No Unban Requests to Process' );
		} else {
			$return					.=		'<div class="form-group cb_form_line clearfix tab_description">' . CBTxt::T( 'UE_UNBAN_MODERATE_DESC', 'Click on the Banned Username to view the corresponding user profile.' ) . '</div>'
									.		'<table class="table table-hover table-responsive">'
									.			'<thead>'
									.				'<tr>'
									.					'<th class="text-left">' . CBTxt::Th( 'UE_BANNEDUSER', 'Banned User' ) . '</th>'
									.					'<th class="text-left">' . CBTxt::Th( 'UE_BANNEDREASON', 'Banned Reason' ) . '</th>'
									.					'<th class="text-left xs-hidden">' . CBTxt::Th( 'UE_BANNEDON', 'Banned Date' ) . '</th>'
									.					'<th class="text-left xs-hidden">' . CBTxt::Th( 'UE_BANNEDBY', 'Banned By' ) . '</th>'
									.					'<th class="text-left xs-hidden">' . CBTxt::Th( 'UE_UNBANNEDON', 'Unbanned Date' ) . '</th>'
									.					'<th class="text-left xs-hidden">' . CBTxt::Th( 'UE_UNBANNEDBY', 'Unbanned By' ) . '</th>'
									.					'<th class="text-left">' . CBTxt::Th( 'UE_BANSTATUS', 'Ban status' ) . '</th>'
									.				'</tr>'
									.			'</thead>'
									.			'<tbody>';

			for ( $i = 0; $i < count( $rows ); $i++ ) {
				$row				=	$rows[$i];

				$return				.=				'<tr>'
									.					'<td class="text-left">' . CBuser::getInstance( (int) $row->id, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.					'<td class="text-left">' . $row->bannedreason . '</td>'
									.					'<td class="text-left xs-hidden">' . cbFormatDate( $row->banneddate ) . '</td>'
									.					'<td class="text-left xs-hidden">' . CBuser::getInstance( (int) $row->bannedby, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.					'<td class="text-left xs-hidden">' . cbFormatDate( $row->unbanneddate ) . '</td>'
									.					'<td class="text-left xs-hidden">' . CBuser::getInstance( (int) $row->unbannedby, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.					'<td class="text-left ' . ( $row->banned == 1 ? 'text-danger' : ( $row->banned == 2 ? 'text-warning' : 'text-success' ) ) . '">'
									.						( $row->banned == 1 ?
																CBTxt::Th( 'UE_BANSTATUS_BANNED', 'Banned' )
																: ( $row->banned == 2 ?
																	CBTxt::Th( 'UE_BANSTATUS_UNBAN_REQUEST_PENDING', 'Unban request pending' )
																	: CBTxt::Th( 'UE_BANSTATUS_PROCESSED', 'Processed' )
																  )
															)
									.					'</td>'
									.				'</tr>';
			}

			$return					.=			'</tbody>'
									.		'</table>';

			if ( $total > $limit ) {
				$return				.=		'<div class="form-group cb_form_line text-center clearfix">'
									.			writePagesLinks( $limitstart, $limit, $total, $_CB_framework->viewUrl( 'moderatebans' ) )
									.		'</div>';
			}
		}

		$return						.=	'</div>';

		echo $return;

		$_CB_framework->setMenuMeta();
	}

	function moderateReports( /** @noinspection PhpUnusedParameterInspection */ $option ) {
		global $_CB_framework, $_CB_database, $_PLUGINS, $_REQUEST;

		$_PLUGINS->loadPluginGroup( 'user' );

		$results					=	$_PLUGINS->trigger( 'onBeforeModerateReportsFormDisplay', array() );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		$isModerator				=	Application::MyUser()->isGlobalModerator();

		if ( ! $isModerator ) {
			cbNotAuth();
			return;
		}

		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0";
		$_CB_database->setQuery( $query );
		$total						=	$_CB_database->loadResult();

		$limitstart					=	(int) getPagesLimitStart( $_REQUEST );
		$limit						=	20;

		if ( $limit > $total ) {
			$limitstart				=	0;
		}

		$query						=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0"
									.	"\n ORDER BY " . $_CB_database->NameQuote( 'reporteduser' ) . ", " . $_CB_database->NameQuote( 'reportedondate' );
		$_CB_database->setQuery( $query, $limitstart, $limit );
		$rows						=	$_CB_database->loadObjectList();

		outputCbJs();
		outputCbTemplate();
		cbValidator::loadValidation();

		$pageClass					=	$_CB_framework->getMenuPageClass();

		$return						=	'<div class="cbModerateReports cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( is_array( $results ) ) {
			$return					.=		implode( '', $results );
		}

		$return						.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_USERREPORT_MODERATE', 'Moderate User Reports' ) . '</h3></div>';

		if ( $total < 1 ) {
			$return					.=		CBTxt::Th( 'UE_NOREPORTSTOPROCESS', 'No User Reports to Process' );
		} else {
			$toggleJs				=	"cbToggleAll( this, " . count( $rows ) . ", 'reports' );";

			$return					.=		'<form action="' . $_CB_framework->viewUrl( 'processreports' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto cbValidation">'
									.			'<table class="table table-hover table-responsive">'
									.				'<thead>'
									.					'<tr>'
									.						'<th style="width: 1%;" class="text-center"><input type="checkbox" name="toggle" value="" onclick="' . $toggleJs . '" /></th>'
									.						'<th style="width: 25%;" class="text-left">' . CBTxt::Th( 'UE_REPORTEDUSER', 'Reported User' ) . '</th>'
									.						'<th style="width: 25%;" class="text-left">' . CBTxt::Th( 'UE_REPORT', 'Report' ) . '</th>'
									.						'<th style="width: 24%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_REPORTEDONDATE', 'Report Date' ) . '</th>'
									.						'<th style="width: 25%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_REPORTEDBY', 'Reported By' ) . '</th>'
									.					'</tr>'
									.				'</thead>'
									.				'<tbody>';

			for ( $i = 0; $i < count( $rows ); $i++ ) {
				$row				=	$rows[$i];

				$return				.=					'<tr>'
									.						'<td style="width: 1%;" class="text-center"><input type="checkbox" id="reports' . $i . '" name="reports[]" checked="checked" value="' . (int) $row->reportid . '" /></td>'
									.						'<td style="width: 25%;" class="text-left">' . CBuser::getInstance( (int) $row->reporteduser, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.						'<td style="width: 25%;" class="text-left">' . $row->reportexplaination . '</td>'
									.						'<td style="width: 24%;" class="text-left xs-hidden">' . cbFormatDate( $row->reportedondate ) . '</td>'
									.						'<td style="width: 25%;" class="text-left xs-hidden">' . CBuser::getInstance( (int) $row->reportedbyuser, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.					'</tr>';
			}

			$return					.=				'</tbody>'
									.			'</table>'
									.			'<div class="form-group cb_form_line clearfix">'
									.				'<input type="submit" class="btn btn-primary cbModReportsProcess" value="' . htmlspecialchars( CBTxt::Th( 'UE_PROCESSUSERREPORT', 'Process' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
									.			'</div>'
									.			cbGetSpoofInputTag( 'moderatereports' )
									.		'</form>';

			if ( $total > $limit ) {
				$return				.=		'<div class="form-group cb_form_line text-center clearfix">'
									.			writePagesLinks( $limitstart, $limit, $total, $_CB_framework->viewUrl( 'moderatereports' ) )
									.		'</div>';
			}
		}

		$return						.=	'</div>';

		echo $return;

		$_CB_framework->setMenuMeta();
    }

	function moderateImages( /** @noinspection PhpUnusedParameterInspection */ $option ) {
		global $_CB_framework, $_CB_database, $_PLUGINS, $_REQUEST;

		$_PLUGINS->loadPluginGroup( 'user' );

		$results					=	$_PLUGINS->trigger( 'onBeforeModerateImagesFormDisplay', array() );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		$isModerator				=	Application::MyUser()->isGlobalModerator();

		if ( ! $isModerator ) {
			cbNotAuth();
			return;
		}

		$avatarPath					=	$_CB_framework->getCfg( 'live_site' ) . '/images/comprofiler/';

		$query						=	'SELECT ' . $_CB_database->NameQuote( 'name' )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'image' );
		$_CB_database->setQuery( $query );
		$imageFields				=	$_CB_database->loadResultArray();

		$approvedColumns			=	array();

		if ( $imageFields ) foreach ( $imageFields as $imageField ) {
			$approvedColumns[]		=	"( c." . $_CB_database->NameQuote( $imageField ) . " != '' AND c." . $_CB_database->NameQuote( $imageField ) . " IS NOT NULL AND c." . $_CB_database->NameQuote( $imageField . 'approved' ) . " = 0 )";
		}

		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
									.	"\n WHERE ( " . implode( ' OR ', $approvedColumns ) . " )"
									.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND c." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
									.	"\n AND c." . $_CB_database->NameQuote( 'banned' ) . " = 0";
		$_CB_database->setQuery( $query );
		$total						=	$_CB_database->loadResult();

		$limitstart					=	(int) getPagesLimitStart( $_REQUEST );
		$limit						=	20;

		if ( $limit > $total ) {
			$limitstart				=	0;
		}

		$query						=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
									.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
									.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
									.	"\n WHERE ( " . implode( ' OR ', $approvedColumns ) . " )"
									.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND c." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
									.	"\n AND c." . $_CB_database->NameQuote( 'banned' ) . " = 0";
		$_CB_database->setQuery( $query, $limitstart, $limit );
		$rows						=	$_CB_database->loadObjectList();

		outputCbTemplate( 1 );

		$pageClass					=	$_CB_framework->getMenuPageClass();

		$return						=	'<div class="cbModerateImages cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( is_array( $results ) ) {
			$return					.=		implode( '', $results );
		}

		$return						.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_IMAGE_MODERATE', 'Moderate Images' ) . '</h3></div>';

		if ( $total < 1 ) {
			$return					.=		CBTxt::Th( 'UE_NOIMAGESTOAPPROVE', 'No Images to Process' );
		} else {
			$return					.=		'<form action="' . $_CB_framework->viewUrl( 'approveimage' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto">'
									.			'<div class="table">';

			for ( $i = 0; $i < count( $rows ); $i++ ) {
				$row				=	$rows[$i];

				$name				=	CBuser::getInstance( (int) $row->id, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true );

				if ( $imageFields ) foreach ( $imageFields as $imageField ) {
					$approvedColumn	=	$imageField . 'approved';

					if ( $row->$approvedColumn == 0 ) {
						$image		=	$avatarPath . 'tn' . $row->$imageField;

						$return		.=				'<div class="containerBox img-thumbnail" style="min-height: 130px; min-width: 90px;">'
									.					'<input id="img' . (int) $row->id . '" type="checkbox" checked="checked" name="images[' . (int) $row->id . '][]" value="' . htmlspecialchars( $imageField ) . '" />'
									.					' ' . $name
									.					'<br />'
									.					'<img src="' . htmlspecialchars( $image ) . '" class="cbThumbPict img-thumbnail" style="margin: 5px;" />'
									.					'<br />'
									.					'<a href="' . $_CB_framework->viewUrl( 'approveimage', true, array( 'flag' => 1, 'images[' . (int) $row->id . '][]' => $imageField ) ) . '">'
									.						'<span class="fa fa-check-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_APPROVE_IMAGE', 'Approve Image' ) ) . '"></span>'
									.					'</a>'
									.					' <a href="' . $_CB_framework->viewUrl( 'approveimage', true, array( 'flag' => 0, 'images[' . (int) $row->id . '][]' => $imageField ) ) . '">'
									.						'<span class="fa fa-times-circle-o" title="' . htmlspecialchars( CBTxt::T( 'UE_REJECT_IMAGE', 'Reject Image' ) ) . '"></span>'
									.					'</a>'
									.					' <a href="' . $_CB_framework->userProfileUrl( (int) $row->id ) . '">'
									.						'<span class="fa fa-user" title="' . htmlspecialchars( CBTxt::T( 'UE_VIEWPROFILE', 'View Profile' ) ) . '"></span>'
									.					'</a>'
									.				'</div>';
					}
				}
			}

			$return					.=			'</div>'
									.			'<div class="form-group cb_form_line clearfix">'
									.				'<input type="button" class="btn btn-success cbModImgApprove" value="' . htmlspecialchars( CBTxt::Th( 'UE_APPROVE', 'Approve' ) ) . '" onclick="this.form.act.value=\'1\'; this.form.submit();" />'
									.				' <input type="button" class="btn btn-danger cbModImgReject" value="' . htmlspecialchars( CBTxt::Th( 'UE_REJECT', 'Reject' ) ) . '" onclick="this.form.act.value=\'0\'; this.form.submit();" />'
									.			'</div>'
									.			'<input type="hidden" name="act" value="" />'
									.			cbGetSpoofInputTag( 'moderateimages' )
									.		'</form>';

			if ( $total > $limit ) {
				$return				.=		'<div class="form-group cb_form_line text-center clearfix">'
									.			writePagesLinks( $limitstart, $limit, $total, $_CB_framework->viewUrl( 'moderateimages' ) )
									.		'</div>';
			}
		}

		$return						.=	'</div>';

		echo $return;

		$_CB_framework->setMenuMeta();
	}

	function viewReports( /** @noinspection PhpUnusedParameterInspection */ $option, $uid, $act ) {
		global $_CB_framework, $_CB_database, $_PLUGINS, $_REQUEST;

		$_PLUGINS->loadPluginGroup( 'user' );

		$results					=	$_PLUGINS->trigger( 'onBeforeViewReportsFormDisplay', array( $uid, $act ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".  $_PLUGINS->getErrorMSG() . "\"); window.history.go(-1); </script>\n";
			exit();
		}

		$isModerator				=	Application::MyUser()->isGlobalModerator();

		if ( ! $isModerator ) {
			cbNotAuth();
			return;
		}

		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'reporteduser' ) . " = " . (int) $uid
									.	( $act == 1 ? "\n AND " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0" : null );
		$_CB_database->setQuery( $query );
		$total						=	$_CB_database->loadResult();

		$limitstart					=	(int) getPagesLimitStart( $_REQUEST );
		$limit						=	20;

		if ( $limit > $total ) {
			$limitstart				=	0;
		}

		$query						=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'reporteduser' ) . " = " . (int) $uid
									.	( $act == 1 ? "\n AND " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0" : null )
									.	"\n ORDER BY " . $_CB_database->NameQuote( 'reporteduser' ) . ", " . $_CB_database->NameQuote( 'reportedondate' );
		$_CB_database->setQuery( $query, $limitstart, $limit );
		$rows						=	$_CB_database->loadObjectList();

		outputCbTemplate( 1 );

		$return						=	'<div class="cbViewReports cb_template cb_template_' . selectTemplate( 'dir' ) . '">';

		if ( is_array( $results ) ) {
			$return					.=		implode( '', $results );
		}

		$return						.=		'<div class="page-header"><h3>' . CBTxt::Th( 'UE_USERREPORT', 'User Report' ) . '</h3></div>';

		if ( $total < 1 ) {
			$return					.=		CBTxt::Th( 'UE_NOREPORTSTOPROCESS', 'No User Reports to Process' );
		} else {
			$return					.=		'<form action="' . $_CB_framework->viewUrl( 'moderatereports' ) . '" method="post" id="adminForm" name="adminForm" class="cb_form form-auto">'
									.			'<table class="table table-hover table-responsive">'
									.				'<thead>'
									.					'<tr>'
									.						'<th style="width: 20%;" class="text-left">' . CBTxt::Th( 'UE_REPORTEDUSER', 'Reported User' ) . '</th>'
									.						'<th style="width: 20%;" class="text-left">' . CBTxt::Th( 'UE_REPORT', 'Report' ) . '</th>'
									.						'<th style="width: 20%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_REPORTEDONDATE', 'Report Date' ) . '</th>'
									.						'<th style="width: 20%;" class="text-left xs-hidden">' . CBTxt::Th( 'UE_REPORTEDBY', 'Reported By' ) . '</th>'
									.						'<th style="width: 20%;" class="text-left">' . CBTxt::Th( 'UE_REPORTSTATUS', 'Report status' ) . '</th>'
									.					'</tr>'
									.				'</thead>'
									.				'<tbody>';

			for ( $i = 0; $i < count( $rows ); $i++ ) {
				$row				=	$rows[$i];

				$return				.=					'<tr>'
									.						'<td style="width: 20%;" class="text-left">' . CBuser::getInstance( (int) $row->reporteduser, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.						'<td style="width: 20%;" class="text-left">' . $row->reportexplaination . '</td>'
									.						'<td style="width: 20%;" class="text-left xs-hidden">' . cbFormatDate( $row->reportedondate ) . '</td>'
									.						'<td style="width: 20%;" class="text-left xs-hidden">' . CBuser::getInstance( (int) $row->reportedbyuser, false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</td>'
									.						'<td style="width: 20%;" class="text-left ' . ( $row->reportedstatus ? 'text-success' : 'text-danger' ) . '">' . ( $row->reportedstatus ? CBTxt::Th( 'UE_REPORTSTATUS_PROCESSED', 'Processed' ) : CBTxt::Th( 'UE_REPORTSTATUS_OPEN', 'Open' ) ) . '</td>'
									.					'</tr>';
			}

			$return					.=				'</tbody>'
									.			'</table>'
									.			'<div class="form-group cb_form_line clearfix">'
									.				'<input type="submit" class="btn btn-primary cbViewReportsMod" value="' . htmlspecialchars( CBTxt::Th( 'UE_USERREPORT_MODERATE', 'Moderate User Reports' ) ) . '" />'
									.			'</div>'
									.		'</form>';

			if ( $total > $limit ) {
				$return				.=		'<div class="form-group cb_form_line text-center clearfix">'
									.			writePagesLinks( $limitstart, $limit, $total, $_CB_framework->viewUrl( 'viewreports' ) )
									.		'</div>';
			}
		}

		$return						.=	'</div>';

		echo $return;
}
