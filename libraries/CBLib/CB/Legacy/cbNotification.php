<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 11:39 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbNotification Class implementation
 * Notification Class for handling CB notifications
 */
class cbNotification
{
	/**
	 * Error message when an error is encountered
	 * @var string
	 */
	public $errorMSG;
	/**
	 * custom mailer properties to apply before processing and sending an email
	 * @var array
	 */
	protected $mailerProperties;

	/**
	 * Constructor
	 * @see comprofilerMail()
	 *
	 * @param  array|null  $mailerProperties  Properties to apply to the mailer object before processing and sending (useful for supplying custom mailer)
	 */
	public function __construct( $mailerProperties = null )
	{
		$this->mailerProperties		=	$mailerProperties;
	}

	/**
	 * Send a message from user to user by email or PMS depending on global configuration
	 * Replaces optionally variables
	 *
	 * @param  int|UserTable  $toUserOrUserId    Receiver
	 * @param  int|UserTable  $fromUserOrUserId  Sender
	 * @param  string         $subject           Subject
	 * @param  string         $message           HTML message for PMS
	 * @param  null|string    $messageEmail      Text message for email
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @return boolean                           Result
	 */
	public function sendFromUser( $toUserOrUserId, $fromUserOrUserId, $subject, $message, $messageEmail = null, $replaceVariables = false, $mode = 0, $extraStrings = array() )
	{
		global $ueConfig;

		if ( $messageEmail === null ) {
			$messageEmail		=	$message;
		}

		switch( $ueConfig['conNotifyType'] ) {
			case 1:
				return $this->sendUserEmail( $toUserOrUserId, $fromUserOrUserId, $subject, $messageEmail, false, $replaceVariables, $mode, null, null, null, $extraStrings );

			case 2:
				return $this->sendUserPMSmsg( $toUserOrUserId, $fromUserOrUserId, $subject, $message, true, $replaceVariables, $mode, $extraStrings );

			case 3:
				$resultPMS		=	$this->sendUserPMSmsg( $toUserOrUserId, $fromUserOrUserId, $subject, $message, true, $replaceVariables, $mode, $extraStrings );
				$resultEmail	=	$this->sendUserEmail( $toUserOrUserId, $fromUserOrUserId, $subject, $messageEmail, false, $replaceVariables, $mode, null, null, null, $extraStrings );

				return ( $resultPMS && $resultEmail );

			default:
				return false;
		}
	}

	/**
	 * Send a message from user to user by PMS
	 * Replaces optionally variables
	 *
	 * @param  int|UserTable  $toUserOrUserId    Receiver
	 * @param  int|UserTable  $fromUserOrUserId  Sender
	 * @param  string         $subject           Subject
	 * @param  string         $message           HTML message for PMS
	 * @param  boolean        $systemGenerated   Is sender the system ?
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @return boolean                           Result
	 */
	public function sendUserPMSmsg( $toUserOrUserId, $fromUserOrUserId, $subject, $message, $systemGenerated = false, $replaceVariables = false, $mode = 0, $extraStrings = array() )
	{
		global $_CB_PMS;

		if ( ! is_object( $toUserOrUserId ) ) {
			$rowTo			=	CBuser::getUserDataInstance( (int) $toUserOrUserId );
		} else {
			if ( ! ( $toUserOrUserId instanceof UserTable ) ) {
				$rowTo		=	CBuser::getUserDataInstance( (int) $toUserOrUserId->id );
			} else {
				$rowTo		=	$toUserOrUserId;
			}
		}

		if ( ! is_object( $fromUserOrUserId ) ) {
			$rowFrom		=	CBuser::getUserDataInstance( (int) $fromUserOrUserId );
		} else {
			if ( ! ( $fromUserOrUserId instanceof UserTable ) ) {
				$rowFrom	=	CBuser::getUserDataInstance( (int) $fromUserOrUserId->id );
			} else {
				$rowFrom	=	$fromUserOrUserId;
			}
		}

		if ( $replaceVariables ) {
			$subject		=	$this->_replaceVariables( $subject, $rowFrom, $mode, $extraStrings );
			$message		=	$this->_replaceVariables( $message, $rowFrom, $mode, $extraStrings );
		} elseif ( $replaceVariables == 2 ) {
			$subject		=	$this->_replaceVariables( $subject, $rowTo, $mode, $extraStrings );
			$message		=	$this->_replaceVariables( $message, $rowTo, $mode, $extraStrings );
		}

		$resultArray		=	$_CB_PMS->sendPMSMSG( $rowTo->id, $rowFrom->id, $subject, $message, $systemGenerated );

		if ( count( $resultArray ) > 0 ) {
			return $resultArray[0];
		} else {
			return false;
		}
	}

