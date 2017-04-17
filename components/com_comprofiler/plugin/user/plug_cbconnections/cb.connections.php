<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Language\CBTxt;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) {	die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'userDeleted', 'getConnectionTab' );

/**
 * Connections Tab Class for handling the Shortest Connections Path CB tab in head by default (other parts are still in core CB)
 * @package Community Builder
 * @subpackage Connections CB core module
 * @author JoomlaJoe and Beat
 */
class getConnectionPathsTab extends cbTabHandler
{
	/**
	 * Constructor
	 */
	public function __construct( )
	{
		parent::__construct();
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database, $ueConfig;

		$return								=	null;

		if ( ( $_CB_framework->myId() != $user->id ) && ( $_CB_framework->myId() > 0 ) && ( isset( $ueConfig['connectionPath'] ) && $ueConfig['connectionPath'] ) && $ueConfig['allowConnections'] ) {
			$myCBUser						=	CBuser::getInstance( (int) $user->id, false );
			$myName							=	$myCBUser->getField( 'formatname', null, 'html', 'none', 'profile', 0, true );
			$myAvatar						=	$myCBUser->getField( 'avatar', null, 'html', 'none', 'profile', 0, true, array( '_hideApproval' => 1 ) );

			$i								=	0;
			$cbCon							=	new cbConnection( $_CB_framework->myId() );
			$conGroups						=	$cbCon->getDegreeOfSepPath( $_CB_framework->myId(), $user->id );
			$directConDetails				=	$cbCon->getConnectionDetails( $_CB_framework->myId(), $user->id );

			$addConnURL						=	$_CB_framework->viewUrl( 'addconnection', true, array( 'connectionid' => (int) $user->id ) );
			$removeConnURL					=	$_CB_framework->viewUrl( 'removeconnection', true, array( 'connectionid' => (int) $user->id ) );
			$acceptConnURL					=	$_CB_framework->viewUrl( 'acceptconnection', true, array( 'connectionid' => (int) $user->id ) );
			$denyConnURL					=	$_CB_framework->viewUrl( 'denyconnection', true, array( 'connectionid' => (int) $user->id ) );

			if ( $ueConfig['conNotifyType'] != 0 ) {
				cbValidator::loadValidation();

				$tooltipTitle				=	sprintf( CBTxt::T( 'UE_CONNECTTO', 'Connect to %s'), $myName );

				$ooltipHTML					=	'<div class="form-group cb_form_line clearfix">'
											.		CBTxt::Th( 'UE_CONNECTIONINVITATIONMSG', 'Personalize your invitation to connect by adding a message that will be included with your connection.' )
											.	'</div>'
											.	'<form action="' . $addConnURL . '" method="post" id="connOverForm" name="connOverForm" class="cb_form cbValidation">'
											.		'<div class="form-group cb_form_line clearfix">'
											.			'<label for="message" class="control-label">' . CBTxt::T( 'UE_MESSAGE', 'Message' ) . '</label>'
											.			'<div class="cb_field">'
											.				'<textarea cols="40" rows="8" name="message" class="form-control"></textarea>'
											.			'</div>'
											.		'</div>'
											.		'<div class="form-group cb_form_line clearfix">'
											.			'<input type="submit" class="btn btn-primary cbConnReqSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_SENDCONNECTIONREQUEST', 'Request Connection' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
											.			' <input type="button" id="cbConnReqCancel" class="btn btn-default cbConnReqCancel cbTooltipClose" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCELCONNECTIONREQUEST', 'Cancel' ) ) . '" />'
											.		'</div>'
											.	'</form>';

				$tooltip					=	cbTooltip( $ui, $ooltipHTML, $tooltipTitle, 400, null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
			} else {
				$tooltip					=	null;
			}

			$connected						=	'<div class="cbConnectionPaths alert alert-info">'
											.		CBTxt::Th( 'CONNECTIONS_YOU_ARE_DIRECTLY_CONNECTED_WITH_USER', 'You are directly connected with [user]', array( '[user]' => $myAvatar ) )
											.	'</div>';

			$requestConnection				=	'<div class="cbConnectionPaths alert alert-info clearfix">'
											.		'<div class="cbConnPathMessage col-sm-8">'
											.			CBTxt::Th( 'CONNECTIONS_YOU_HAVE_NO_CONNECTION_WITH_USER', 'You have no established connection with [user]', array( '[user]' => $myAvatar ) )
											.		'</div>'
											.		'<div class="cbConnPathActions col-sm-4 text-right">'
											.			'<input type="button" value="' . htmlspecialchars( CBTxt::Th( 'Request Connection' ) ) . '" class="btn btn-success cbConnPathAccept"' . ( $tooltip ? ' ' . $tooltip : ' onclick="location.href = \'' . addslashes( $addConnURL ) . '\';"' ) . ' />'
											.		'</div>'
											.	'</div>';

			$cancelRequest					=	'<div class="cbConnectionPaths alert alert-info clearfix">'
											.		'<div class="cbConnPathMessage col-sm-8">'
											.			CBTxt::Th( 'CONNECTIONS_YOUR_CONNECTION_REQUEST_WITH_USER_IS_PENDING', 'Your connection request with [user] is pending acceptance', array( '[user]' => $myAvatar ) )
											.		'</div>'
											.		'<div class="cbConnPathActions col-sm-4 text-right">'
											.			'<input type="button" value="' . htmlspecialchars( CBTxt::Th( 'Cancel Request' ) ) . '" class="btn btn-danger cbConnPathReject" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'UE_CONFIRMREMOVECONNECTION', 'Are you sure you want to remove this connection?' ) ) . '\' ) ) { location.href = \'' . addslashes( $removeConnURL ) . '\'; } else { return false }" />'
											.		'</div>'
											.	'</div>';

			$acceptDenyRequest				=	'<div class="cbConnectionPaths alert alert-info clearfix">'
											.		'<div class="cbConnPathMessage col-sm-8">'
											.			CBTxt::Th( 'CONNECTIONS_THE_CONNECTION_WITH_USER_IS_PENDING_YOUR_ACCEPTANCE', 'The connection with [user] is pending your acceptance', array( '[user]' => $myAvatar ) )
											.		'</div>'
											.		'<div class="cbConnPathActions col-sm-4 text-right">'
											.			'<input type="button" value="' . htmlspecialchars( CBTxt::Th( 'Accept' ) ) . '" class="btn btn-success cbConnPathAccept" onclick="location.href = \'' . addslashes( $acceptConnURL ) . '\';" />'
											.			' <input type="button" value="' . htmlspecialchars( CBTxt::Th( 'Reject' ) ) . '" class="btn btn-danger cbConnPathReject" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'UE_CONFIRMREMOVECONNECTION', 'Are you sure you want to remove this connection?' ) ) . '\' ) ) { location.href = \'' . addslashes( $denyConnURL ) . '\'; } else { return false }" />'
											.		'</div>'
											.	'</div>';

			$return							.=	$this->_writeTabDescription( $tab, $user );

			if ( is_array( $conGroups ) && ( count( $conGroups ) > 2 ) ) {
				cbArrayToInts( $conGroups );

				$query						=	"SELECT u.name, u.email, u.username, c.avatar, c.avatarapproved, u.id "
											.	"\n FROM #__comprofiler AS c"
											.	"\n LEFT JOIN #__users AS u ON c.id=u.id"
											.	"\n WHERE c.id IN (" . implode( ',', $conGroups ) . ")"
											.	"\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0";
				$_CB_database->setQuery( $query );
				$connections				=	$_CB_database->loadObjectList( 'id' );

				$prevConID					=	null;
				$prevConName				=	null;

				if ( isset( $connections[$user->id] ) ) {
					$return					.=	'<div class="cbConnectionPaths alert alert-info">'
											.		CBTxt::Th( 'CONNECTIONS_YOUR_CONNECTION_PATH_TO_USER_OF_DEGREE_IS', 'Your connection path to [user] of [degrees] degrees is ', array( '[user]' => $myAvatar, '[degrees]' => $cbCon->getDegreeOfSep() ) );

					foreach ( $conGroups as $conGroup ) {
						$cbUser				=	CBuser::getInstance( (int) $conGroup );

						if ( ! $cbUser ) {
							$cbUser			=	CBuser::getInstance( null );
						}

						if ( $i != 0 ) {
							$return			.=	' <span class="fa fa-chevron-right fa-sm"></span> ';
						}

						$conName			=	$cbUser->getField( 'formatname', null, 'html', 'none', 'profile', 0, true );
						$conAvatar			=	$cbUser->getField( 'avatar', null, 'html', 'none', 'profile', 0, true, array( '_hideApproval' => 1 ) );

						if ( ( $conGroup != $_CB_framework->myId() ) && ( isset( $connections[$conGroup] ) ) ) {
							$conDetail		=	$cbCon->getConnectionDetails( $prevConID, $conGroup );

							$tipField		=	getConnectionTab::renderConnectionToolTip( $conDetail );
							$tipField		.=	'<div style="text-align: center; margin: 8px;">'
											.		$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true )
											.	'</div>';


							$tipTitle		=	$prevConName . CBTxt::T( 'UE_DETAILSABOUT', ' Details About [PERSON_NAME]', array( '[PERSON_NAME]' => htmlspecialchars( $conName ) ) );

							if ( $conGroup != $user->id ) {
								$href		=	$_CB_framework->userProfileUrl( (int) $conGroup );
							} else {
								$href		=	null;
							}

							$return			.=	cbTooltip( $ui, $tipField, $tipTitle, 300, null, $conAvatar, $href );
						} else {
							$return			.=	$conAvatar;
						}

						$i++;

						$prevConID			=	$conGroup;
						$prevConName		=	$conName;
					}

					$return					.=	'</div>';

					if ( $directConDetails !== false && $directConDetails->pending ) {
						$return				.=	$cancelRequest;
					} elseif ( ( $directConDetails !== false ) && ( ! $directConDetails->accepted ) ) {
						$return				.=	$acceptDenyRequest;
					} elseif ( $directConDetails === false ) {
						$return				.=	$requestConnection;
					}
				} else {
					$return					.=	$requestConnection;
				}
			} elseif ( is_array( $conGroups ) && ( count( $conGroups ) == 2 ) ) {
				$return						.=	$connected;
			} else {
				if ( ( $directConDetails !== false ) && $directConDetails->pending ) {
					$return					.=	$cancelRequest;
				} elseif ( ( $directConDetails !== false ) && ( ! $directConDetails->accepted ) ) {
					$return					.=	$acceptDenyRequest;
				} else {
					$return					.=	$requestConnection;
				}
			}
		}

		return $return;
	}
} // end class getConnectionPathsTab


