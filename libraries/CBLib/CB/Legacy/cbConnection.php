<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 11:36 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\MemberTable;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbConnection Class implementation
 * Connections Class for handling CB connections
 */
class cbConnection
{
	/**
	 * Error message when an error is encountered
	 * @var string
	 */
	protected $errorMSG;
	/**
	 * Userid related to base user of the connection action
	 * @var int
	 */
	protected $referenceId;
	/**
	 * Distance between referenceid and connectionid
	 * @var int
	 */
	protected $degreeOfSep;
	/**
	 * Message that needs to be returned to the user
	 * @var string
	 */
	protected $userMSG;

	/**
	 * Constructor
	 *
	 * @param  int  $referenceId  Target user of the connection
	 */
	public function __construct( $referenceId )
	{
		$this->referenceId	=	$referenceId;
	}

	/**
	 * Adds a non-existing connection (request) between $this->referenceId and $connectionId with message $messageToNewConnection
	 *
	 * @param  int      $connectionId            Target user id
	 * @param  string   $messageToNewConnection  Message to target user
	 * @param  boolean  $notifyNewConnection     [optional, default true] Notify to the other contact the new connection
	 * @return boolean                           Success
	 */
	public function addConnection( $connectionId, $messageToNewConnection = null, $notifyNewConnection = true )
	{
		global $ueConfig, $_PLUGINS;

		$existingConnection		=	$this->getConnectionDetails( $this->referenceId , $connectionId );

		if ( $existingConnection !== false ) {
			return false;
		}

		$_PLUGINS->loadPluginGroup('user');
		$_PLUGINS->trigger( 'onBeforeAddConnection', array( $this->referenceId,$connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'], &$messageToNewConnection ) );
		if( $_PLUGINS->is_errors() ) {
			$this->_setUserMSG( $_PLUGINS->getErrorMSG() );
			return false;
		}

		if( ! $this->addConnectionToDatabase( $this->referenceId, $connectionId, $messageToNewConnection ) ) {
			$this->_setUserMSG( $this->getErrorMSG() );
			return false;
		}

		if ( $notifyNewConnection ) {
			if( $ueConfig['useMutualConnections'] ) {
				$msg			=	CBTxt::T( 'UE_CONNECTIONPENDINGACCEPTANCE', 'Connection Pending Acceptance!' );
				$subject		=	CBTxt::T( 'UE_CONNECTIONPENDSUB', 'You have a pending connection from %s!' );
				$messageHTML	=	CBTxt::Th( 'UE_CONNECTIONPENDMSG', '%s is requesting to connect with you and requires your approval.  Please accept or deny the connection request accordingly.' );
			} else {
				$msg			=	CBTxt::T( 'UE_CONNECTIONADDSUCCESSFULL', 'Connection Successfully Added!' );
				$subject		=	CBTxt::T( 'UE_CONNECTIONMADESUB', '%s has connected with you!' );
				$messageHTML	=	CBTxt::Th( 'UE_CONNECTIONMADEMSG', '%s has established a connection with you.' );
			}
			$messageText		=	$messageHTML;

			$result				=	$this->_notifyConnectionChange( $this->referenceId, $connectionId, $msg, $subject, $messageHTML, $messageText, $messageToNewConnection );
		} else {
			$result				=	true;
		}

		$_PLUGINS->trigger( 'onAfterAddConnection', array( $this->referenceId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );

		return $result;
	}

	/**
	 * Notifies connection changes
	 *
	 * @param  int      $userId
	 * @param  int      $connectionId
	 * @param  string   $msg
	 * @param  string   $subject
	 * @param  string   $messageHTML
	 * @param  string   $messageText
	 * @param  string   $userMessage
	 * @return boolean
	 */
	protected function _notifyConnectionChange( $userId, $connectionId, $msg, $subject, $messageHTML, $messageText, $userMessage = null )
	{
		global $_CB_framework, $ueConfig;

		$rowFrom				=	new UserTable();
		$rowFrom->load( (int) $userId );

		$fromName				=	getNameFormat( $rowFrom->name, $rowFrom->username, $ueConfig['name_format'] );
		$fromURL				=	'index.php?option=com_comprofiler&amp;view=userprofile&amp;user=' . $userId . '&amp;tab=1' . getCBprofileItemid(true);
		$fromURL				=	cbSef( $fromURL );

		if ( strncasecmp( 'http', $fromURL, 4 ) != 0 ) {
			$fromURL			=	$_CB_framework->getCfg( 'live_site' ) . '/' . $fromURL;
		}

		$subject				=	sprintf( $subject, $fromName );

		if ( $userMessage != null ) {
			$messageHTML		.=	sprintf( str_replace( "\n", "\n<br />", CBTxt::T( 'UE_CONNECTIONMSGPREFIX', "  %s included the following personal message:\n\n%s" ) ),
											 htmlspecialchars( $fromName ),
											 '<strong>' . htmlspecialchars( $userMessage ) . '</strong>' );
			$messageText		.=	sprintf( str_replace( "\n", "\r\n", CBTxt::T( 'UE_CONNECTIONMSGPREFIX', "  %s included the following personal message:\n\n%s" ) ),
											 $fromName,
											 $userMessage );
		}

		$notificationMsgHTML	=	sprintf( $messageHTML, '<strong><a href="' . $fromURL . '">' . htmlspecialchars( $fromName ) . '</a></strong>' );
		$notificationMsgText	=	sprintf( $messageText, $fromName );

		$manageURL				=	'index.php?option=com_comprofiler&amp;view=manageconnections' . getCBprofileItemid( true );
		$manageURL				=	cbSef( $manageURL );

		if ( strncasecmp( 'http', $manageURL, 4 ) != 0 ) {
			$manageURL			=	$_CB_framework->getCfg( 'live_site' ) . '/' . $manageURL;
		}

		$notificationMsgHTML	=	$notificationMsgHTML
								.	"\n<br /><br /><a href=\"" . $manageURL . '">'
								.	CBTxt::T( 'UE_MANAGECONNECTIONS_LINK UE_MANAGECONNECTIONS', 'Manage Connections' )
								.	"</a>\n";

		$notificationMsgText	=	$notificationMsgText
								.	"\r\n\r\n\r\n" . $fromName . ' '
								.	CBTxt::T( 'CONNECTION_PROFILE UE_PROFILE', 'Profile' )
								.	': '
								.	cbUnHtmlspecialchars( $fromURL );

		$notificationMsgText	=	$notificationMsgText
								.	"\r\n\r\n"
								.	CBTxt::T( 'UE_MANAGECONNECTIONS_URL_LABEL UE_MANAGECONNECTIONS', 'Manage Connections' )
								.	': '
								.	cbUnHtmlspecialchars( $manageURL )
								.	"\r\n";

		$notificationMsgHTML	=	'<div style="padding: 4px; margin: 4px 3px 6px 0px; background: #C44; font-weight: bold;" class="cbNotice">'
			. CBTxt::T( 'UE_SENDPMSNOTICE', 'NOTE: This is a message generated automatically by the Connections system. It has the connecting user\'s address, so you can conveniently reply if you wish to.' )
			. "</div>\n\n"
			. $notificationMsgHTML;

		$cbNotification			=	new cbNotification();
		$cbNotification->sendFromUser( $connectionId, $userId, $subject, $notificationMsgHTML, $notificationMsgText );

		$this->_setUserMSG( $msg );

		return true;
	}

	/**
	 * Inserts a connection from $referenceId to $connectionId with a $userMessage for connection requests
	 * (needs to be public for backwards compatibility with CB plugins autoactions and cbinvites)
	 *
	 * @deprecated 2.0 use cbConnection::addConnection() instead (it now has a new parameter to notify or not, and it also triggers events)
	 * @see \cbConnection::addConnection()
	 *
	 * @param  int      $referenceId
	 * @param  int      $connectionId
	 * @param  string   $userMessage
	 * @return boolean                 TRUE (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function _insertConnection( $referenceId, $connectionId, $userMessage )
	{
		$oldReferenceId		=	$this->referenceId;
		$this->referenceId	=	$referenceId;

		$result				=	$this->addConnection( $connectionId, $userMessage, false );

		$this->referenceId	=	$oldReferenceId;

		return $result;
	}

	/**
	 * Inserts a connection from $referenceId to $connectionId with a $userMessage for connection requests
	 *
	 * @param  int      $referenceId
	 * @param  int      $connectionId
	 * @param  string   $userMessage
	 * @return boolean                 TRUE (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	protected function addConnectionToDatabase( $referenceId, $connectionId, $userMessage )
	{
		global $_CB_database, $ueConfig;

		$accepted			=	1;
		$pending			=	0;

		if( $ueConfig['useMutualConnections'] ) {
			$accepted		=	1;
			$pending		=	1;
		}

		$sql				=	'INSERT INTO #__comprofiler_members (referenceid, memberid, accepted, pending, membersince, reason) VALUES ('
							.		(int) $referenceId . ', '
							.		(int) $connectionId . ', '
							.		(int) $accepted . ', '
							.		(int) $pending . ', '
							.		'CURDATE(), '
							.		$_CB_database->Quote( $userMessage )
							.	')';

		$_CB_database->SetQuery( $sql );

		$_CB_database->query();		// throws Exception on database error

		if( $ueConfig['autoAddConnections'] ) {
			$accepted		=	1;
			$pending		=	0;

			if( $ueConfig['useMutualConnections'] ) {
				$accepted	=	0;
				$pending	=	0;
			}

			$sql			=	'INSERT INTO #__comprofiler_members (referenceid, memberid, accepted, pending, membersince, reason) VALUES ('
							.		(int) $connectionId . ', '
							.		(int) $referenceId . ', '
							.		(int) $accepted . ', '
							.		(int) $pending . ', '
							.		'CURDATE(), '
							.		$_CB_database->Quote( $userMessage )
							.	')';

			$_CB_database->SetQuery( $sql );
			$_CB_database->query();		// throws Exception on database error
		}

		return true;
	}

	/**
	 * Removes a connection $connectionId of user $userId
	 *
	 * @param  int      $userId                   User id
	 * @param  int      $connectionId             Connection id
	 * @param  boolean  $notifyDroppedConnection  [optional, default false] Notify to the other contact the new connection
	 * @return boolean                            Result (throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function removeConnection( $userId, $connectionId, $notifyDroppedConnection = false )
	{
		global $ueConfig, $_PLUGINS;

		if ( $this->getConnectionDetails( $userId, $connectionId ) === false ) {
			$this->_setErrorMSG( CBTxt::T( 'UE_NODIRECTCONNECTION', 'There is no direct connection.' ) );
			return false;
		}

		$_PLUGINS->loadPluginGroup('user');
		$_PLUGINS->trigger( 'onBeforeRemoveConnection', array($userId,$connectionId,$ueConfig['useMutualConnections'],$ueConfig['autoAddConnections']));
		if ( $_PLUGINS->is_errors() ) {
			$this->_setUserMSG( $_PLUGINS->getErrorMSG() );
			return false;
		}

		$result		=	$this->_deleteConnection( $userId, $connectionId );

		$msg		=	CBTxt::Th( 'UE_CONNECTIONREMOVESUCCESSFULL', 'Connection Successfully Removed!' );

		if ( $notifyDroppedConnection ) {
			$subject = CBTxt::T( 'UE_CONNECTIONREMOVED_SUB', 'Connection Removed!' );
			$messageHTML = CBTxt::T( 'UE_CONNECTIONREMOVED_MSG', '%s has removed your connection!' );
			$messageText = $messageHTML;
			$result = $this->_notifyConnectionChange( $userId, $connectionId, $msg, $subject, $messageHTML, $messageText);
		}

		$this->_setUserMSG( $msg );

		$_PLUGINS->trigger( 'onAfterRemoveConnection', array( $userId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );

		return $result;
	}

	/**
	 * Denies a connection request $connectionId for user $userId
	 *
	 * @param  int      $userId                  User id
	 * @param  int      $connectionId            Connection id
	 * @param  boolean  $notifyDeniedConnection  [optional, default false] Notify to the other contact the new connection
	 * @return boolean                           Result (throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function denyConnection( $userId, $connectionId, $notifyDeniedConnection = false ) {			//BB needs to be called+do different then remove (one way if ...?)
		global $ueConfig, $_PLUGINS;

		if ( $this->getConnectionDetails( $connectionId, $userId ) === false ) {
			$this->_setErrorMSG( CBTxt::T( 'UE_NODIRECTCONNECTION', 'There is no direct connection.' ) );
			return false;
		}

		$_PLUGINS->loadPluginGroup( 'user' );
		$_PLUGINS->trigger( 'onBeforeDenyConnection', array( $userId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );
		if($_PLUGINS->is_errors()) {
			$this->_setUserMSG( $_PLUGINS->getErrorMSG() );
			return false;
		}

		$result		=	$this->_deleteConnection( $connectionId, $userId );

		$msg		=	CBTxt::Th( 'UE_CONNECTIONDENYSUCCESSFULL', 'Connection Successfully Denied!' );

		if ( $notifyDeniedConnection ) {
			$subject = CBTxt::T( 'UE_CONNECTIONDENIED_SUB', 'Connection Request Declined!' );
			$messageHTML = CBTxt::T( 'UE_CONNECTIONDENIED_MSG', 'Your request to connect with %s was declined!' );
			$messageText = $messageHTML;
			$result = $this->_notifyConnectionChange( $userId, $connectionId, $msg, $subject, $messageHTML, $messageText );
		}

		$this->_setUserMSG( $msg );
		$_PLUGINS->trigger( 'onAfterDenyConnection', array( $userId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );

		return $result;
	}

	/**
	 * Deletes a connection from $referenceId to $connectionId
	 *
	 * @param  int      $referenceId   Reference User id
	 * @param  int      $connectionId  Connection id
	 * @return boolean                 TRUE (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	protected function _deleteConnection( $referenceId, $connectionId )
	{
		global $_CB_database, $ueConfig;

		$sql		=	'DELETE FROM #__comprofiler_members WHERE referenceid=' . (int) $referenceId . ' AND memberid=' . (int) $connectionId;
		$_CB_database->SetQuery( $sql );
		$_CB_database->query();			// throws Exception on database error

		if( $ueConfig['autoAddConnections'] ) {
			$sql	=	'DELETE FROM #__comprofiler_members WHERE referenceid=' . (int) $connectionId . ' AND memberid=' . (int) $referenceId;
			$_CB_database->SetQuery($sql);
			$_CB_database->query();		// throws Exception on database error
		}

		return true;
	}

	/**
	 * Accepts a connection request $connectionId for user $userId
	 *
	 * @param  int      $userId               User id
	 * @param  int      $connectionId         Connection id
	 * @param  boolean  $notifyNewConnection  [optional, default true] Notify to the other contact the new connection
	 * @return boolean                        Result (throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function acceptConnection( $userId, $connectionId, $notifyNewConnection = true )
	{
		global $ueConfig, $_PLUGINS;

		if ( $this->getConnectionDetails( $connectionId, $userId ) === false ) {
			$this->_setErrorMSG( CBTxt::T( 'UE_NODIRECTCONNECTION', 'There is no direct connection.' ) );
			return false;
		}

		$_PLUGINS->loadPluginGroup( 'user' );
		$_PLUGINS->trigger( 'onBeforeAcceptConnection', array( $userId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );
		if( $_PLUGINS->is_errors() ) {
			$this->_setUserMSG( $_PLUGINS->getErrorMSG() );
			return false;
		}

		$this->activateConnectionInDatabase( $userId, $connectionId );

		if ( $notifyNewConnection ) {
			$msg			=	CBTxt::Th( 'UE_CONNECTIONACCEPTSUCCESSFULL', 'Connection Successfully Accepted!' );
			$subject		=	CBTxt::T( 'UE_CONNECTIONACCEPTED_SUB', 'Connection Request Accepted!' );
			$messageHTML	=	CBTxt::T( 'UE_CONNECTIONACCEPTED_MSG', 'Your request to connect with %s was accepted!' );
			$messageText	=	$messageHTML;

			$result			=	$this->_notifyConnectionChange( $userId, $connectionId, $msg, $subject, $messageHTML, $messageText );
		} else {
			$result			=	true;
		}
		$_PLUGINS->trigger( 'onAfterAcceptConnection', array( $userId, $connectionId, $ueConfig['useMutualConnections'], $ueConfig['autoAddConnections'] ) );

		return $result;
	}

	/**
	 * Activates a connection $connectionId for user $userId
	 * (needs to be public for backwards compatibility with CB plugins autoactions and cbinvites)
	 * @deprecated 2.0 use cbConnection::acceptConnection() instead (it now has a new parameter to notify or not, and it also triggers events)
	 * @see cbConnection::acceptConnection()
	 *
	 * @param  int      $userId        User id
	 * @param  int      $connectionId  Connection id
	 * @return boolean                 TRUE (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function _activateConnection( $userId, $connectionId )
	{
		return $this->acceptConnection( $userId, $connectionId, false );
	}

	/**
	 * Updates database to activate a connection $connectionId for user $userId
	 *
	 * @param  int      $userId        User id
	 * @param  int      $connectionId  Connection id
	 * @return boolean                 TRUE (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	protected function activateConnectionInDatabase( $userId, $connectionId )
	{
		global $_CB_database, $ueConfig;

		$sql		=	'UPDATE #__comprofiler_members SET accepted=1, pending=0, membersince=CURDATE() WHERE referenceid=' . (int) $connectionId.' AND memberid=' . (int) $userId;
		$_CB_database->SetQuery( $sql );
		$_CB_database->query();		// throws Exception on database error

		if($ueConfig['autoAddConnections']) {
			$sql	=	'UPDATE #__comprofiler_members SET accepted=1, pending=0, membersince=CURDATE() WHERE referenceid=' . (int) $userId.' AND memberid=' . (int) $connectionId;
			$_CB_database->SetQuery( $sql );
			$_CB_database->query();		// throws Exception on database error
		}

		return true;
	}

	/**
	 * Gets the connections count
	 *
	 * @param  int      $userId                      User to count the connections for
	 * @param  boolean  $countPendingConnectionsToo  Should pending connections be counted too ?
	 * @return int                                   Connections count
	 */
	public function getConnectionsCount( $userId, $countPendingConnectionsToo = false )
	{
		global $_CB_database;

		static $cache			=	array();

		$userId					=	(int) $userId;

		if ( ! isset( $cache[$userId] ) ) {
			//select a count of all applicable entries
			$query				=	'SELECT COUNT(*)'
				. "\n FROM #__comprofiler_members AS m"
				. "\n LEFT JOIN #__comprofiler AS c ON m.memberid = c.id"
				. "\n LEFT JOIN #__users AS u ON m.memberid = u.id"
				. "\n WHERE m.referenceid = " . (int) $userId
				. "\n AND c.approved = 1 AND c.confirmed = 1 AND c.banned = 0 AND u.block = 0"
				. ( $countPendingConnectionsToo ? '' : "\n AND m.pending = 0" )
				. " AND m.accepted = 1"
			;
			$_CB_database->setQuery( $query );
			$cache[$userId]		=	(int) $_CB_database->loadResult();
		}

		return $cache[$userId];
	}

	/**
	 * Gets the pending connections count for user $userId
	 *
	 * @param  int      $userId            User to count the connections for
	 * @return int                         Connections count
	 */
	public function getPendingConnectionsCount( $userId )
	{
		global $_CB_database;

		static $cache			=	array();

		$userId					=	(int) $userId;

		if ( ! isset( $cache[$userId] ) ) {
			//select a count of all applicable entries
			$query				=	"SELECT COUNT(*)"
				. "\n FROM #__comprofiler_members AS m"
				. "\n LEFT JOIN #__comprofiler AS c ON m.referenceid = c.id"
				. "\n LEFT JOIN #__users AS u ON m.referenceid = u.id"
				. "\n WHERE m.memberid = " . (int) $userId
				. "\n AND c.approved = 1 AND c.confirmed = 1 AND c.banned = 0 AND u.block = 0"
				. " AND m.pending = 1"
			;
			$_CB_database->setQuery( $query );
			$cache[$userId]		=	(int) $_CB_database->loadResult();
		}

		return $cache[$userId];
	}

	/**
	 * Gets the pending connections for user $userId
	 *
	 * @param  int  $userId  For user id
	 * @param  int  $offset  Gets from this offset
	 * @param  int  $limit   Maximum count of connections to return
	 * @return array
	 */
	public function getPendingConnections( $userId, $offset = 0, $limit = 200 )
	{
		global $_CB_database;

		$query = "SELECT DISTINCT m.*,u.name,u.email,u.username,c.avatar,c.avatarapproved, u.id, IF(s.session_id=null,0,1) AS 'isOnline' "
			. "\n FROM #__comprofiler_members AS m"
			. "\n LEFT JOIN #__comprofiler AS c ON m.referenceid=c.id"
			. "\n LEFT JOIN #__users AS u ON m.referenceid=u.id"
			. "\n LEFT JOIN #__session AS s ON s.userid=u.id"
			. "\n WHERE m.memberid=". (int) $userId . " AND m.pending=1"
			. "\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
		;
		$_CB_database->setQuery( $query, $offset, $limit );
		$objects	=	$_CB_database->loadObjectList();

		return $objects;
	}

	/**
	 * Gets all active connections from $userId
	 *
	 * @param  int  $userId  For user id
	 * @param  int  $offset  Gets from this offset
	 * @param  int  $limit   Maximum count of connections to return
	 * @return array
	 */
	public function getActiveConnections( $userId, $offset = 0, $limit = 200 )
	{
		global $_CB_database;

		$query = "SELECT DISTINCT m.*,u.name,u.email,u.username,c.avatar,c.avatarapproved, u.id, IF(s.session_id=null,0,1) AS 'isOnline' "
			. "\n FROM #__comprofiler_members AS m"
			. "\n LEFT JOIN #__comprofiler AS c ON m.memberid=c.id"
			. "\n LEFT JOIN #__users AS u ON m.memberid=u.id"
			. "\n LEFT JOIN #__session AS s ON s.userid=u.id"
			. "\n WHERE m.referenceid=". (int) $userId
			. "\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0 AND m.accepted=1"
			. "\n ORDER BY m.accepted "
		;
		$_CB_database->setQuery( $query, $offset, $limit );
		$objects	=	$_CB_database->loadObjectList();

		return $objects;
	}

	/**
	 * Gets all active connections to $userId
	 *
	 * @param  int  $userId  For user id
	 * @param  int  $offset  Gets from this offset
	 * @param  int  $limit   Maximum count of connections to return
	 * @return array
	 */
	public function getConnectedToMe( $userId, $offset = 0, $limit = 200 )
	{
		global $_CB_database;

		$query = "SELECT DISTINCT m.*,u.name,u.email,u.username,c.avatar,c.avatarapproved, u.id, IF(s.session_id=null,0,1) AS 'isOnline' "
			. "\n FROM #__comprofiler_members AS m"
			. "\n LEFT JOIN #__comprofiler AS c ON m.referenceid=c.id"
			. "\n LEFT JOIN #__users AS u ON m.referenceid=u.id"
			. "\n LEFT JOIN #__session AS s ON s.userid=u.id"
			. "\n WHERE m.memberid=". (int) $userId ." AND m.pending=0"
			. "\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
		;
		$_CB_database->setQuery( $query, $offset, $limit );
		$objects	=	$_CB_database->loadObjectList();

		return $objects;
	}

	/**
	 * Saves the connection
	 *
	 * @param  int      $connectionId    Connection id
	 * @param  string   $description     Connection description
	 * @param  string   $connectionType  Connection type
	 * @return boolean                   True (or throws \RuntimeException in case of database error)
	 *
	 * @throws \RuntimeException
	 */
	public function saveConnection( $connectionId, $description = null, $connectionType = null )
	{
		global $_CB_database;

		$sql	=	'UPDATE #__comprofiler_members SET description=' . $_CB_database->Quote( htmlspecialchars( $description ) )
			.	', type=' . $_CB_database->Quote( htmlspecialchars( $connectionType ) )
			.	"\n WHERE referenceid=" . (int) $this->referenceId . " AND memberid=" . (int) $connectionId;

		$_CB_database->SetQuery($sql);
		$_CB_database->query();			// throws Exception on database error

		return true;
	}

	/**
	 * Computes the paths between 2 users (degree of separation) up to 6th degree between two users (directed graph) and returns the paths
	 *
	 * @param  int    $fromUserId  From user
	 * @param  int    $toUserId    To user
	 * @param  int    $limit       Limit the number of paths
	 * @param  int    $degree      Limit to degrees to search
	 * @return array               Connection Paths found
	 */
	public function getDegreeOfSepPathArray( $fromUserId, $toUserId, $limit = 10, $degree = 6 )
	{
		global $_CB_database;

		$fromUserId		= (int) $fromUserId;
		$toUserId		= (int) $toUserId;
		$limit			= (int) $limit;
		$connections	=	array();

		if ( $degree >= 1 ) {
			$sql="SELECT a.referenceid, a.memberid AS d1 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND a.memberid = " . $toUserId;
			$_CB_database->setQuery( $sql );

			$connections	=	$_CB_database->loadRowList();
		}

		if ( ( ! $connections ) && ( $degree >= 2 ) ) {
			$sql="SELECT a.referenceid, a.memberid AS d1,  b.memberid AS d2 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n LEFT JOIN  #__comprofiler_members AS b FORCE INDEX (pamr) ON a.memberid=b.referenceid AND b.accepted=1 AND b.pending=0 "
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND b.memberid = " . $toUserId
				."\n AND b.memberid NOT IN ( " . $fromUserId . ",a.memberid ) "
				// ."\n ORDER BY a.memberid,b.memberid "
			;
			$_CB_database->setQuery( $sql, 0, $limit );

			$connections	=	$_CB_database->loadRowList();
		}

		if ( ( ! $connections ) && ( $degree >= 3 ) ) {
			$sql="SELECT a.referenceid, a.memberid AS d1,  b.memberid AS d2,  c.memberid AS d3 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n LEFT JOIN  #__comprofiler_members AS b FORCE INDEX (pamr) ON a.memberid=b.referenceid AND b.accepted=1 AND b.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS c FORCE INDEX (pamr) ON b.memberid=c.referenceid AND c.accepted=1 AND c.pending=0 "
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND c.memberid = " . $toUserId
				."\n AND b.memberid NOT IN ( " . $fromUserId . ",a.memberid) "
				."\n AND c.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid) "
				// ."\n ORDER BY a.memberid,b.memberid,c.memberid "
			;
			$_CB_database->setQuery( $sql, 0, $limit );

			$connections	=	$_CB_database->loadRowList();
		}

		if ( ( ! $connections ) && ( $degree >= 4 ) ) {
			$sql="SELECT a.referenceid, a.memberid AS d1,  b.memberid AS d2,  c.memberid AS d3,  d.memberid AS d4 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n LEFT JOIN  #__comprofiler_members AS b FORCE INDEX (aprm) ON a.memberid=b.referenceid AND b.accepted=1 AND b.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS c FORCE INDEX (pamr) ON b.memberid=c.referenceid AND c.accepted=1 AND c.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS d FORCE INDEX (pamr) ON c.memberid=d.referenceid AND d.accepted=1 AND d.pending=0 "
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND d.memberid = " . $toUserId
				."\n AND b.memberid NOT IN ( " . $fromUserId . ",a.memberid) "
				."\n AND c.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid) "
				."\n AND d.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid) "
				// ."\n ORDER BY a.memberid,b.memberid,c.memberid,d.memberid "
			;
			$_CB_database->setQuery( $sql, 0 ,$limit );

			$connections	=	$_CB_database->loadRowList();
		}

		if ( ( ! $connections ) && ( $degree >= 5 ) ) {
			$sql="SELECT a.referenceid, a.memberid AS d1,  b.memberid AS d2,  c.memberid AS d3,  d.memberid AS d4,  e.memberid AS d5 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n LEFT JOIN  #__comprofiler_members AS b FORCE INDEX (aprm) ON a.memberid=b.referenceid AND b.accepted=1 AND b.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS c FORCE INDEX (aprm) ON b.memberid=c.referenceid AND c.accepted=1 AND c.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS d FORCE INDEX (pamr) ON c.memberid=d.referenceid AND d.accepted=1 AND d.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS e FORCE INDEX (pamr) ON d.memberid=e.referenceid AND e.accepted=1 AND e.pending=0 "
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND e.memberid = " . $toUserId
				."\n AND b.memberid NOT IN ( " . $fromUserId . ",a.memberid) "
				."\n AND c.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid) "
				."\n AND d.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid) "
				."\n AND e.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid,d.memberid) "
				// ."\n ORDER BY a.memberid,b.memberid,c.memberid,d.memberid,e.memberid "
			;
			$_CB_database->setQuery( $sql, 0, $limit );

			$connections	=	$_CB_database->loadRowList();
		}

		if ( ( ! $connections ) && ( $degree >= 6 ) ) {
			$sql="SELECT a.referenceid, a.memberid AS d1,  b.memberid AS d2,  c.memberid AS d3,  d.memberid AS d4,  e.memberid AS d5,  f.memberid AS d6 "
				."\n FROM `#__comprofiler_members` AS a FORCE INDEX (aprm)"
				."\n LEFT JOIN  #__comprofiler_members AS b FORCE INDEX (aprm) ON a.memberid=b.referenceid AND b.accepted=1 AND b.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS c FORCE INDEX (aprm) ON b.memberid=c.referenceid AND c.accepted=1 AND c.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS d FORCE INDEX (pamr) ON c.memberid=d.referenceid AND d.accepted=1 AND d.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS e FORCE INDEX (pamr) ON d.memberid=e.referenceid AND e.accepted=1 AND e.pending=0 "
				."\n LEFT JOIN  #__comprofiler_members AS f FORCE INDEX (pamr) ON e.memberid=f.referenceid AND f.accepted=1 AND f.pending=0 "
				."\n WHERE a.referenceid = " . $fromUserId . " AND a.accepted=1 AND a.pending=0 AND f.memberid = " . $toUserId
				."\n AND b.memberid NOT IN ( " . $fromUserId . ",a.memberid) "
				."\n AND c.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid) "
				."\n AND d.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid) "
				."\n AND e.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid,d.memberid) "
				."\n AND f.memberid NOT IN ( " . $fromUserId . ",a.memberid,b.memberid,c.memberid,d.memberid,e.memberid) "
				// ."\n ORDER BY a.memberid,b.memberid,c.memberid,d.memberid,e.memberid,f.memberid "
			;
			$_CB_database->setQuery( $sql, 0 , $limit );

			$connections	=	$_CB_database->loadRowList();
		}

		// nothing found up to $degree:
		return $connections;
	}

	/**
	 * Computes the degree of separation up to 6th degree between two users (directed graph) and returns shortest path
	 *
	 * @param  int    $fromUserId  From user
	 * @param  int    $toUserId    To user
	 * @return array               Shortest Connection Paths found
	 */
	public function getDegreeOfSepPath( $fromUserId, $toUserId )
	{
		$connectionGroups	=	$this->getDegreeOfSepPathArray( $fromUserId, $toUserId, 1 );

		if ( is_array( $connectionGroups ) && ( count( $connectionGroups ) > 0 ) ) {
			$this->_setDegreeOfSep( count( $connectionGroups[0] ) - 1 );

			return $connectionGroups[0];
		}

		return null;
	}
	/**
	 * Gets connection details
	 *
	 * @param  int                  $fromUserId  From user id
	 * @param  int                  $toUserId    To user id
	 * @return MemberTable|boolean               Connection object or FALSE if none
	 */
	public function getConnectionDetails( $fromUserId, $toUserId )
	{
		global $_CB_database;

		$query			=	'SELECT * '
			.	"\n FROM #__comprofiler_members AS m"
			.	"\n WHERE m.referenceid=" . (int) $fromUserId . ' AND m.memberid=' . (int) $toUserId
		;

		$_CB_database->setQuery( $query );

		$connections	=	$_CB_database->loadObjectList( null, '\CB\Database\Table\MemberTable', array( &$_CB_database ) );

		if ( count( $connections ) > 0 ) {
			return $connections[0];
		}

		return false;
	}

	/**
	 * Checks if $memberId is connected with member $referenceId
	 *
	 * @param  int      $memberId
	 * @param  int      $referenceId
	 * @return boolean
	 */
	public function isConnected( $memberId, $referenceId = null )
	{
		global $_CB_database;

		if ( ! $referenceId ) {
			$referenceId	=	$this->referenceId;
		}

		$query				=	'SELECT COUNT(*)'
			.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_members' )
			.	"\n WHERE " . $_CB_database->NameQuote( 'referenceid' ) . ' = ' . (int) $referenceId
			.	"\n AND " . $_CB_database->NameQuote( 'memberid' ) . ' = ' . (int) $memberId;

		$_CB_database->setQuery( $query );

		if ( $_CB_database->loadResult() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if connection from $memberId to $referenceId is approved.
	 *
	 * @param  int      $memberId
	 * @param  int      $referenceId
	 * @return boolean
	 */
	public function isConnectionApproved( $memberId, $referenceId = null )
	{
		global $_CB_database;

		if ( ! $referenceId ) {
			$referenceId	=	$this->referenceId;
		}

		$query				=	'SELECT COUNT(*)'
			.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_members' )
			.	"\n WHERE " . $_CB_database->NameQuote( 'referenceid' ) . ' = ' . (int) $referenceId
			.	"\n AND " . $_CB_database->NameQuote( 'memberid' ) . ' = ' . (int) $memberId
			.	"\n AND " . $_CB_database->NameQuote( 'pending' ) . ' = 0';

		$_CB_database->setQuery( $query );

		if ( $_CB_database->loadResult() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if connection from $memberId to $referenceId is pending.
	 *
	 * @param  int      $memberId
	 * @param  int      $referenceId
	 * @return boolean
	 */
	public function isConnectionPending( $memberId, $referenceId = null )
	{
		global $_CB_database;

		if ( ! $referenceId ) {
			$referenceId	=	$this->referenceId;
		}

		$query				=	'SELECT COUNT(*)'
			.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_members' )
			.	"\n WHERE " . $_CB_database->NameQuote( 'referenceid' ) . ' = ' . (int) $referenceId
			.	"\n AND " . $_CB_database->NameQuote( 'memberid' ) . ' = ' . (int) $memberId
			.	"\n AND " . $_CB_database->NameQuote( 'pending' ) . ' = 1';

		$_CB_database->setQuery( $query );

		if ( $_CB_database->loadResult() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if connection from $memberId to $referenceId is accepted.
	 *
	 * @param  int      $memberId
	 * @param  int      $referenceId
	 * @return boolean
	 */
	public function isConnectionAccepted( $memberId, $referenceId = null )
	{
		global $_CB_database;

		if ( ! $referenceId ) {
			$referenceId	=	$this->referenceId;
		}

		$query				=	'SELECT COUNT(*)'
			.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_members' )
			.	"\n WHERE " . $_CB_database->NameQuote( 'referenceid' ) . ' = ' . (int) $referenceId
			.	"\n AND " . $_CB_database->NameQuote( 'memberid' ) . ' = ' . (int) $memberId
			.	"\n AND " . $_CB_database->NameQuote( 'accepted' ) . ' = 1';

		$_CB_database->setQuery( $query );

		if ( $_CB_database->loadResult() ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the just computed degree of separation
	 * @return int
	 */
	public function getDegreeOfSep( )
	{
		return $this->degreeOfSep;
	}

	/**
	 * Sets the degree of separation
	 *
	 * @param  int  $deg
	 * @return void
	 */
	protected function _setDegreeOfSep( $deg )
	{
		$this->degreeOfSep	=	$deg;
	}

	/**
	 * Gets the user message
	 *
	 * @return string
	 */
	public function getUserMSG( )
	{
		return $this->userMSG;
	}

	/**
	 * Sets the user message
	 *
	 * @param  string  $msg
	 * @return void
	 */
	protected function _setUserMSG( $msg )
	{
		$this->userMSG	=	$msg;
	}

	/**
	 * Gets the error message
	 *
	 * @return string
	 */
	public function getErrorMSG()
	{
		return $this->errorMSG;
	}

	/**
	 * Sets the error message
	 *
	 * @param  string  $msg
	 * @return void
	 */
	protected function _setErrorMSG( $msg )
	{
		$this->errorMSG	=	$msg;
		return;
	}
}