	/**
	 * Send an email from an email address to a user
	 * Replaces optionally variables
	 *
	 * @param  int|UserTable  $toUserOrUserId    Receiver
	 * @param  string         $fromEmailName     From name
	 * @param  string         $fromEmailAddress  From email address
	 * @param  string         $subject           Subject
	 * @param  string         $message           Body
	 * @param  boolean        $revealEmail       Should we reveal the email of the sender ?
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  null|string    $cc                Email CC address
	 * @param  null|string    $bcc               Email BCC address
	 * @param  null|string    $attachment        Email attachment files
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @return boolean                           Result
	 */
	public function sendUserEmailFromEmail( $toUserOrUserId, $fromEmailName, $fromEmailAddress, $subject, $message, $revealEmail = false, $replaceVariables = false, $mode = 0, $cc = null, $bcc = null, $attachment = null, $extraStrings = array() )
	{
		global $_CB_framework, $_SERVER;

		if ( ( ! $subject ) && ( ! $message ) && ( ! $fromEmailAddress ) ) {
			return true;
		}

		if ( ! is_object( $toUserOrUserId ) ) {
			$rowTo							=	CBuser::getUserDataInstance( (int) $toUserOrUserId );
		} else {
			if ( ! ( $toUserOrUserId instanceof UserTable ) ) {
				$rowTo						=	CBuser::getUserDataInstance( (int) $toUserOrUserId->id );
			} else {
				$rowTo						=	$toUserOrUserId;
			}
		}

		$rowFrom							=	new UserTable();
		$rowFrom->name						=	( $fromEmailName ? $fromEmailName : $fromEmailAddress );
		$rowFrom->username					=	( $fromEmailName ? $fromEmailName : $fromEmailAddress );
		$rowFrom->email						=	$fromEmailAddress;

		$replyToName						=	$rowFrom->name;
		$fromNameHidden						=	CBTxt::T( 'EMAIL_NOTE_NOTIFICATIONS_AT_SITENAME', 'Notifications at [sitename]', array( '[sitename]' => $_CB_framework->getCfg( 'sitename' ) ) );

		return $this->sendThatMail( $rowTo, $rowFrom, $subject, $message, $revealEmail, $replaceVariables, $mode, $cc, $bcc, $attachment, $extraStrings, $replyToName, $fromNameHidden );
	}

	/**
	 * Send an email from a user to a user
	 * Replaces optionally variables
	 *
	 * @param  int|UserTable  $toUserOrUserId    Receiver
	 * @param  int|UserTable  $fromUserOrUserId  Sender
	 * @param  string         $subject           Subject
	 * @param  string         $message           HTML message for PMS
	 * @param  boolean        $revealEmail       Should we reveal the email of the sender ?
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  null|string    $cc                Email CC address
	 * @param  null|string    $bcc               Email BCC address
	 * @param  null|string    $attachment        Email attachment files
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @return boolean                           Result
	 */
	public function sendUserEmail( $toUserOrUserId, $fromUserOrUserId, $subject, $message, $revealEmail = false, $replaceVariables = false, $mode = 0, $cc = null, $bcc = null, $attachment = null, $extraStrings = array() )
	{
		if ( ( ! $subject ) && ( ! $message ) ) {
			return true;
		}

		if ( ! is_object( $toUserOrUserId ) ) {
			$rowTo							=	CBuser::getUserDataInstance( (int) $toUserOrUserId );
		} else {
			if ( ! ( $toUserOrUserId instanceof UserTable ) ) {
				$rowTo						=	CBuser::getUserDataInstance( (int) $toUserOrUserId->id );
			} else {
				$rowTo						=	$toUserOrUserId;
			}
		}

		if ( ! is_object( $fromUserOrUserId ) ) {
			$rowFrom						=	CBuser::getUserDataInstance( (int) $fromUserOrUserId );
		} else {
			if ( ! ( $fromUserOrUserId instanceof UserTable ) ) {
				$rowFrom					=	CBuser::getUserDataInstance( (int) $fromUserOrUserId->id );
			} else {
				$rowFrom					=	$fromUserOrUserId;
			}
		}

		return $this->sendThatMail( $rowTo, $rowFrom, $subject, $message, $revealEmail, $replaceVariables, $mode, $cc, $bcc, $attachment, $extraStrings, null, null );
	}