/**
 * Connections Tab Class for handling the Connections List CB tab (other parts are still in core CB)
 * @package Community Builder
 * @subpackage Connections CB core module
 * @author JoomlaJoe and Beat
 */
class getConnectionTab extends cbTabHandler
{
	/**
	 * Constructor
	 */
	public function __construct( )
	{
		parent::__construct();
	}

	protected function _getUserNumberOfConnections( $user )
	{
		global $_CB_framework;
		
		$cbCon							=	new cbConnection( $_CB_framework->myId() );
		return $cbCon->getConnectionsCount( $user->id );
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database, $ueConfig;

		$return										=	null;

		if ( ! $ueConfig['allowConnections'] || ( ( isset( $ueConfig['connectionDisplay'] ) && ( $ueConfig['connectionDisplay'] == 1 ) ) && ( $_CB_framework->myId() != $user->id ) ) ) {
			return null;
		}

		$js											=	"if ( typeof confirmSubmit != 'function' ) {"
													.		"function confirmSubmit() {"
													.			"if ( confirm( '" . addslashes( CBTxt::T( 'UE_CONFIRMREMOVECONNECTION', 'Are you sure you want to remove this connection?' ) ) . "' ) ) {"
													.				"return true;"
													.			"} else {"
													.				"return false;"
													.			"}"
													.		"};"
													.	"}";

		$_CB_framework->document->addHeadScriptDeclaration( $js );

		$params										=	$this->params;
		$con_ShowSummary							=	$params->get( 'con_ShowSummary', '0' );
		$con_SummaryEntries							=	$params->get( 'con_SummaryEntries', '4' );
		$con_pagingenabled							=	$params->get( 'con_PagingEnabled', '1' );
		$con_entriesperpage							=	$params->get( 'con_EntriesPerPage', '10' );

		$pagingParams								=	$this->_getPaging( array(), array( 'connshow_' ) );

		$showall									=	$this->_getReqParam( "showall", false );

		if ( $con_ShowSummary && ! $showall && ( $pagingParams['connshow_limitstart'] === null ) ) {
			$summaryMode							=	true;
			$showpaging								=	false;
			$con_entriesperpage						=	$con_SummaryEntries;
		} else {
			$summaryMode							=	false;
			$showpaging								=	$con_pagingenabled;
		}

		$isVisitor									=	null;

		if ( $_CB_framework->myId() != $user->id ) {
			$isVisitor								=	"\n AND m.pending=0 AND m.accepted=1";
		}

		if ( $showpaging || $summaryMode ) {
			//select a count of all applicable entries for pagination
			if ( $isVisitor ) {
				$contotal							=	$this->_getUserNumberOfConnections( $user );
			} else {
				$query								=	"SELECT COUNT(*)"
													.	"\n FROM #__comprofiler_members AS m"
													.	"\n LEFT JOIN #__comprofiler AS c ON m.memberid=c.id"
													.	"\n LEFT JOIN #__users AS u ON m.memberid=u.id"
													.	"\n WHERE m.referenceid=" . (int) $user->id
													.	"\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
													.	$isVisitor;
				
				$_CB_database->setQuery( $query );
				$contotal							=	$_CB_database->loadResult();
				
				if ( ! is_numeric( $contotal ) )
					$contotal						=	0;
			}
		} else {
			$contotal								=	0;
		}

		if ( ( ! $showpaging ) || ( $pagingParams['connshow_limitstart'] === null ) || ( $con_entriesperpage > $contotal ) ) {
			$pagingParams['connshow_limitstart']	=	0;
		}

		$query										=	"SELECT m.*,u.name,u.email,u.username,c.avatar,c.avatarapproved, u.id"
													.	"\n FROM #__comprofiler_members AS m"
													.	"\n LEFT JOIN #__comprofiler AS c ON m.memberid=c.id"
													.	"\n LEFT JOIN #__users AS u ON m.memberid=u.id"
													.	"\n WHERE m.referenceid=" . (int) $user->id . ""
													.	"\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
													.	$isVisitor
													.	"\n ORDER BY m.membersince DESC, m.memberid ASC";

		$_CB_database->setQuery( $query, (int) ( $pagingParams['connshow_limitstart'] ? $pagingParams['connshow_limitstart'] : 0 ), (int) $con_entriesperpage );
		$connections								=	$_CB_database->loadObjectList();

		if ( ! count( $connections ) > 0 ) {
			$return									.=	CBTxt::Th( 'UE_NOCONNECTIONS', 'This user has no current connections.' );

			return $return;
		}

		$return										.=	$this->_writeTabDescription( $tab, $user );

		foreach ( $connections as $connection ) {
			$cbUser									=	CBuser::getInstance( (int) $connection->id );

			if ( ! $cbUser ) {
				$cbUser								=	CBuser::getInstance( null );
			}

			$tipField								=	getConnectionTab::renderConnectionToolTip( $connection );
			$tipTitle								=	CBTxt::T( 'UE_CONNECTEDDETAIL', 'Connection Details');
			$htmlText								=	$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true );
			$tooltip								=	cbTooltip( 1, $tipField, $tipTitle, 300, null, $htmlText, null, 'style="display: inline-block; padding: 5px;"' );

			if ( ( $connection->accepted == 1 ) && ( $connection->pending == 1 ) ) {
				$actionImg							=	'<span class="fa fa-clock-o fa-lg" title="' . htmlspecialchars( CBTxt::T( 'UE_CONNECTIONPENDING', 'Connection Pending' ) ) . '"></span>'
													.	' <a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'connections', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
													.		'<span class="fa fa-times-circle-o fa-lg" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION_DESC', 'Remove Connection to that user' ) ) . '"></span>'
													.	'</a>';
			} elseif ( ( $connection->accepted == 1 ) && ( $connection->pending == 0 ) ) {
				$actionImg							=	'<a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'connections', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
													.		'<span class="fa fa-times-circle-o fa-lg" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
													.	'</a>';
			} elseif ( $connection->accepted == 0 ) {
				$actionImg							=	'<a href="' . $_CB_framework->viewUrl( 'acceptconnection', true, array( 'act' => 'connections', 'connectionid' => (int) $connection->memberid ) ) . '" >'
													.		'<span class="fa fa-check-circle-o fa-lg" title="' . htmlspecialchars( CBTxt::T( 'UE_ACCEPTCONNECTION', 'Accept Connection' ) ) . '"></span>'
													.	'</a>'
													.	' <a href="' . $_CB_framework->viewUrl( 'removeconnection', true, array( 'act' => 'connections', 'connectionid' => (int) $connection->memberid ) ) . '" onclick="return confirmSubmit();" >'
													.		'<span class="fa fa-times-circle-o fa-lg" title="' . htmlspecialchars( CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' ) ) . '"></span>'
													.	'</a>';
			} else {
				$actionImg							=	null;
			}

			if ( $_CB_framework->myId() == $user->id ) {
				$return								.=	'<div class="containerBox img-thumbnail">'
													.		'<div class="containerBoxInner" style="min-height: 130px; min-width: 90px;">'
													.			$actionImg . '<br />'
													.			$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
													.			' ' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true )
													.			'<br />' . $tooltip . '<br />'
													.			' <a href="' . $_CB_framework->userProfileUrl( (int) $connection->memberid ) . '">'
													.				'<span class="fa fa-user" title="' . htmlspecialchars( CBTxt::T( 'UE_VIEWPROFILE', 'View Profile' ) ) . '"></span>'
													.			'</a>'
													.			' ' . $cbUser->getField( 'email', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
													.			' ' . $cbUser->getField( 'pm', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
													.		'</div>'
													.	'</div>';
			} else {
				$return								.=	'<div class="containerBox img-thumbnail">'
													.		'<div class="containerBoxInner" style="min-height: 100px; min-width: 90px;">'
													.			$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'profile', 0, true, array( '_imgMode' => 1 ) )
													.			' ' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true )
													.			'<br />'
													.			$tooltip
													.		'</div>'
													.	'</div>';
			}
		}

		$return										.=	'<div class="clearfix"></div>';

		// Add paging control at end of list if paging enabled
		if ( $showpaging && ( $con_entriesperpage < $contotal ) ) {
			$return									.=	'<div class="text-center">'
													.		$this->_writePaging( $pagingParams, 'connshow_', $con_entriesperpage, $contotal )
													.	'</div>';
		}

		if ( $con_ShowSummary && ( $_CB_framework->myId() == $user->id ) || ( $summaryMode && ( $con_entriesperpage < $contotal ) ) ) {
			$return									.=	'<div class="connSummaryFooter clearfix">';

			if ( $_CB_framework->myId() == $user->id ) {
				// Manage connections link:
				$return								.=		'<div id="connSummaryFooterManage" class="pull-left">'
													.			'<a href="' . $_CB_framework->viewUrl( 'manageconnections' ) . '" >[' . CBTxt::Th( 'UE_MANAGECONNECTIONS', 'Manage Connections' ) . ']</a>'
													.		'</div>';
			}

			if ( $summaryMode && ( $con_entriesperpage < $contotal ) ) {
				// See all of user's ## connections
				$return								.=		'<div id="connSummaryFooterSeeConnections" class="pull-right">'
													.			'<a href="' . $this->_getAbsURLwithParam( array( 'showall' => 1 ) ) . '">';

				if ( $_CB_framework->myId() == $user->id ) {
					$return							.=				sprintf( CBTxt::Th( 'UE_SEEALLNCONNECTIONS', 'See all %s connections' ), $contotal );
				} else {
					$return							.=				sprintf( CBTxt::Th( 'UE_SEEALLOFUSERSNCONNECTIONS', 'See all of %s\'s %s connections' ), getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ), "<strong>" . $contotal . "</strong>" );
				}

				$return								.=			'</a>'
													.		'</div>';
			}

			$return									.=	'</div>';
		}

		return $return;
	}

	/**
	 * Renders the tooltip for a connection
	 *
	 * @param  \CB\Database\Table\MemberTable  $connection  Connection to render field tip for
	 * @return string                                       HTML for the description of the connection
	 */
	public static function renderConnectionToolTip( $connection )
	{
		$tipField		=	CBTxt::Th( 'CONNECTION_TIP_CONNECTED_SINCE_CONNECTION_DATE', 'Connected Since [CONNECTION_DATE]', array( '[CONNECTION_DATE]' => cbFormatDate( $connection->membersince ) ) );

		if ( $connection->type != null ) {
			$tipField	.=	'<br />' . CBTxt::Th( 'CONNECTION_TIP_TYPES_LIST', '{1} Type: [CONNECTIONS_TYPES]|]1,Inf] Types: [CONNECTIONS_TYPES]|%%COUNT%%', array( '%%COUNT%%' => count( explode( "|*|", $connection->type ) ), '[CONNECTIONS_TYPES]' => getConnectionTypes( $connection->type ) ) );
		}

		if ( $connection->description != null ) {
			$tipField	.=	'<br />' . CBTxt::Th( 'CONNECTION_TIP_CONNECTION_COMMENT', 'Comment: [CONNECTION_DESCRIPTION]', array( '[CONNECTION_DESCRIPTION]' => htmlspecialchars( $connection->description ) ) );
		}
		return $tipField;
	}

	/**
	 * UserBot Called when a user is deleted from backend (prepare future unregistration)
	 * @param  UserTable  $user     reflecting the user being deleted
	 * @param  int        $success  1 for successful deleting
	 * @return boolean              true if all is ok, or false if ErrorMSG generated
	 */
	public function userDeleted( $user, /** @noinspection PhpUnusedParameterInspection */ $success )
	{
		global $_CB_database, $ueConfig;
		$sql		=	"DELETE FROM #__comprofiler_members WHERE referenceid = " . (int) $user->id;
		$_CB_database->SetQuery( $sql );

		try {
			$_CB_database->query();
		}
		catch ( RuntimeException $e ) {
			$this->_setErrorMSG( "SQL error cb.connections:userDelted-1" . $e->getMessage() );
			return false;
		}
		
		if ( $ueConfig['autoAddConnections'] ) {
			$sql	=	"DELETE FROM #__comprofiler_members WHERE memberid = " . (int) $user->id;
			$_CB_database->SetQuery( $sql );

			try {
				$_CB_database->query();
			}
			catch ( RuntimeException $e ) {
				$this->_setErrorMSG( "SQL error cb.connections:userDelted-1" . $e->getMessage() );
				return false;
			}

		}
		return true;
	}
} // end class getConnectionTab.
