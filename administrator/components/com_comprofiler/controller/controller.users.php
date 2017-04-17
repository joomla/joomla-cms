<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBController_users {

	/**
	 * Outputs legacy user mass mailer and user reconfirm email display
	 *
	 * @param  string  $option
	 * @param  string  $task
	 * @param  int[]   $cid
	 * @return bool
	 * @deprecated 2.0
	 */
	public function showUsers( $option, $task, $cid ) {
		global $_CB_framework, $_CB_database, $ueConfig, $_PLUGINS;

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );
		cbimport( 'cb.pagination' );
		cbimport( 'cb.lists' );

		// We just need the user rows as we've already filtered down the IDs in user management:
		$query								=	'SELECT *'
											.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
											.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
											.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
											.	"\n WHERE u." . $_CB_database->NameQuote( 'id' ) . " IN ( " . implode( ', ', cbArrayToInts( $cid ) ) . " )";
		$_CB_database->setQuery( $query );
		$rows								=	$_CB_database->loadObjectList( null, '\CB\Database\Table\UserTable', array( $_CB_database ) );
		$total								=	count( $rows );

		if ( $task == 'resendconfirmationemails' ) {
			if ( ! $rows ) {
				cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), CBTxt::T( 'SELECT_A_ROW_TO_TASK', 'Select a row to [task]', array( '[task]' => $task ) ), 'error' );
			}

			$count							=	0;

			/** @var UserTable[] $rows */
			foreach ( $rows as $row ) {
				if ( $row->confirmed == 0 ) {
					if ( $row->cbactivation == '' ) {
						// Generate a new confirmation code if the user doesn't have one (requires email confirmation to be enabled):
						$row->store();
					}

					$cbNotification			=	new cbNotification();

					$cbNotification->sendFromSystem( $row->id, CBTxt::T( $ueConfig['reg_pend_appr_sub'] ), CBTxt::T( $ueConfig['reg_pend_appr_msg'] ), true, ( isset( $ueConfig['reg_email_html'] ) ? (int) $ueConfig['reg_email_html']  : 0 ) );
					++$count;
				}
			}

			cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), CBTxt::T( 'SENT_CONFIRMATION_EMAILS_TO_NUM_USERS_USERS', 'Sent confirmation emails to [NUM_USERS] users', array( '[NUM_USERS]' => $count ) ) );
		} else {
			$emailSubject					=	stripslashes( cbGetParam( $_POST, 'emailsubject', '' ) );
			$emailBody						=	stripslashes( rawurldecode( cbGetParam( $_POST, 'emailbody', '', _CB_ALLOWRAW | _CB_NOTRIM ) ) );
			$emailAttach					=	stripslashes( cbGetParam( $_POST, 'emailattach', '' ) );
			$emailsPerBatch					=	stripslashes( cbGetParam( $_POST, 'emailsperbatch', 50 ) );
			$emailsBatch					=	stripslashes( cbGetParam( $_POST, 'emailsbatch', 0 ) );
			$emailFromName					=	stripslashes( cbGetParam( $_POST, 'emailfromname', '' ) );
			$emailFromAddr					=	stripslashes( cbGetParam( $_POST, 'emailfromaddr', '' ) );
			$emailReplyName					=	stripslashes( cbGetParam( $_POST, 'emailreplyname', '' ) );
			$emailReplyAddr					=	stripslashes( cbGetParam( $_POST, 'emailreplyaddr', '' ) );
			$emailPause						=	stripslashes( cbGetParam( $_POST, 'emailpause', 30 ) );
			$simulationMode					=	stripslashes( cbGetParam( $_POST, 'simulationmode', '' ) );

			// B/C trigger variables:
			if ( ( count( $cid ) > 0 ) && ( count( $cid ) < $total ) ) {
				$total						=	count( $cid );
			}

			$pageNav						=	new cbPageNav( $total, 0, 10 );
			$search							=	'';
			$lists							=	array();
			$inputTextExtras				=	null;
			$select_tag_attribs				=	null;

			if ( $task == 'emailusers' ) {
				if ( ! $rows ) {
					cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), CBTxt::T( 'SELECT_A_ROW_TO_TASK', 'Select a row to [task]', array( '[task]' => $task ) ), 'error' );
				}

				$pluginRows					=	$_PLUGINS->trigger( 'onBeforeBackendUsersEmailForm', array( &$rows, &$pageNav, &$search, &$lists, &$cid, &$emailSubject, &$emailBody, &$inputTextExtras, &$select_tag_attribs, $simulationMode, $option, &$emailAttach, &$emailFromName, &$emailFromAddr, &$emailReplyName, &$emailReplyAddr ) );
				$usersView					=	_CBloadView( 'users' );

				/** @var CBView_users $usersView */
				$usersView->emailUsers( $rows, $emailSubject, $emailBody, $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $emailsPerBatch, $emailsBatch, $emailPause, $simulationMode, $pluginRows );
			} elseif ( $task == 'startemailusers' ) {
				$pluginRows					=	$_PLUGINS->trigger( 'onBeforeBackendUsersEmailStart', array( &$rows, $total, $search, $lists, $cid, &$emailSubject, &$emailBody, &$inputTextExtras, $simulationMode, $option, &$emailAttach, &$emailFromName, &$emailFromAddr, &$emailReplyName, &$emailReplyAddr ) );
				$usersView					=	_CBloadView( 'users' );

				/** @var CBView_users $usersView */
				$usersView->startEmailUsers( $rows, $emailSubject, $emailBody, $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $emailsPerBatch, $emailsBatch, $emailPause, $simulationMode, $pluginRows );
			} elseif ( $task == 'ajaxemailusers' ) {
				cbSpoofCheck( 'cbadmingui' );
				cbRegAntiSpamCheck();

				$cbNotification				=	new cbNotification();
				$mode						=	1; // html
				$errors						=	0;
				$success					=	array();
				$failed						=	array();

				$users						=	array_slice( $rows, $emailsBatch, $emailsPerBatch );

				if ( $simulationMode ) {
					$success				=	array( '<div class="alert alert-info">' . CBTxt::T( 'Emails do not send in simulation mode' ) . '</div>' );
				} else {
					foreach ( $users as $user ) {
						$extraStrings		=	array();

						$_PLUGINS->trigger( 'onBeforeBackendUserEmail', array( &$user, &$emailSubject, &$emailBody, $mode, &$extraStrings, $simulationMode, &$emailAttach, &$emailFromName, &$emailFromAddr, &$emailReplyName, &$emailReplyAddr ) );

						$attachments		=	cbReplaceVars( $emailAttach, $user, $mode, true, $extraStrings );

						if ( $attachments ) {
							$attachments	=	preg_split( ' *, *', $attachments );
						} else {
							$attachments	=	null;
						}

						if ( ! $cbNotification->sendFromSystem( $user, $emailSubject, $this->makeLinksAbsolute( $emailBody ), true, $mode, null, null, $attachments, $extraStrings, false, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr ) ) {
							$failed[]		=	'<div class="alert alert-danger">' . '<strong>' . htmlspecialchars( $user->name . ' <' . $user->email . '>' ) . '</strong>: ' . CBTxt::Th( 'ERROR_SENDING_EMAIL_ERRORMSG', 'Error sending email: [ERROR_MSG]', array( '[ERROR_MSG]' => $cbNotification->errorMSG ) ) . '</div>';
							++$errors;
						} else {
							$success[]		=	htmlspecialchars( $user->name . ' <' . $user->email . '>' );
						}
					}
				}

				$usernames					=	implode( ', ', $success ) . implode( '', $failed );

				if ( $total < $emailsPerBatch ) {
					$limit					=	$total;
				} else {
					$limit					=	$emailsPerBatch;
				}

				ob_start();
				$usersView					=	_CBloadView( 'users' );
				/** @var CBView_users $usersView */
				$usersView->ajaxResults( $usernames, $emailSubject, $this->makeLinksAbsolute( $emailBody ), $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $emailsBatch, $limit, $total, $errors );
				$html						=	ob_get_contents();
				ob_end_clean();

				$reply						=	array(	'result' => 1,
														'htmlcontent' => $html
													);

				if ( ! ( $total - ( (int) $emailsBatch + (int) $emailsPerBatch ) > 0 ) ) {
					$reply['result']		=	2;
				}

				echo json_encode( $reply );
			}
		}
	}

	/**      
	 * Replaces relative URLs with absolute URLs
	 * 
	 * @param  string $text
	 * @return string
	 * @deprecated 2.0
	 */
	public function makeLinksAbsolute( $text ) {
		// replace <a> links:
		$text		=	preg_replace_callback( '/<a ((?:[^>]* )*)href="(.*)"([^>]*)>/iUs', array( $this, 'parseLinkURL' ), $text );

		// replace <img> links:
		$text		=	preg_replace_callback( '/<img ((?:[^>]* )*)src="(.*)"([^>]*)>/iUs', array( $this, 'parseImgURL' ), $text );

		return $text;
	}

	/**
	 * Replace relative non-sefed link with absolute sefed links
	 *
	 * @param array $matches
	 * @return string
	 * @deprecated 2.0
	 */
	public function parseLinkURL( &$matches ) {
		$url		=	cbUnHtmlspecialchars( $matches[2] );

		if ( ( substr( $url, 0, 6 ) == 'mailto' ) || ( substr( $url, 0, 1 ) == '#' ) || ( substr( $url, 0, 4 ) == 'http' ) ) {
			//mailto link or anchor inside mail or already absolute URL, do nothing..
			return $matches[0];
		}

		// find $url (remove absolute link case from above exception) in known links for id, otherwise insert
		// find linkid + mailing id in links_stats table, otherwise create, and re-create a specific url

		$url		=	cbSef( $url, true );

		return '<a ' . $matches[1] . 'href="' . $url . '"' . $matches[3] . '>';
	}

	/**
	 * Replace relative image src links with absolute links
	 *
	 * @param array $matches
	 * @return string
	 * @deprecated 2.0
	 */
	public function parseImgURL(&$matches){
		global $_CB_framework;

		$image		=	cbUnHtmlspecialchars( $matches[2] );

		if ( substr( $image, 0, 4 ) == 'http' ) {
			// already absolute URL, do nothing..
			return $matches[0];
		}

		if ( substr( $image, 0, 1 ) != '/' ) {
			$image	=	'/' . $image;
		}

		$image		=	$_CB_framework->getCfg( 'live_site' ) . $image;

		return '<img ' . $matches[1] . 'src="' . $image . '"' . $matches[3] . '>';
	}
}