	/**
	 * Internal function common to function sendUserEmailFromEmail and function sendUserEmail
	 *
	 * @param  UserTable    $rowTo             Receiver
	 * @param  UserTable    $rowFrom           Sender
	 * @param  string       $subject           Subject
	 * @param  string       $message           Body
	 * @param  boolean      $revealEmail       Should we reveal the email of the sender ?
	 * @param  boolean|int  $replaceVariables  Should we replace variables ?
	 * @param  int          $mode              false = plain text, true = HTML
	 * @param  null|string  $cc                Email CC address
	 * @param  null|string  $bcc               Email BCC address
	 * @param  null|string  $attachment        Email attachment files
	 * @param  array        $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @param  string|null  $replyToName       ReplyTo name(s)
	 * @param  string|null  $fromNameHidden    Replacement name to hide real name of sender
	 * @return boolean                         Result
	 */
	private function sendThatMail( $rowTo, $rowFrom, $subject, $message, $revealEmail, $replaceVariables, $mode, $cc, $bcc, $attachment, $extraStrings, $replyToName, $fromNameHidden )
	{
		global $_CB_framework, $ueConfig;

		$fromEmail							=	false;
		$fromName							=	false;

		if ( $replaceVariables ) {
			$subject						=	$this->_replaceVariables( $subject, $rowFrom, $mode, $extraStrings );
			$message						=	$this->_replaceVariables( $message, $rowFrom, $mode, $extraStrings );
		} elseif ( $replaceVariables == 2 ) {
			$subject						=	$this->_replaceVariables( $subject, $rowTo, $mode, $extraStrings );
			$message						=	$this->_replaceVariables( $message, $rowTo, $mode, $extraStrings );
		}

		if ( $revealEmail ) {
			if ( isset( $ueConfig['allow_email_replyto'] ) && ( $ueConfig['allow_email_replyto'] == 2 ) ) {
				$replyToEmail				=	$rowFrom->email;
				if ( ! $replyToName ) {
					$replyToName 			=	getNameFormat( $rowFrom->name, $rowFrom->username, $ueConfig['name_format'] );
				}
				$fromEmail					=	$ueConfig['reg_email_from'];
				$fromName					=	$this->defaultFromName();
			} else {
				$replyToEmail				=	null;
				$replyToName 				=	null;
			}
		} else {
			$replyToEmail					=	null;
			$replyToName					=	null;
			$fromEmail						=	$ueConfig['reg_email_from'];
			if ( $fromNameHidden ) {
				$fromName					=	$fromNameHidden;
			} else {
				$fromName					=	CBTxt::T( 'EMAIL_NOTE_NOTIFICATIONS_AT_SITENAME', 'Notifications at [sitename]', array( '[sitename]' => $_CB_framework->getCfg( 'sitename' ) ) );
			}

			$toUserLanguage					=	CBuser::getInstance( (int) $rowTo->id )->getUserData()->getUserLanguage();
			$savedLanguage					=	CBTxt::setLanguage( $toUserLanguage );

			$message						.=	"\n\n" . $this->_replaceVariables( CBTxt::T( 'EMAIL_NOTE_AUTOMATIC_GENERATION', 'NOTE: This email was automatically generated from [sitename] ([siteurl]).' ), $rowFrom, $mode, $extraStrings );

			CBTxt::setLanguage( $savedLanguage );
		}

		// Lets fix linebreaks encase the message was sent as a plain string:
		$message							=	str_replace( array( '\r\n', '\n' ), array( "\r\n", "\n" ), $message );

		return $this->_sendEmailMSG( $rowTo, $rowFrom, $replyToName, $replyToEmail, $subject, $message, $revealEmail, $mode, $cc, $bcc, $attachment, $fromEmail, $fromName );
	}

	/**
	 * Send email from system to a user
	 * Replaces optionally variables
	 *
	 * @param  int|UserTable  $toUserOrUserId    Receiver
	 * @param  string         $subject           Subject
	 * @param  string         $message           HTML message for PMS
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  null|string    $cc                Email CC address
	 * @param  null|string    $bcc               Email BCC address
	 * @param  null|string    $attachment        Email attachment files
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @param  boolean        $footer            Add footer "Automated message sent from" ?
	 * @param  null|string    $fromName          [optional] From name
	 * @param  null|string    $fromEmail         [optional] From email address
	 * @param  null|string    $replyToName       [optional] Reply-To name
	 * @param  null|string    $replyToEmail      [optional] Reply-To email address
	 * @return boolean                           Result
	 */
	public function sendFromSystem( $toUserOrUserId, $subject, $message, $replaceVariables = true, $mode = 0, $cc = null, $bcc = null, $attachment = null, $extraStrings = array(), $footer = true, $fromName = null, $fromEmail = null, $replyToName = null, $replyToEmail = null )
	{
		global $_CB_framework, $ueConfig;

		if ( ( ! $subject ) && ( ! $message ) ) {
			return true;
		}

		$rowFrom					=	new UserTable();
		$rowFrom->email				=	( $fromEmail ? $fromEmail : $ueConfig['reg_email_from'] );
		$rowFrom->name				=	( $fromName ? $fromName : $this->defaultFromName() );;
		if ( ! $replyToEmail ) {
			$replyToEmail			=	$ueConfig['reg_email_replyto'];
		}
		if ( ! $replyToName ) {
			$replyToName			=	$this->defaultFromName();
		}

		if ( ! is_object( $toUserOrUserId ) ) {
			$rowTo					=	CBuser::getUserDataInstance( (int) $toUserOrUserId );
		} else {
			if ( ! ( $toUserOrUserId instanceof UserTable ) ) {
				$rowTo				=	CBuser::getUserDataInstance( (int) $toUserOrUserId->id );
			} else {
				$rowTo				=	$toUserOrUserId;
			}
		}

		if ( $replaceVariables ) {
			$subject				=	$this->_replaceVariables( $subject, $rowTo, $mode, $extraStrings );
			$message				=	$this->_replaceVariables( $message, $rowTo, $mode, $extraStrings );
		}

		if ( $footer ) {
			$toUserLanguage			=	CBuser::getInstance( (int) $rowTo->id )->getUserData()->getUserLanguage();
			$savedLanguage			=	CBTxt::setLanguage( $toUserLanguage );

			$message				.=	"\n\n" . $this->_replaceVariables( CBTxt::T( 'EMAIL_NOTE_AUTOMATIC_GENERATION', 'NOTE: This email was automatically generated from [sitename] ([siteurl]).' ), $rowTo, $mode, $extraStrings );

			CBTxt::setLanguage( $savedLanguage );
		}

		$subject					=	$_CB_framework->getCfg( 'sitename' ) . ' - ' . $subject;

		// Lets fix linebreaks encase the message was sent as a plain string:
		$message					=	str_replace( array( '\r\n', '\n' ), array( "\r\n", "\n" ), $message );

		return $this->_sendEmailMSG( $rowTo, $rowFrom, $replyToName, $replyToEmail, $subject, $message, false, $mode, $cc, $bcc, $attachment );
	}

	/**
	 * Returns translasted default "From" name
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public  function defaultFromName( )
	{
		global $_CB_framework, $ueConfig;

		return CBTxt::T( $ueConfig['reg_email_name'], null, array( '[sitename]' => $_CB_framework->getCfg( 'sitename' ) ) );
	}

	/**
	 * Send an email to all global moderators
	 *
	 * @param  string         $subject           Subject
	 * @param  string         $message           HTML message for PMS
	 * @param  boolean|int    $replaceVariables  Should we replace variables ?
	 * @param  int            $mode              false = plain text, true = HTML
	 * @param  null|string    $cc                Email CC address
	 * @param  null|string    $bcc               Email BCC address
	 * @param  null|string    $attachment        Email attachment files
	 * @param  array          $extraStrings      Extra replacement strings to use if $replaceVariables = true
	 * @return boolean                           Result
	 */
	public function sendToModerators( $subject, $message, $replaceVariables = false, $mode = 0, $cc = null, $bcc = null, $attachment = null, $extraStrings = array() )
	{
		global $_CB_database;

		$moderators		=	Application::CmsPermissions()->getGroupsOfViewAccessLevel( Application::Config()->get( 'moderator_viewaccesslevel', 3, \CBLib\Registry\GetterInterface::INT ), true );

		if ( $moderators ) {
			$query		=	'SELECT DISTINCT u.id'
				.	"\n FROM #__users u"
				.	"\n INNER JOIN #__comprofiler c"
				.	' ON u.id = c.id';

			$query		.=	"\n INNER JOIN #__user_usergroup_map g"
				.	' ON c.id = g.user_id'
				.	"\n WHERE g.group_id IN " . $_CB_database->safeArrayOfIntegers( $moderators );

			$query		.=	"\n AND u.block = 0"
				.	"\n AND c.confirmed = 1"
				.	"\n AND c.approved = 1"
				.	"\n AND u.sendEmail = 1";

			$_CB_database->setQuery( $query );
			$mods		=	$_CB_database->loadObjectList();

			if ( $mods ) foreach ( $mods AS $mod ) {
				$this->sendFromSystem( $mod->id, $subject, $message, $replaceVariables, $mode, $cc, $bcc, $attachment, $extraStrings );
			}
		}
	}

	/**
	 * Internal function to sends an email from a user to another user
	 *
	 * @param  UserTable|stdClass  $toUser        Receiver
	 * @param  UserTable           $fromUser      Sender
	 * @param  string              $replyToName   Reply-To name
	 * @param  string              $replyToEmail  Reply-To email address
	 * @param  string              $subject       Subject
	 * @param  string              $message       HTML message for PMS
	 * @param  boolean             $addPrefix     Add prefix text to explain who is sending from which site ?
	 * @param  int                 $mode          false = plain text, true = HTML
	 * @param  null|string         $cc            Email CC address
	 * @param  null|string         $bcc           Email BCC address
	 * @param  null|string         $attachment    Email attachment files
	 * @param  boolean|string      $fromEmail     From email address (overrides from row email): false = from row email, null = system email, otherwise use as specified
	 * @param  boolean|string      $fromName      From name (overrides from row name): false = from row name, null = system name, otherwise use a specified
	 * @return boolean                            Result
	 */
	private function _sendEmailMSG( $toUser, $fromUser, $replyToName, $replyToEmail, $subject, $message, $addPrefix = false, $mode = 0, $cc = null, $bcc = null, $attachment = null, $fromEmail = false, $fromName = false )
	{
		global $_CB_framework, $ueConfig, $_PLUGINS, $_SERVER;

		$_PLUGINS->trigger( 'onBeforeSendEmailMSG', array( $this, &$toUser, &$fromUser, &$subject, &$message, &$addPrefix, &$mode, &$cc, &$bcc, &$attachment ) );

		if ( $addPrefix ) {
			$toUserLanguage	=	CBuser::getInstance( (int) $toUser->id )->getUserData()->getUserLanguage();
			$savedLanguage	=	CBTxt::setLanguage( $toUserLanguage );

			$preMsg			=	$this->_replaceVariables(
				CBTxt::T( 'EMAIL_NOTE_MESSAGE_FROM_NAME_AT_SITENAME_TO_YOU', '------- This is a message from [formatname] at [sitename] ([siteurl]) to you: -------' ) . "\r\n\r\n",
				$fromUser, $mode
			);

			$postMsg		=	$this->_replaceVariables(
				"\r\n\r\n" . CBTxt::T( 'EMAIL_NOTE', '------ NOTES: ------' )
				. ( isset( $ueConfig['allow_email_replyto'] ) && ( $ueConfig['allow_email_replyto'] == 2 ) ? "\r\n\r\n" . CBTxt::T( 'EMAIL_NOTE_WHEN_REPLYING_CHECK_ADDRESS', 'When replying, please check carefully that the email address of [formatname] is [email].' ) : null )
				. "\r\n\r\n" . CBTxt::T( 'EMAIL_NOTE_UNSEEN_ADDRESS', 'This user did not see your email address. If you reply the recipient will have your email address.' )
				. "\r\n\r\n" . CBTxt::T( 'EMAIL_NOTE_CONTENTS_DISCLAIMER', '[sitename] owners cannot accept any responsibility for the contents of the email and of user email addresses.' ),
				$fromUser, $mode
			);
			$message		=	$preMsg . $message . $postMsg;
			$fromName		=	getNameFormat( $fromUser->name, $fromUser->username, $ueConfig['name_format'] ) . ' @ ' . $_CB_framework->getCfg( 'sitename' );

			CBTxt::setLanguage( $savedLanguage );
		}

		$this->errorMSG		=	null;

		$result				=	comprofilerMail(
			( $fromEmail !== false ? $fromEmail : $fromUser->email ),
			( $fromName !== false ? $fromName : $fromUser->name ),
			$toUser->email,
			$subject,
			$message,
			$mode,
			$cc,
			$bcc,
			$attachment,
			$replyToEmail,
			$replyToName,
			$this->mailerProperties,
			$this->errorMSG
		);

		$_PLUGINS->trigger( 'onAfterSendEmailMSG', array( $result, $this, $toUser, $fromUser, $subject, $message, $addPrefix, $mode, $cc, $bcc, $attachment ) );

		return $result;
	}

	/**
	 * Returns text of user details of user
	 *
	 * @param  UserTable  $user             User for details
	 * @param  int        $includePassword  Should password field be included (if user is confirmed and approved. Field must be clear-text) ?
	 * @return string                       new-line-separated text
	 */
	private function _getUserDetails( $user, $includePassword )
	{
		$return			=	CBTxt::T( 'EMAIL_EMAILADDRESS', 'Email: [emailaddress]', array( '[emailaddress]' => $user->email ) )
			.	"\n" . CBTxt::T( 'NAME_NAME', 'Name: [name]', array( '[name]' => $user->name ) )
			.	"\n" . CBTxt::T( 'USERNAME_USERNAME', 'Username: [username]', array( '[username]' => $user->username ) );

		if ( ( $includePassword == 1 ) && ( $user->confirmed == 1 ) && ( $user->approved == 1 ) ) {
			$return		.=	"\n" . CBTxt::T( 'PASSWORD_PASSWORD', 'Password: [password]', array( '[password]' => $user->password ) );
		}

		return $return;
	}

	/**
	 * @param  string     $message
	 * @param  UserTable  $row
	 * @param  int        $mode
	 * @param  array      $extras
	 * @return string
	 */
	public function _replaceVariables( $message, $row, $mode = 0, $extras = array() )
	{
		global $_CB_framework, $ueConfig;

		// Always build the confirm link for substitution:
		$confirmLink		=	$_CB_framework->rawViewUrl( 'confirm', false, array( 'confirmcode' => $row->cbactivation ) );

		// Lets build the confirm output only if confirmation is enabled:
		if ( $ueConfig['reg_confirmation'] == 1 ) {
			if ( $row->confirmed ) {
				$confirm	=	"\n" . CBTxt::T( 'UE_USER_EMAIL_CONFIRMED', 'Email address is already confirmed' ) . "\n";
			} else {
				$confirm	=	"\n" . $confirmLink . "\n";
			}
		} else {
			$confirm		=	null;
		}

		// Lets fix linebreaks encase the message was sent as a plain string:
		$message			=	str_replace( array( '\r\n', '\n' ), array( "\r\n", "\n" ), $message );

		// Prepare default substitution extras (note how we push some normal substitutions as extras so non-existing users can still substitute information):
		$extraStrings		=	array(
										'emailaddress'	=> $row->email,
										'email'			=> $row->email,
										'formatname'	=> getNameFormat( $row->name, $row->username, $ueConfig['name_format'] ),
										'name'			=> $row->name,
										'username'		=> $row->username,
										'details'		=> $this->_getUserDetails( $row, ( isset( $ueConfig['emailpass'] ) ? $ueConfig['emailpass'] : 0 ) ),
										'confirm'		=> $confirm,
										'confirmlink'	=> $confirmLink,
										'sitename'		=> $_CB_framework->getCfg( 'sitename' ),
										'siteurl'		=> $_CB_framework->getCfg( 'live_site' )
									 );

		// Combine default substitutions with extras supplied:
		if ( $extras && is_array( $extras ) ) {
			$extraStrings	=	array_merge( $extraStrings, $extras );
		}

		if ( $row instanceof UserTable && $row->id ) {
			return CBuser::getInstance( $row->id )->replaceUserVars( $message, true, true, $extraStrings, false );
		}

		return cbReplaceVars( $message, $row, $mode, true, $extraStrings, false );
	}
}
