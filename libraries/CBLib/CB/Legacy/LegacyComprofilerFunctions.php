<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 7/8/14 6:22 PM $
* @package CB\Legacy
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Legacy
{
	use cbPMS;
	use CBLib\Language\CBTxt;

	/**
	 * @global int $_CB_OneTwoRowsStyleToggle
	 */
	/** @noinspection PhpUnusedLocalVariableInspection */
	global $_CB_OneTwoRowsStyleToggle;
	/**
	 * storing already outputed items in head to output only once and avoid double-outputing.
	 * @global array $_CB_outputedHeads
	 */
	/** @noinspection PhpUnusedLocalVariableInspection */
	global $_CB_outputedHeads;

	/** @global cbPMS $_CB_PMS */
	/** @noinspection PhpUnusedLocalVariableInspection */
	global  $_CB_PMS;

	defined('CBLIB') or die();

	/**
	 * CB\Legacy\LegacyComprofilerFunctions Class implementation
	 *
	 */
	class LegacyComprofilerFunctions
	{
		/**
		 * Checks that the constructor is executed only once
		 * @var boolean
		 */
		private static $loaded	=	false;

		/**
		 * Constructor (do not call directly, use DI to call it)
		 * Code in here was previously in comprofiler.class.php
		 */
		function __construct( )
		{
			if ( self::$loaded ) {
				return;
			}

			self::$loaded		=	true;

			define( "_UE_PREGMATCH_VALID_EMAIL", "/[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i" );

			// START Legacy Defines
			if ( ! defined( '_UE_BLANK' ) ) {
				define( '_CMN_NO', CBTxt::T( 'UE_NO', 'No' ) );
				define( '_CMN_YES', CBTxt::T( 'UE_YES', 'Yes' ) );
				define( '_UE_NO', CBTxt::T( 'UE_NO', 'No' ) );
				define( '_UE_YES', CBTxt::T( 'UE_YES', 'Yes' ) );
				define( '_LOGOUT_SUCCESS', CBTxt::T( 'LOGOUT_SUCCESS', 'You have successfully logged out' ) );
				define( '_UE_ALREADY_LOGGED_IN', CBTxt::T( 'UE_ALREADY_LOGGED_IN', 'You are already logged in' ) );
				define( '_UE_BANUSER_MSG', CBTxt::T( 'UE_BANUSER_MSG', 'Your user profile was banned by an administrator. Please log in and review why it was banned.' ) );
				define( '_UE_BANUSER_SUB', CBTxt::T( 'UE_BANUSER_SUB', 'User Profile Banned.' ) );
				define( '_UE_CAPTCHA_ALT_IMAGE', CBTxt::T( 'Image with security code embedded in it' ) );
				define( '_UE_CAPTCHA_AUDIO', CBTxt::T( 'click here to hear the letters' ) );
				define( '_UE_CAPTCHA_AUDIO_CLICK2DOWNLOAD', CBTxt::T( '(right-click or control-click)' ) );
				define( '_UE_CAPTCHA_AUDIO_DOWNLOAD', CBTxt::T( 'Click to externally play or download audio file' ) );
				define( '_UE_CAPTCHA_AUDIO_POPUP_CLOSEWINDOW', CBTxt::T( 'Click to close window' ) );
				define( '_UE_CAPTCHA_AUDIO_POPUP_DESCRIPTION', CBTxt::T( 'Listen to audio playback of captcha image' ) );
				define( '_UE_CAPTCHA_AUDIO_POPUP_TITLE', CBTxt::T( 'CB Captcha Audio Playback' ) );
				define( '_UE_CAPTCHA_Desc', CBTxt::T( 'Enter Security Code from image. If no image is present then try disabling your advertisement blocker and then refresh this page. Otherwise please contact the website administrator for assistance.' ) );
				define( '_UE_CAPTCHA_Enter_Label', CBTxt::T( 'Enter Security Code' ) );
				define( '_UE_CAPTCHA_Label', CBTxt::T( 'Security Code' ) );
				define( '_UE_CAPTCHA_NOT_VALID', CBTxt::T( 'Invalid Security Code' ) );
				define( '_UE_CHARACTERS', CBTxt::T( 'characters' ) );
				define( '_UE_CLOSE_OVERLIB', CBTxt::T( 'CLOSE', 'Close' ) );
				define( '_UE_DELETE_AVATAR', CBTxt::T( 'Remove Image' ) );
				define( '_UE_DO_LOGIN', CBTxt::T( 'UE_DO_LOGIN', 'You need to log in.' ) );
				define( '_UE_EMAIL', CBTxt::T( 'UE_EMAIL', 'Email' ) );
				define( '_UE_FIELDREQUIRED', CBTxt::T( 'UE_REQUIRED_ERROR', 'This Field is required' ) );
				define( '_UE_HAS_NO_PROFILE_IMAGE', CBTxt::T( 'UE_HAS_NO_PROFILE_IMAGE', 'Has no profile image' ) );
				define( '_UE_HAS_PROFILE_IMAGE', CBTxt::T( 'UE_HAS_PROFILE_IMAGE', 'Has a profile image' ) );
				define( '_UE_MENU_SENDUSEREMAIL_DESC', CBTxt::T( 'UE_MENU_SENDUSEREMAIL_DESC', 'Send an Email to this user' ) );
				define( '_UE_NEVER', CBTxt::T( 'Never' ) );
				define( '_UE_NOSUCHPROFILE', CBTxt::T( 'UE_NOSUCHPROFILE', 'This profile does not exist or is no longer available' ) );
				define( '_UE_NOT_AUTHORIZED', CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
				define( '_UE_NO_PREFERENCE', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
				define( '_UE_PASS', CBTxt::T( 'UE_PASS', 'Password' ) );
				define( '_UE_REQUIRED_ERROR', CBTxt::T( 'UE_REQUIRED_ERROR', 'This field is required!' ) );
				define( '_UE_SENDEMAIL', CBTxt::T( 'UE_SENDEMAIL', 'Send Email' ) );
				define( '_UE_USERNAME', CBTxt::T( 'Username' ) );
				define( '_UE_BLANK', '' );
			}
			// END Legacy Defines

			global $_CB_OneTwoRowsStyleToggle;
			global $_CB_outputedHeads;
			global $_CB_PMS;

			$_CB_OneTwoRowsStyleToggle	=	1;			// toggle for status sectionTableEntry display

			$_CB_outputedHeads			=	array();

			$_CB_PMS		=	new cbPMS();
		}
	}
}

/**
 * LEGACY FUNCTIONS FROM comprofiler.class.php :
 * =============================================
 */

namespace
{
	use CBLib\Application\Application;
	use CBLib\Input\InjectionsFilter;
	use CBLib\Language\CBTxt;
	use CB\Database\Table\UserTable;
	use CB\Database\Table\UserViewTable;
	use CBLib\Registry\GetterInterface;
	use CBPHPMailer\CBPHPMailer;

	// use CBCookie;
	// use cbNotification;
	// use cbPageNav;
	// use CBPHPMailer;
	// use CBuser;

	/**
	 * Checks if a given string is a valid email address
	 *
	 * @param	string	$email	String to check for a valid email address
	 * @return	boolean
	 */
	function cbIsValidEmail( $email ) {
		return preg_match( _UE_PREGMATCH_VALID_EMAIL, $email );
	}

	/**
	 * Function to create a mail object for futher use (uses phpMailer, smtp or sendmail depending on global config)
	 *
	 * @param  string  $from      From e-mail address
	 * @param  string  $fromname  From name
	 * @param  string  $subject   E-mail subject
	 * @param  string  $body      Message body
	 * @return CBPHPMailer        Mail object
	 */
	function & comprofilerCreateMail( $from = '', $fromname = '', $subject, $body ) {
		global $_CB_framework, $_PLUGINS;

		$mail					=	new CBPHPMailer();

		$_PLUGINS->trigger( 'onBeforeCreateMailer', array( &$mail, &$from, &$fromname, &$subject, &$body ) );

		$mail->SetLanguage( 'en' );
		$mail->CharSet 			=	$_CB_framework->outputCharset();
		$mail->IsMail();
		$mail->From				=	$from ? $from : $_CB_framework->getCfg( 'mailfrom' );
		if ( ( $mail->From == '' ) || ( $mail->From == 'registration@whatever' ) ) {
			$mail->From			=	$_CB_framework->getCfg( 'mailfrom' );
		}
		$mail->FromName			=	$fromname ? $fromname : $_CB_framework->getCfg( 'fromname' );
		$mail->Mailer 			=	$_CB_framework->getCfg( 'mailer' );

		if ( $_CB_framework->getCfg( 'mailer' ) == 'smtp' ) {
			// Add smtp values:
			$mail->SMTPAuth		=	$_CB_framework->getCfg( 'smtpauth' );
			$mail->Username		=	$_CB_framework->getCfg( 'smtpuser' );
			$mail->Password		=	$_CB_framework->getCfg( 'smtppass' );
			$mail->Host			=	$_CB_framework->getCfg( 'smtphost' );
			$smtpport			=	(int) trim( $_CB_framework->getCfg( 'smtpport' ) );
			if ( $smtpport ) {
				$mail->Port		=	$smtpport;
			}
			$smtpsecure			=	$_CB_framework->getCfg( 'smtpsecure' );
			if ( $smtpsecure === 'ssl' ) {
				$mail->SMTPSecure	=	$smtpsecure;
			}
			if  ( $smtpsecure === 'tls' && substr( $mail->Host, 0, 6 ) !== 'tls://' ) {
				$mail->Host		=	'tls://' . $mail->Host;
			}
		} elseif ( $_CB_framework->getCfg( 'mailer' ) == 'sendmail' ) {
			// Set sendmail path:
			if ( $_CB_framework->getCfg( 'sendmail' ) ) {
				$mail->Sendmail	=	$_CB_framework->getCfg( 'sendmail' );
			}
		}

		// If email domain matches sub-part of site domain, we can safely set the sender header to lower risk of valid registration mails being flagged as spam:
		$email_parts			=	explode( '@', $mail->From );
		if ( count( $email_parts ) > 1 ) {
			$email_domain		=	array_pop( $email_parts );
			$urlParts			=	parse_url( $_CB_framework->getCfg( 'live_site' ) );

			if ( $email_domain && ( stripos( $urlParts['host'], $email_domain ) !== false ) ) {
				$mail->Sender	=	$mail->From;
			}
		}

		$mail->Subject 			=	$subject;
		$mail->Body 			=	$body;

		$_PLUGINS->trigger( 'onAfterCreateMailer', array( &$mail, $from, $fromname, $subject, $body ) );

		return $mail;
	}

	/**
	 * Mail function (uses phpMailer or SMTP depending on global settings)
	 *
	 * @param  string        $from         From e-mail address
	 * @param  string        $fromname     From name
	 * @param  string|array  $recipient    Recipient e-mail address(es)
	 * @param  string        $subject      E-mail subject
	 * @param  string        $body         Message body
	 * @param  int           $mode         false = plain text, true = HTML
	 * @param  string|array  $cc           CC e-mail address(es)
	 * @param  string|array  $bcc          BCC e-mail address(es)
	 * @param  string|array  $attachment   Attachment file name(s) (array index is filename, if string)
	 * @param  string|array  $replyto      ReplyTo e-mail address(es)
	 * @param  string|array  $replytoname  ReplyTo name(s)
	 * @param  array         $properties properties to apply to the mailer object before processing and sending (useful for supplying custom mailer)
	 * @param  null|string   $error stores most recent mailer error if available
	 * @return bool True: mail sent, False: mail not sent (error)
	 */
	function comprofilerMail( $from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null, $properties = array(), &$error = null ) {
		global $_CB_framework, $_PLUGINS;

		$mail						=&	comprofilerCreateMail( $from, $fromname, $subject, $body );

		$_PLUGINS->trigger( 'onBeforeSendMail', array( &$mail, &$from, &$fromname, &$recipient, &$subject, &$body, &$mode, &$cc, &$bcc, &$attachment, &$replyto, &$replytoname, &$properties, &$error ) );

		if ( $mode ) {
			$mail->IsHTML( true );

			$hasHtmlHeaders			=	false;
			$htmlStarts				=	array( '<html', '<!DOCTYPE html>' );
			foreach ( $htmlStarts as $startPossibility ) {
				if ( substr( $body, 0, strlen( $startPossibility ) ) == $startPossibility ) {
					$hasHtmlHeaders	=	true;
					break;
				}
			}

			if ( ! $hasHtmlHeaders ) {
				$langCode			=	$_CB_framework->getCfg( 'lang_tag' );
				$ltr				=	$_CB_framework->document->getDirection();
				$mail->Body			=	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
					.	$mail::CRLF
					.	'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . htmlspecialchars( $langCode ) .'" lang="' . htmlspecialchars( $langCode ) .'" dir="' . htmlspecialchars( $ltr ) .'">'
					.	$mail::CRLF
					.	'<head>'
					.	$mail::CRLF
					.	'<meta http-equiv="content-type" content="text/html; charset=utf-8" />'
					.	$mail::CRLF
					.	'</head>'
					.	$mail::CRLF
					.	'<body>'
					.	$mail::CRLF
					.	$body
					.	$mail::CRLF
					.	'</body>'
					.	$mail::CRLF
					.	'</html>';
			}
		}

		if ( is_array( $recipient ) ) {
			foreach ( $recipient as $to ) {
				$mail->AddAddress( $to );
			}
		} else {
			$mail->AddAddress( $recipient );
		}

		if ( isset( $cc ) ) {
			if ( is_array( $cc ) ) {
				foreach ( $cc as $to ) {
					$mail->AddCC( $to );
				}
			} else {
				$mail->AddCC( $cc );
			}
		}

		if ( isset( $bcc ) ) {
			if ( is_array( $bcc ) ) {
				foreach ( $bcc as $to ) {
					$mail->AddBCC( $to );
				}
			} else {
				$mail->AddBCC( $bcc );
			}
		}

		if ( $attachment ) {
			if ( is_array( $attachment ) ) {
				foreach ( $attachment as $fname => $fpath ) {
					if ( is_string( $fname ) ) {
						$mail->AddAttachment( $fpath, $fname );
					} else {
						$mail->AddAttachment( $fpath );
					}
				}
			} else {
				$mail->AddAttachment( $attachment );
			}
		}

		if ( $replyto ) {
			if ( is_array( $replyto ) ) {
				reset( $replytoname );

				foreach ( $replyto as $to ) {
					$mail->AddReplyTo( $to, ( ( false !== (list( , $value ) = each( $replytoname ) ) ) ? $value : '' ) );
				}
			} else {
				$mail->AddReplyTo( $replyto, $replytoname );
			}
		}

		if ( $properties ) foreach ( $properties as $k => $v ) {
			$mail->$k				=	$v;
		}

		if ( checkJversion( '3.2+' ) ) {
			$mailOnline				=	$_CB_framework->getCfg( 'mailonline' );
		} else {
			$mailOnline				=	true;
		}

		if ( $mailOnline ) {
			$mailsSent				=	$mail->Send();
		} else {
			$mailsSent				=	false;

			$_CB_framework->enqueueMessage( CBTxt::T( 'The mailer function is currently not enabled.' ), 'warning' );
		}

		if ( $mail->IsError() ) {
			$error					=	$mail->ErrorInfo;
		}

		$_PLUGINS->trigger( 'onAfterSendMail', array( $mailsSent, $mail, $from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, &$replytoname, $properties, $error ) );

		return $mailsSent;
	}

	/**
	 * Checks E-Mail address with Regex, MX records and SMTP server function (uses SMTP)
	 *
	 * @param  string        $from         From e-mail address
	 * @param  string|array  $recipient    Recipient e-mail address(es)
	 * @return int                         Result: -2: invalid email format, -1: couldn't check, 0: invalid email, 1: valid email.
	 */
	function cbCheckMail( $from, $recipient ) {
		if ( ! cbIsValidEmail( $recipient ) ) {
			return -2;
		}
		$mailparts					=	explode( '@', $recipient, 2 );
		if ( count( $mailparts ) != 2 ) {
			return 0;
		}

		$domain						=	$mailparts[1];
		$mxFound					=	false;
		if ( function_exists( 'getmxrr' ) ) {
			$mxFound				=	false;
			while ( strpos( $domain, '.' ) !== false ) {
				// Validate domain:
				$mxRecords			=	array();
				$mxWeights			=	array();
				if ( @getmxrr( $domain . '.', $mxRecords, $mxWeights ) ) {
					$mxFound	=	true;
					break;
				} else {
					$subDomains		=	explode( '.', $domain, 2 );
					if ( count( $subDomains ) == 2 ) {
						$domain		=	$subDomains[1];
					} else {
						break;
					}
				}
			}
		}
		if ( ! $mxFound ) {
			$ipAddresses			=	gethostbynamel( $mailparts[1] . '.' );		// '.' added so local domain is not added as 2nd trial.
			if ( $ipAddresses === false ) {
				return 0;
			}
			$mxRecords		=	array( $mailparts[1] );
			$mxWeights		=	array( 0 );
		}
		array_multisort( $mxWeights, SORT_ASC, SORT_NUMERIC, $mxRecords );

		$mail					=&	comprofilerCreateMail( $from, '', '', '' );
		$mail->SMTPAuth			=	false;
		// $mail->SMTPDebug		=	2;

		foreach ( $mxRecords as $host ) {
			try {
				$mail->Host			=	$host;

				if ( ! $mail->SmtpConnect() ) {
					continue;
				}
				if ( ! $mail->smtp->Mail( $from ) ) {
					$mail->smtp->Reset();
					return -1;
				}
				if ( ! $mail->smtp->Recipient( $recipient ) ) {
					$error			=	$mail->smtp->getError();
					$mail->smtp->Reset();
					if ( isset( $error['smtp_code'] ) && isset( $error['smtp_msg'] ) && ( $error['smtp_code'] == 450 ) && ( substr( $error['smtp_msg'], 0, 5 ) == '4.7.1' ) ) {
						return -1;		// greylisting detected.
					}
					return 0;
				}
				if ( $mail->SMTPKeepAlive == true ) {
					$mail->smtp->Reset();
				} else {
					$mail->SmtpClose();
				}
				return 1;
			} catch ( \Exception $e ) {
				return -1;
			}
		}
		if ( function_exists( 'getmxrr' ) && ! $mxFound ) {
			return 0;
		} else {
			return -1;
		}
	}

	/**
	 * class moscomprofilerHTML is now in libraries/CBLib/CB/Legacy folder.
	 */

	/**
	 * Deletes all user views from that user and for that user (called on user delete). Temporary function !!
	 *
	 * @param int $userId
	 * @return boolean true if ok, false with warning on sql error
	 */
	function _cbdeleteUserViews( $userId ) {
		$views	=	new UserViewTable();

		return $views->deleteUserViews( $userId );
	}

	/**
	 * Deletes an avatar
	 *
	 * @param  string  $avatar
	 * @return void
	 */
	function deleteAvatar( $avatar ){
		global $_CB_framework;
// 	if(preg_match('/gallery\//i',$avatar)==false && is_file($_CB_framework->getCfg('absolute_path').'/images/comprofiler/'.$avatar)) {
		if( ( strpos( $avatar, '/' ) === false ) && is_file($_CB_framework->getCfg('absolute_path').'/images/comprofiler/'.$avatar)) {
			@unlink($_CB_framework->getCfg('absolute_path').'/images/comprofiler/'.$avatar);
			if(is_file($_CB_framework->getCfg('absolute_path').'/images/comprofiler/tn'.$avatar)) @unlink($_CB_framework->getCfg('absolute_path').'/images/comprofiler/tn'.$avatar);
		}
	}

	/**
	 * Gets activation message depending on user state and cause
	 *
	 * @param  UserTable    $user   User
	 * @param  string       $cause  Cause: 'UserRegistration', 'SameUserRegistrationAgain', 'UserConfirmation'
	 * @return string|null          HTML translated message
	 */
	function getActivationMessage( &$user, $cause ) {
		global $ueConfig;
		if ( ! isset( $ueConfig['emailpass'] ) ) {
			$ueConfig['emailpass']	=	'0';
		}

		$messagesToUser = null;
		if ( in_array( $cause, array( 'UserRegistration', 'SameUserRegistrationAgain' ) ) )
		{
			if 		 ( $ueConfig['emailpass'] == '1' && $user->approved != 1 && $user->confirmed == 1 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOPASS_NOAPPR', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your sign up request requires approval. Once approved your password will be sent to the e-mail address you entered.</p><p>When you receive approval and a password you will then be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '1' && $user->approved != 1 && $user->confirmed == 0 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOPASS_NOAPPR_CONF', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your sign up request requires email confirmation and approval. Please follow the confirmation steps sent to you in email.</p><p>When you receive approval a password will be emailed to you and you will then be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '1' && $user->approved == 1 && $user->confirmed == 1 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOPASS', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your password has been sent to the e-mail address you entered.</p><p>Please check your email (including your spambox). When you receive your password you will then be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '1' && $user->approved == 1 && $user->confirmed == 0 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOPASS_CONF', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your password has been sent to the e-mail address you entered.</p><p>Please check your email (including your spambox). When you receive your password and follow the confirmation instructions you will then be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '0' && $user->approved != 1 && $user->confirmed == 1 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOAPPR', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your sign up request requires approval. Once approved, you will be sent an approval notice to the e-mail address you entered.</p><p>When you receive the approval, you will be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '0' && $user->approved != 1 && $user->confirmed == 0 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOAPPR_CONF', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your sign up request requires email confirmation and approval. Please follow the confirmation steps sent to you in email. Once approved you will be sent an acceptance notice to the e-mail address you entered.</p><p>When you receive approval then you will be able to log in.</p>' );
			}
			elseif ( $ueConfig['emailpass'] == '0' && $user->approved == 1 && $user->confirmed == 0 )
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_CONF', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>An email with further instructions on how to complete your sign up has been sent to the email address you provided.</p><p>Please check your email (including your spambox) to complete your sign up.</p><p>To have the email sent again, simply try logging in with the username and password of your sign up.</p>' );
			}
			else
			{
				$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>You may now log in.</p>' );
			}
		} elseif ( $cause == 'UserConfirmation' ) {
			if ($user->approved != 1)
			{
				$messagesToUser = CBTxt::Th( 'UE_USER_CONFIRMED_NEEDAPPR', 'Thank you for confirming your Email Address.  Your account requires approval by a moderator.  You will receive an email with the outcome of the review.' );
			}
			else
			{
				if ( $ueConfig['emailpass'] == '1' )
				{
					$messagesToUser = CBTxt::Th( 'UE_REG_COMPLETE_NOPASS', '<div class="page-header"><h3>Sign Up Complete!</h3></div><p>Your password has been sent to the e-mail address you entered.</p><p>Please check your email (including your spambox). When you receive your password you will then be able to log in.</p>' );
				}
				else
				{
					$messagesToUser = CBTxt::Th( 'UE_USER_CONFIRMED', 'Your account is now active.  You may now log in!' );
				}
			}
		}

		if ( $messagesToUser ) {
			$messagesToUser = array( 'sys' => $messagesToUser );
			if ( $cause == 'SameUserRegistrationAgain' )
			{
				array_unshift( $messagesToUser, CBTxt::Th( 'UE_YOU_ARE_ALREADY_REGISTERED', 'You are already signed up with this username and password.' ) );
			}
		}
		return $messagesToUser;
	}

	/**
	 * Activates a user
	 * user plugins must have been loaded
	 *
	 * @param  UserTable  $user
	 * @param  int        $ui               1=frontend, 2=backend, 0=no UI: machine-machine UI
	 * @param  string     $cause            (one of: 'UserRegistration', 'UserConfirmation', 'UserApproval', 'NewUser', 'UpdateUser')
	 * @param  boolean    $mailToAdmins     true if the standard new-user email should be sent to admins if moderator emails are enabled
	 * @param  boolean    $mailToUser       true if the welcome new user email (from CB config) should be sent to the new user
	 * @param  boolean    $triggerBeforeActivate
	 * @return array                        Texts to display
	 */
	function activateUser( &$user, $ui, $cause, $mailToAdmins = true, $mailToUser = true, $triggerBeforeActivate = true ) {
		global $ueConfig, $_PLUGINS;

		static $notificationsSent	=	array();

		$activate = ( $user->confirmed && ( $user->approved == 1 ) );
		$showSysMessage = true;

		$savedLanguage				=	CBTxt::setLanguage( $user->getUserLanguage() );
		$messagesToUser				=	getActivationMessage( $user, $cause );
		CBTxt::setLanguage( $savedLanguage );

		if ( $cause == 'UserConfirmation' && $user->approved == 0) {
			$activate = false;
			$msg = array(
				'emailAdminSubject'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_PA_SUB', 'ACTION REQUIRED! New user sign up request pending approval' ) ),
				'emailAdminMessage'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_PA_MSG', "A new user has signed up at [SITEURL] and requires approval.\nThis email contains their details\n\nName - [NAME]\nE-mail - [EMAILADDRESS]\nUsername - [USERNAME]\n\n\nPlease do not respond to this message as it is automatically generated and is for informational purposes only.\n" ) ),
				'emailUserSubject'	=> array( ),
				'emailUserMessage'	=> array( )
			);
		} elseif ( $user->confirmed == 0 ) {
			$msg = array(
				'emailAdminSubject'	=> array( ),
				'emailAdminMessage'	=> array( )
			);
			$savedLanguage				=	CBTxt::setLanguage( $user->getUserLanguage() );
			$msg['emailUserSubject'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_pend_appr_sub'] ) ) );
			$msg['emailUserMessage'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_pend_appr_msg'] ) ) );
			CBTxt::setLanguage( $savedLanguage );
		} elseif ( $cause == 'SameUserRegistrationAgain' ) {
			$activate = false;
			$msg = array(
				'emailAdminSubject'	=> array( ),
				'emailAdminMessage'	=> array( ),
				'emailUserSubject'	=> array( ),
				'emailUserMessage'	=> array( )
			);
		} elseif ( $user->confirmed && ! ( $user->approved == 1 ) ) {
			$msg = array(
				'emailAdminSubject'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_PA_SUB', 'ACTION REQUIRED! New user sign up request pending approval' ) ),
				'emailAdminMessage'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_PA_MSG', "A new user has signed up at [SITEURL] and requires approval.\nThis email contains their details\n\nName - [NAME]\nE-mail - [EMAILADDRESS]\nUsername - [USERNAME]\n\n\nPlease do not respond to this message as it is automatically generated and is for informational purposes only.\n" ) )
			);
			$savedLanguage				=	CBTxt::setLanguage( $user->getUserLanguage() );
			$msg['emailUserSubject'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_pend_appr_sub'] ) ) );
			$msg['emailUserMessage'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_pend_appr_msg'] ) ) );
			CBTxt::setLanguage( $savedLanguage );
		} elseif  ( $user->confirmed && ( $user->approved == 1 ) ) {
			$msg = array(
				'emailAdminSubject'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_SUB', 'New user sign up' ) ),
				'emailAdminMessage'	=> array( 'sys' => CBTxt::T( 'UE_REG_ADMIN_MSG', "A new user has signed up at [SITEURL].\nThis email contains their details\n\nName - [NAME]\nE-mail - [EMAILADDRESS]\nUsername - [USERNAME]\n\n\nPlease do not respond to this message as it is automatically generated and is for information purposes only.\n" ) )
			);
			$savedLanguage				=	CBTxt::setLanguage( $user->getUserLanguage() );
			$msg['emailUserSubject'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_welcome_sub'] ) ) );
			$msg['emailUserMessage'] = array( 'sys' => CBTxt::T( stripslashes( $ueConfig['reg_welcome_msg'] ) ) );
			CBTxt::setLanguage( $savedLanguage );
		}
		$msg['messagesToUser']		=	$messagesToUser;

		if ( $triggerBeforeActivate ) {
			$results = $_PLUGINS->trigger( 'onBeforeUserActive', array( &$user, $ui, $cause, $mailToAdmins, $mailToUser ));
			if( $_PLUGINS->is_errors() && ( $ui != 0 ) ) {
				echo $_PLUGINS->getErrorMSG( '<br />' );
			}

			foreach ( $results as $res ) {
				if ( is_array( $res ) ) {
					$activate		=	$activate			&& $res['activate'];
					$mailToAdmins	=	$mailToAdmins		&& $res['mailToAdmins'];
					$mailToUser		=	$mailToUser		&& $res['mailToUser'];
					$showSysMessage	=	$showSysMessage	&& $res['showSysMessage'];
					foreach ( array_keys( $msg ) as $key ) {
						if ( isset( $res[$key] ) && $res[$key] ) {
							array_push( $msg[$key], $res[$key] );
						}
					}
				}
			}
			if ( ! ( $mailToAdmins && ( $ueConfig['moderatorEmail'] == 1 ) ) ) {
				unset( $msg['emailAdminSubject']['sys'] );
				unset( $msg['emailAdminMessage']['sys'] );
			}
			if ( ! $mailToUser ) {
				unset( $msg['emailUserSubject']['sys'] );
				unset( $msg['emailUserMessage']['sys'] );
			}
			if ( ! $showSysMessage ) {
				unset( $msg['messagesToUser']['sys'] );
			}
		}

		if ( $activate ) {
			$user->block				=	0;
			$user->storeBlock( false );
			$user->removeActivationCode();
		}

		if ( $activate ) {
			$_PLUGINS->trigger( 'onUserActive', array( &$user, $ui, $cause, $mailToAdmins, $mailToUser ) );
			if( $_PLUGINS->is_errors() && ( $ui != 0 ) ) {
				$msg['messagesToUser']	=	$_PLUGINS->getErrorMSG( '<br />' )
					.	$msg['messagesToUser'];
			}
		}
		if ( ! isset( $notificationsSent[$user->id][$user->confirmed][$user->approved][$user->block] ) ) {		// in case done several times (e.g. plugins), avoid resending messages.
			$cbNotification				=	new cbNotification();

			if ( $ueConfig['moderatorEmail'] && count( $msg['emailAdminMessage'] ) ) {
				$pwd					=	$user->password;
				$user->password			=	null;
				$cbNotification->sendToModerators( implode( ', ', $msg['emailAdminSubject'] ),
					$cbNotification->_replaceVariables( implode( '\n\n', $msg['emailAdminMessage'] ), $user ) );
				$user->password			=	$pwd;
			}

			if ( count( $msg['emailUserMessage'] ) ) {
				$cbNotification->sendFromSystem( $user, implode( ', ', $msg['emailUserSubject'] ), implode( '\n\n', $msg['emailUserMessage'] ), true, ( isset( $ueConfig['reg_email_html'] ) ? (int) $ueConfig['reg_email_html']  : 0 ) );
			}
			$notificationsSent[$user->id][$user->confirmed][$user->approved][$user->block]	=	true;
		}
		return $msg['messagesToUser'];
	}

	/**
	 * Page navigation support functions
	 */

	/**
	 * Writes the html links for pages, eg, previous 1 2 3 ... x next
	 *
	 * @deprecated 2.0 use cbPageNav
	 * @see cbPageNav
	 *
	 * @param  int           $limitstart  The record number to start dislpaying from
	 * @param  int           $limit       Number of rows to display per page
	 * @param  int           $total       Total number of rows
	 * @param  string        $baseUrl     Base url (without SEF): cbSef done inside this function
	 * @param  string|array  $search      String: search parameter added as &$prefix.search=... if NOT NULL ; array: each added as $prefix.&key=$val
	 * @param  string        $prefix      Prefix on the &limitstart and &search URL items
	 * @return string
	 */
	function writePagesLinks( $limitstart, $limit, $total, $baseUrl, $search = null, $prefix = null ) {
		global $_PLUGINS;

		$_PLUGINS->trigger( 'onBeforeWritePagesLinks', array( $limitstart, $limit, $total, &$baseUrl, &$search, $prefix ) );

		$pagingUrl						=	$baseUrl;

		if ( $search ) {
			$append						=	( strpos( $baseUrl, '?' ) !== false ? '&' : '?' );

			if ( is_array( $search ) ) {
				foreach ( $search as $k => $v ) {
					if ( ( $k != 'limitstart' ) && $v ) {
						$pagingUrl		.=	$append . urlencode( $prefix . $search ) . '=' . urlencode( $search );

						$append			=	'&';
					}
				}
			} else {
				$pagingUrl				.=	$append . 'search=' . urlencode( $search );
			}
		}

		cbimport( 'cb.pagination' );

		$pageNav						=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( $prefix );
		$pageNav->setBaseURL( $pagingUrl );

		$return							=	$pageNav->getListLinks();

		$_PLUGINS->trigger( 'onAfterWritePagesLinks', array( &$return, $limitstart, $limit, $total, $baseUrl, $search, $prefix ) );

		return $return;
	}

	/**
	 * Gets the limitstart parameter from $arr, that got set in writePagesLinks()
	 *
	 * @since 1.9
	 * @deprecated 2.0 use cbPageNav
	 *
	 * @param  array     $arr
	 * @return int|null
	 */
	function getPagesLimitStart( $arr ) {
		$limitstart			=	cbGetParam( $arr, 'limitstart' );

		if ( ( $limitstart === null ) && ( checkJversion() == 2 ) ) {
			// Joomla 3.0 introduces a wonderful new feature: Break $_GET variables: copy limitstart to start, and unset limitstart:
			$limitstart		=	cbGetParam( $arr, 'start' );
		}
		if ( $limitstart !== null ) {
			$limitstart		=	(int) $limitstart;
		}
		return $limitstart;
	}

	/**
	 * Writes the html for the pages counter, eg, Results 1-10 of x
	 *
	 * @deprecated 2.0 use cbPageNav
	 *
	 * @param  int  $limitstart  The record number to start dislpaying from
	 * @param  int  $limit       Number of rows to display per page
	 * @param  int  $total       Total number of rows
	 */
	function writePagesCounter( $limitstart, $limit, $total ) {
		cbimport( 'cb.pagination' );

		$pageNav	=	new cbPageNav( $total, $limitstart, $limit );

		echo $pageNav->getPagesCounter();
	}

	/**
	 * Date format converter function
	 *
	 * @deprecated 2.0 Use  $_CB_framework->getUTCDate( array( $toFormat, $fromFormat ), $date )
	 *
	 * @param  string  $date        Date in $fromFormat
	 * @param  string  $fromFormat  Format of $date (y,m,d,-,.,/ chars)
	 * @param  string  $toFormat    Format to return
	 * @return string               Date in $toFormat
	 */
	function dateConverter( $date, $fromFormat, $toFormat )
	{
		global $_CB_framework;

		if ( $date == '' || $date == null || !isset( $date ) ) {
			return '';
		}

		return $_CB_framework->getUTCDate( array( $toFormat, $fromFormat ), $date );
	}

	/**
	 * Offsets date-time if time is present and $serverTimeOffset 1, then formats to CB's configured date-time format.
	 *
	 * @param  int|string       $date                In "Y-m-d H:i:s" format, or 	int : unix timestamp
	 * @param  boolean          $serverTimeOffset    False: don't offset, true: offset if time also in $date
	 * @param  bool|string      $showTime            False don't show time even if time is present in string, timeago: show jquery timeago
	 * @param  null|string      $dateFormatOverride  Format override for date display
	 * @param  null|string      $timeFormatOverride  Format override for time display
	 * @param  null|string|int  $offsetOverride      Offset override for time display
	 * @return string
	 */
	function cbFormatDate( $date, $serverTimeOffset = true, $showTime = true, $dateFormatOverride = null, $timeFormatOverride = null, $offsetOverride = null ) {
		global $_CB_framework, $ueConfig;

		$dateTime				=	Application::Date( $date, ( $serverTimeOffset ? ( $offsetOverride ? $offsetOverride : null ) : 'UTC' ) );

		if ( ( $showTime === 'timeago' ) || ( $showTime === 'exacttimeago' ) ) {
			static $JS_loaded	=	0;

			if ( ! $JS_loaded++ ) {
				$js				=	"$.fn.cbtimeago.defaults.strings.future = '" . addslashes( CBTxt::T( 'TIMEAGO_FUTURE', 'in %s' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.past = '" . addslashes( CBTxt::T( 'TIMEAGO_PAST', '%s ago' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.seconds = '" . addslashes( CBTxt::T( 'TIMEAGO_LESS_THAN_A_MINUTE', 'less than a minute' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.minute = '" . addslashes( CBTxt::T( 'TIMEAGO_ABOUT_A_MINUTE', 'about a minute' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.minutes = '" . addslashes( CBTxt::T( 'TIMEAGO_N_MINUTES', '%d minutes' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.hour = '" . addslashes( CBTxt::T( 'TIMEAGO_ABOUTE_ONE_HOUR', 'about an hour' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.hours = '" . addslashes( CBTxt::T( 'TIMEAGO_ABOUT_N_HOURS', 'about %d hours' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.day = '" . addslashes( CBTxt::T( 'TIMEAGO_A_DAY', 'a day' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.days = '" . addslashes( CBTxt::T( 'TIMEAGO_N_DAYS', '%d days' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.month = '" . addslashes( CBTxt::T( 'TIMEAGO_ABOUT_A_MONTH', 'about a month' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.months = '" . addslashes( CBTxt::T( 'TIMEAGO_N_MONTHS', '%d months' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.year = '" . addslashes( CBTxt::T( 'TIMEAGO_ABOUT A_YEAR', 'about a year' ) ) . "';"
								.	"$.fn.cbtimeago.defaults.strings.years = '" . addslashes( CBTxt::T( 'TIMEAGO_N_YEARS', '%d years' ) ) . "';"
								.	"$( '.cbDateTimeago' ).cbtimeago();";

				$_CB_framework->outputCbJQuery( $js, 'cbtimeago' );
			}

			$attributes			=	null;

			if ( $showTime === 'exacttimeago' ) {
				$attributes		.=	' data-cbtimeago-hideago="true"';
			}

			return '<span class="cbDateTimeago"' . $attributes . ' title="' . htmlspecialchars( $dateTime->format( 'c' ) ) . '"></span>';
		} else {
			if ( $dateFormatOverride || $timeFormatOverride || ( ! $showTime ) ) {
				$format			=	( $dateFormatOverride !== null ? $dateFormatOverride : ( CBTxt::T( 'UE_DATE_FORMAT', '' ) != '' ? CBTxt::T( 'UE_DATE_FORMAT', '' ) : $ueConfig['date_format'] ) );

				if ( $showTime ) {
					$format		.=	( $timeFormatOverride !== null ? $timeFormatOverride : ( CBTxt::T( 'UE_TIME_FORMAT', '' ) != '' ? CBTxt::T( 'UE_TIME_FORMAT', '' ) : ' ' . ( isset( $ueConfig['time_format'] ) ? $ueConfig['time_format'] : 'H:i:s' ) ) );
				}
			} else {
				$format			=	null;
			}

			return $dateTime->format( $format );
		}
	}

	/**
	 * Returns formated date according to current local and adds time offset
	 *
	 * @deprecated 2.0 (left because CB contentbot uses it)
	 *
	 * @param  string  $date    In datetime format
	 * @param  string  $format  Optional format for strftime
	 * @param  int     $offset  Time offset if different than global one
	 * @return string           Formated date
	 * @access private
	 */
	function _old_cbFormatDate( $date, $format = "", $offset = null ) {
		global $_CB_framework;

		if ( $format == '' ) {
			// %Y-%m-%d %H:%M:%S
			// These are Joomla defines and this funtion is legacy (deprecated) so the defines can remain safely:
			$format		=	defined( '_DATE_FORMAT_LC' ) ? _DATE_FORMAT_LC : ( defined( 'DATE_FORMAT_LC' ) ? DATE_FORMAT_LC : '%Y-%m-%d %H:%M:%S' );
		}
		if ( is_null( $offset ) ) {
			$offset		=	$_CB_framework->getCfg( 'offset' );
		}
		$regs			=	null;
		if ( $date && preg_match( "/([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})/", $date, $regs ) ) {
			$timezone		=	date_default_timezone_get();
			date_default_timezone_set( 'UTC' );

			$date		=	mktime( $regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1] );
			$date		=	$date > -1 ? strftime( $format, $date + ( $offset * 3600 ) ) : '-';

			date_default_timezone_set( $timezone );
		}
		return $date;
	}

	/**
	 * returns htmlspecialchar( formatted name of user as specified in format )
	 *
	 * @param  string  $name    Name            $user->name
	 * @param  string  $uname   Username        $user->username
	 * @param  string  $format  Format from CB  $ueConfig['name_format']
	 * @return string           Formatted name
	 */
	function getNameFormat($name,$uname,$format) {
		if ( $name || $uname ) {
			switch ($format) {
				case 1 :
					$returnName = cbUnHtmlspecialchars($name);	//TBD: unhtml is kept for backwards database compatibility until CB 2.0
					break;
				case 2 :
					$returnName = cbUnHtmlspecialchars($name)." (".$uname.")";	//TBD: unhtml is kept for backwards database compatibility until CB 2.0
					break;
				case 4 :
					$returnName = $uname." (".cbUnHtmlspecialchars($name).")";	//TBD: unhtml is kept for backwards database compatibility until CB 2.0
					break;
				case 3 :
				default:
					$returnName = $uname;
					break;
			}
		} else {
			$returnName = CBTxt::T( 'UE_UNNAMED_USER', 'Unnamed user' );
		}
		return htmlspecialchars($returnName);
	}

	/**
	 * Formats a field value $oValue for field name $oType for user $user
	 *
	 * @deprecated 1.0 (used only by (and kept only for) ProfileBook 1.3 until it is rewritten)
	 *
	 * @param  string     $oType
	 * @param  string     $oValue
	 * @param  UserTable  $user
	 * @return string
	 */
	function getFieldValue( $oType, $oValue = null, $user = null )
	{
		if ( ( ! $user ) || ( ! $user->id ) ) {
			return '';
		}

		if ( $oType == 'text' ) {
			return htmlspecialchars( $oValue );
		}

		if ( $oType == 'webaddress' ) {
			if ( $oValue == null ) {
				return '';
			}
			if ( Application::Config()->get( 'allow_website' ) == 1 ) {
				$oReturn			=	explode( '|*|', $oValue );
				if ( count( $oReturn ) < 2 ) {
					$oReturn[1]		=	$oReturn[0];
				}

				return '<a href="http://' . htmlspecialchars( $oReturn[0] ) . '" target="_blank" rel="nofollow">' . htmlspecialchars( $oReturn[1] ) . '</a>';
			}
			return htmlspecialchars( $oValue );
		}

		return CBuser::getInstance( $user->id )->getField( $oType, $oValue );
	}

	/**
	 * CORRECTION FOR OLD-STYLE TEMPLATES:
	 */

	/**
	 * Outputs an arbitrary html text into head tags if possible and configured, otherwise echo it.
	 * Adds RTL overrides in 'rtl.css' on 'template.css' file if in RTL output mode from template's rtl.css file if existing, otherwise from default template.
	 *
	 * @param  int     $obsoleteUi    (was int $ui user interface : 1: frontend, 2: backend)
	 * @param  string  $templateFile
	 * @param  string  $media         e.g. "screen"
	 * @return void
	 */
	function outputCbTemplate( /** @noinspection PhpUnusedParameterInspection */ $obsoleteUi = 0, $templateFile = 'template.css', $media = null ) {
		global $_CB_framework, $ueConfig;

		$loadBootstrap				=	( isset( $ueConfig['templateBootstrap'] ) ? (int) $ueConfig['templateBootstrap'] : 1 );
		$loadFontawesome			=	( isset( $ueConfig['templateFontawesme'] ) ? (int) $ueConfig['templateFontawesme'] : 1 );
		$tmplFileExists				=	file_exists( selectTemplate( 'absolute_path' ) . '/' . $templateFile );

		if ( $tmplFileExists ) {
			$livePath				=	selectTemplate( 'relative_path' );
		} else {
			$tmplFileExists			=	file_exists( selectTemplate( 'absolute_path', 'default' ) . '/' . $templateFile );
			$livePath				=	selectTemplate( 'relative_path', 'default' );
		}

		if ( $templateFile === 'template.css' ) {
			if ( $loadBootstrap || ( Application::Cms()->getClientId() ) ) {
				// Add bootstrap styles if available:
				outputCbTemplate( null, 'bootstrap.css', $media );
			}

			if ( $loadFontawesome || ( Application::Cms()->getClientId() ) ) {
				// Add fontawesome styles if available:
				outputCbTemplate( null, 'fontawesome.css', $media );
			}
		}

		if ( $tmplFileExists ) {
			$_CB_framework->document->addHeadStyleSheet( $livePath . '/' . $templateFile, false, $media );
		}

		if ( $templateFile === 'template.css' ) {
			// Add RTL style overrides if available:
			if ( $_CB_framework->document->getDirection() == 'rtl' ) {
				outputCbTemplate( null, 'rtl.css', $media );
			}
		}

		if ( ( $templateFile === 'template.css' ) && ( selectTemplate( 'dir' ) != 'default' ) ) {
			// Add style overrides if available:
			outputCbTemplate( null, 'override.css', $media );
		}
	}

	/**
	 * Outputs an arbitrary html text into head tags if possible and configured, otherwise echo it.
	 *
	 * old param: int $obsoleteUi  (was int user interface : 1: frontend, 2: backend)
	 * @return void
	 */
	function outputCbJs( ) {
		static $needOut	=	1;
		if ( $needOut-- ) {
			global $_CB_framework;
			$_CB_framework->document->addHeadScriptUrl( '/components/com_comprofiler/js/cb12.js', true );
		}
	}

	/**
	 * Autogenerates an URL-compatible title-alias for a title
	 *
	 * @deprecated 2.0 (used by CB Blogs only)
	 *
	 * @param  string  $title  Title
	 * @return string          URL-compatible Alias corresponding to title
	 */
	function cbGetTitleAlias( $title ) {
		$alias	=	str_replace( '-', ' ', $title );
		$alias	=	trim( cbIsoUtf_strtolower( $alias ) );
		$alias	=	preg_replace( '/(\s|[^A-Za-z0-9\-])+/', '-', $alias );
		$alias	=	trim( $alias, '-' );

		return $alias;
	}

	/**
	 * Unescapes from PHP escaping algorythm if magic_quotes are set
	 *
	 * @deprecated 2.0 (kept for B/C during upgrades because up to CBSubs 3.0 uses it in IPN)
	 *
	 * @param  string  string
	 * @return string
	 */
	function cbGetUnEscaped( $string ) {
		if (get_magic_quotes_gpc()==1) {
			// if (ini_get('magic_quotes_sybase')) return str_replace("''","'",$string);
			return ( stripslashes( $string ));			// this does not handle it correctly if magic_quotes_sybase is ON.
		} else {
			return ( $string );
		}
	}

	/**
	 * Unescapes SQL string except % and _ . So it's reverse of $_CB_database->getEscaped...
	 *
	 * @deprecated 2.0 (kept for B/C with CB Profile Gallery)
	 *
	 * @param  string  $string
	 * @return string
	 */
	function cbUnEscapeSQL($string) {
		return str_replace(array("\\0","\\n","\\r","\\\\","\\'","\\\"","\\Z"),array("\x00","\n","\r","\\","'","\"","\x1a"),$string);
	}

	/**
	 * @deprecated CB 1.2.2 (kept in CB 2.0 for CB Latest Views plugin still using it)
	 *
	 * Legacy function: use cbUnHtmlspecialchars instead !
	 */
	if ( ! is_callable( 'unHtmlspecialchars' ) ) {
		function unHtmlspecialchars( $text ) {
			return cbUnHtmlspecialchars( $text );
		}
	}

	/**
	 * Convert HTML entities to plaintext
	 * Rewritten in CB to use CB's own version of html_entity_decode where innexistant or buggy in < joomla 1.5
	 *
	 * @deprecated 2.0 (kept for B/C with CBSubs 3.0.0), use \CBLib\Input\InjectionsFilter::getInstance()->decode( $source )
	 *
	 * @param	string	$source
	 * @return	string	Plaintext string
	 */
	function cb_html_entity_decode_all( $source ) {
		return InjectionsFilter::getInstance()->decode( $source );
	}

	/**
	 * @deprecated CB 1.2.2 : No need to use anymore! kept in 2.0 because of 3 plugins using it
	 *
	 * @param  string  $string
	 * @return string
	 */
	function utf8ToISO( $string ) {
		return $string;
	}

	/**
	 * @deprecated CB 2,0 : No need to use anymore! kept in 2.0 because of 3 plugins using it
	 *
	 * @param $string
	 * @return string
	 */
	function ISOtoUtf8( $string ) {
		return $string;
	}

	/**
	 * Checks if begin of $subject matches a $search string
	 *
	 * @param  string|string[]  $subject  Haystack
	 * @param  string|string[]  $search   Needle
	 * @return boolean                    True if a match is found
	 */
	function cbStartOfStringMatch( $subject, $search ) {
		if ( is_array( $search)) {
			foreach ($search as $s ) {
				if ( substr( $subject, 0, strlen( $s ) ) == $s ) {
					return true;
				}
			}
			return false;
		}
		return( substr( $subject, 0, strlen( $search ) ) == $search );
	}

	/**
	 * UTF8 helper functions
	 * @license    LGPL (http://www.gnu.org/copyleft/lesser.html)
	 * @author     Andreas Gohr <andi@splitbrain.org>
	 */

	/**
	 * Unicode aware replacement for strlen()
	 *
	 * utf8_decode() converts characters that are not in ISO-8859-1
	 * to '?', which, for the purpose of counting, is alright - It's
	 * even faster than mb_strlen.
	 *
	 * @author <chernyshevsky at hotmail dot com>
	 * @see    strlen()
	 * @see    utf8_decode()
	 */
	function cbutf8_strlen($string) {
		return strlen(utf8_decode($string));
	}

	/**
	 * CB's own UTF-8-compatible output-charset-dependent strlen()
	 *
	 * @param  $string  $string  String to check lenght in characters
	 * @return int               Length in characters
	 */
	function cbIsoUtf_strlen( $string ) {
		global $_CB_framework;

		if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
			return cbutf8_strlen( $string );
		} else {
			return strlen( $string );
		}
	}

	/**
	 * Unicode aware replacement for substr()
	 *
	 * @author lmak at NOSPAM dot iti dot gr
	 * @link   http://www.php.net/manual/en/function.substr.php
	 * @see    substr()
	 */
	function cbutf8_substr( $str, $start, $length = null ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $start, $length, 'UTF-8' );
		} else {
			$ar = null;
			preg_match_all("/./u", $str, $ar);

			if($length != null) {
				return join("",array_slice($ar[0],$start,$length));
			} else {
				return join("",array_slice($ar[0],$start));
			}
		}
	}

	/**
	 * CB's own UTF-8-compatible output-charset-dependent substr()
	 *
	 * @param  string  $str
	 * @param  int     $start
	 * @param  int     $length
	 * @return string
	 */
	function cbIsoUtf_substr( $str, $start, $length = null ) {
		global $_CB_framework;

		if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
			return cbutf8_substr(  $str, $start, $length );
		} else {
			if ( $length === null ) {
				return substr( $str, $start );
			} else {
				return substr(  $str, $start, $length );
			}
		}
	}

	/**
	 * Unicode aware replacement for strtolower()
	 *
	 * @see strtolower()
	 */
	function cbutf8_strtolower( $str ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $str, 'UTF-8' );
		} elseif ( function_exists( 'utf8_strtolower' ) ) {
			return utf8_strtolower( $str );
		} else {
			return utf8_encode( strtolower( utf8_decode( $str ) ) );
		}
	}

	/**
	 * CB's own UTF-8-compatible output-charset-dependent strtolower()
	 *
	 * @param  string  $str
	 * @return string
	 */
	function cbIsoUtf_strtolower( $str ) {
		global $_CB_framework;

		if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
			return cbutf8_strtolower( $str );
		} else {
			return strtolower( $str );
		}
	}

	/**
	 * Unicode aware replacement for strtolower()
	 *
	 * @see strtoupper()
	 */
	function cbutf8_strtoupper( $str ) {
		if ( function_exists( 'mb_strtoupper' ) ) {
			return mb_strtoupper( $str, 'UTF-8' );
		} elseif ( function_exists( 'utf8_strtoupper' ) ) {
			return utf8_strtoupper( $str );
		} else {
			return utf8_encode( strtoupper( utf8_decode( $str ) ) );
		}
	}

	/**
	 * CB's own UTF-8-compatible output-charset-dependent strtoupper()
	 *
	 * @param  string  $str
	 * @return string
	 */
	function cbIsoUtf_strtoupper( $str ) {
		global $_CB_framework;

		if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
			return cbutf8_strtoupper( $str );
		} else {
			return strtoupper( $str );
		}
	}

	/**
	 * Reads the files and directories in a directory
	 * Backend-only
	 *
	 * @access private
	 *
	 * @param  string   $path      The file system path
	 * @param  string   $filter    A filter for the names
	 * @param  boolean  $recurse   Recurse search into sub-directories
	 * @param  boolean  $fullpath  True if to prepend the full path to the file name
	 * @return array               List of files
	 */
	function cbReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  ) {
		$arr						=	array();
		if ( ! @is_dir( $path ) ) {
			return $arr;
		}
		$handle						=	opendir( $path );

		while ( true == ( $file = readdir( $handle ) ) ) {
			if ( ! in_array( $file, array( '.', '..', '.svn', '.git', '.gitignore', '.gitattributes', '__MACOSX' ) ) ) {
				$dir				=	_cbPathName( $path . '/' . $file, false );
				if ( preg_match( "/$filter/", $file ) ) {
					if ( $fullpath ) {
						$arr[]		=	trim( _cbPathName( $path . '/' . $file, false ) );
					} else {
						$arr[]		=	trim( $file );
					}
				}
				if ( $recurse && is_dir( $dir ) ) {
					$arr2			=	cbReadDirectory( $dir, $filter, $recurse, $fullpath );
					if ( ! $fullpath ) {
						foreach ( $arr2 as $k => $n ) {
							$arr2[$k]	=	$file . '/' . $n;
						}
					}
					$arr			=	array_merge( $arr, $arr2 );
				}
			}
		}
		closedir( $handle );
		asort( $arr );
		return $arr;
	}

	/**
	 * Function to strip additional / or \ in a path name
	 * Backend-only
	 * @access private
	 *
	 * @param  string   $p_path              The path
	 * @param  boolean  $p_addtrailingslash  Add trailing slash
	 * @return string
	 */
	function _cbPathName( $p_path, $p_addtrailingslash = true ) {
		if ( substr( PHP_OS, 0, 3 ) == 'WIN' )	{
			$f				=	'/';
			$t				=	'\\';
		} else {
			$f				=	'\\';
			$t				=	'/';
		}

		$retval				=	str_replace( $f, $t, $p_path );						// fix /
		if ( $p_addtrailingslash ) {
			if ( substr( $retval, -1 ) != $t ) {
				$retval		.=	$t;
			}
		}
		$prepend			=	( substr( $retval, 0, 2 ) == $t . $t ) ? $t : '';	// check for UNC path //
		$retval				=	$prepend . str_replace( $t . $t, $t, $retval );		// Remove double // while keeping UNC if needed
		return $retval;
	}

	/**
	 * Prepares and includes CBs Tooltip JavaScript
	 *
	 * @param  int   $obsoleteUi  No longer in use (1 = frontend and 2 = backend)
	 * @param  int   $width       The default tooltip width
	 * @return null
	 */
	function initToolTip( /** @noinspection PhpUnusedParameterInspection */ $obsoleteUi = 0, $width = 250 ) {
		static $cache		=	false;

		if ( ! $cache ) {
			global $_CB_framework;

			$js				=	"$.fn.cbtooltip.defaults.classes = 'cb_template cb_template_" . addslashes( selectTemplate( 'dir' ) ) . "';"
							.	"$.fn.cbtooltip.defaults.buttonClose = '" . addslashes( CBTxt::T( 'TOOLTIP_CLOSE CLOSE', 'Close' ) ) . "';"
							.	"$.fn.cbtooltip.defaults.buttonYes = '" . addslashes( CBTxt::T( 'TOOLTIP_YES', 'Ok' ) ) . "';"
							.	"$.fn.cbtooltip.defaults.buttonNo = '" . addslashes( CBTxt::T( 'TOOLTIP_NO', 'Cancel' ) ) . "';"
							.	( $width ? "$.fn.cbtooltip.defaults.width = " . (int) $width . ";" : null )
							.	"$( window ).load( function() {"
							.		"$( '.cbTooltip,[data-hascbtooltip=\"true\"]' ).cbtooltip();"
							.	"});";

			$_CB_framework->outputCbJQuery( $js, 'cbtooltip' );

			// TODO: Remove the below when overlib usages are completely gone
			$postScript		=	'overlib_pagedefaults(WIDTH,'.(int) $width.',VAUTO,RIGHT,AUTOSTATUSCAP, CSSCLASS,'
				.	'TEXTFONTCLASS,\'cb-tips-font\',FGCLASS,\'cb-tips-fg\',BGCLASS,\'cb-tips-bg\''
				.	',CAPTIONFONTCLASS,\'cb-tips-capfont\', CLOSEFONTCLASS, \'cb-tips-closefont\', CONTAINERCLASS, \'cb_template cb_template_' . selectTemplate( 'dir' ) . '\');'
			;
			$_CB_framework->document->addHeadScriptUrl( '/components/com_comprofiler/js/overlib_all_mini.js', false, null, $postScript );

			$cache			=	true;
		}

		return null;
	}

	/**
	 * Renders a CB Tooltip
	 *
	 * @param int $ui determines tooltip image location (1 = frontend and 2 = backend)
	 * @param string $tooltip tooltip content message
	 * @param null|string $title tooltip title
	 * @param null|int|array $size tooltip size (single int width or array of width and height)
	 * @param string $image tooltip icon
	 * @param null|string $html tooltip custom html (overrides image display)
	 * @param null|string $href tooltip url
	 * @param null|string $attributes custom tooltip element html attributes
	 * @param null|string $imageAttributes custom tooltip image html attributes
	 * @return string
	 */
	function cbTooltip( $ui, $tooltip, $title = null, $size = null, $image = null, $html = '<span class="fa fa-info-circle"></span>', $href = null, $attributes = null, $imageAttributes = null ) {
		global $_CB_framework;

		static $init				=	0;

		if ( ! $init++ ) {
			initToolTip();
		}

		if ( $image ) {
			if ( preg_match( '%^/%', $image ) ) {
				$imagePath			=	$_CB_framework->getCfg( 'live_site' ) . $image;
			} elseif ( preg_match( '/^http/i', $image ) ) {
				$imagePath			=	$image;
			} else {
				$imagePath			=	selectTemplate( $ui ) . $image;
			}
		} else {
			$imagePath				=	null;
		}

		if ( strpos( $attributes, 'data-hascbtooltip="true"' ) !== false ) {
			$class					=	null;
		} else {
			$class					=	'cbTooltip';
		}

		if ( $attributes ) {
			$attributes				=	trim( stripslashes( $attributes ) );

			if ( preg_match( '/class=(?:\'|")([^\'"]+)(?:\'|")/', $attributes, $classes ) ) {
				$attributes			=	preg_replace( '/class=(?:\'|")([^\'"]+)(?:\'|")/', '', $attributes );

				$class				.=	' ' . $classes[1];
			}
		}

		if ( $imageAttributes ) {
			$imageAttributes		=	trim( stripslashes( $imageAttributes ) );
		}

		if ( $size ) {
			if ( is_array( $size ) ) {
				if ( count( $size ) > 1 ) {
					$width			=	( $size[0] == 'auto' ? false : ( $size[0] ? $size[0] : null ) );
					$height			=	( $size[1] == 'auto' ? false : ( $size[1] ? $size[1] : null ) );
				} else {
					$width			=	( $size[0] == 'auto' ? false : ( $size[0] ? $size[0] : null ) );
					$height			=	null;
				}
			} else {
				$width				=	( $size == 'auto' ? false : ( $size ? $size : null ) );
				$height				=	null;
			}
		} else {
			$width					=	null;
			$height					=	null;
		}

		$toolTipAttributes			=	( $class ? ' class="' . htmlspecialchars( $class ) . '"' : null )
			.	' data-cbtooltip-tooltip="' . htmlspecialchars( stripslashes( $tooltip ) ) . '"'
			.	( $title ? ' data-cbtooltip-title="' . htmlspecialchars( stripslashes( $title ) ) . '"' : null )
			.	( $width !== null ? ' data-cbtooltip-width="' . htmlspecialchars( $width ) . '"' : null )
			.	( $height !== null ? ' data-cbtooltip-height="' . htmlspecialchars( $height ) . '"' : null )
			.	( $attributes ? ' ' . $attributes : null );

		if ( $image || $html || $href ) {
			if ( $href ) {
				if ( ! $html ) {
					if ( $imagePath ) {
						$html		=	'<img src="' . htmlspecialchars( $imagePath ) . '""' . ( $imageAttributes ? ' ' . $imageAttributes : null ) . ' />';
					} else {
						$html		=	$href;
					}
				}

				$return				=	'<a href="' . htmlspecialchars( $href ) . '"' . $toolTipAttributes . '>' . $html . '</a>';
			} else {
				if ( $html ) {
					$return			=	'<span' . $toolTipAttributes . '>' . $html . '</span>';
				} elseif ( $imagePath ) {
					$return			=	'<img src="' . htmlspecialchars( $imagePath ) . '"' . $toolTipAttributes . ( $imageAttributes ? ' ' . $imageAttributes : null ) . ' />';
				} else {
					$return			=	null;
				}
			}
		} else {
			$return					=	$toolTipAttributes;
		}

		return $return;
	}

	/**
	 * Renders a CB Tooltip
	 *
	 * @param int $ui determines tooltip image location (1 = frontend and 2 = backend)
	 * @param string $tooltip tooltip content message
	 * @param null|string $title tooltip title
	 * @param null|int|array $size tooltip size (single int width or array of width and height)
	 * @param string $image tooltip icon
	 * @param null|string $html tooltip custom html (overrides image display)
	 * @param null|string $href tooltip url
	 * @param null|string $attributes custom tooltip element html attributes
	 * @param null|string $imageAttributes custom tooltip image html attributes
	 * @return string
	 * @deprecated 2.0 use cbTooltip
	 */
	function CB45_mosToolTip( $ui, $tooltip, $title = null, $size = null, $image = null, $html = '<span class="fa fa-info-circle"></span>', $href = null, $attributes = null, $imageAttributes = null ) {
		return cbTooltip( $ui, $tooltip, $title, $size, $image, $html, $href, $attributes, $imageAttributes );
	}

	/**
	 * Renders a CB Field Tooltip (auto adds image Alt)
	 *
	 * @param int $ui determines tooltip image location (1 = frontend and 2 = backend)
	 * @param string $fieldTip tooltip content message
	 * @param null|string $tipTitle tooltip title
	 * @param null|int|array $size tooltip size (single int width or array of width and height)
	 * @param string $image tooltip icon
	 * @param null|string $html tooltip custom html (overrides image display)
	 * @param null|string $href tooltip url
	 * @param null|string $attributes custom tooltip element html attributes
	 * @return string
	 */
	function cbFieldTip( $ui, $fieldTip, $tipTitle = null, $size = null, $image = null, $html = '<span class="fa fa-info-circle"></span>', $href = null, $attributes = null ) {
		$imageAttributes	=	'alt="' . htmlspecialchars( CBTxt::T( 'UE_INFORMATION_FOR_FIELD FIELD_ICON_INFORMATION_FOR_FIELD', 'Information for: [FIELDTITLE] : [FIELDDESCRIPTION]', array( '[FIELDTITLE]' => $tipTitle, '[FIELDDESCRIPTION]' => $fieldTip ) ) ) . '"';

		return cbTooltip( $ui, $fieldTip, $tipTitle, $size, $image, $html, $href, $attributes, $imageAttributes );
	}

	/**
	 * Shows tooltip icons or explanation line for fields
	 *
	 * @param int          $ui            =1 front-end, =2 back-end
	 * @param boolean|int  $oReq          =true|1: field required
	 * @param boolean|int  $oProfile      =true|1: on profile, =false|0: not on profile, =null: icon not shown at all
	 * @param string       $oDescription  description to show in tooltip ove a i (if any)
	 * @param string       $oTitle        Title of description to show in tooltip
	 * @param boolean      $showLabels    Description to show in tooltip : TRUE: show info of labels, 2: show info but not about the 'i';
	 * @param int|null     $display       Display type
	 * @return string                     HTML code.
	 */
	function getFieldIcons( $ui, $oReq, $oProfile, $oDescription = "", $oTitle = "", $showLabels = false, $display = null ) {
		global $ueConfig;

		if ( $display == '' ) {
			if ( isset( $ueConfig['icons_display'] ) ) {
				$display		=	$ueConfig['icons_display'];
			} else {
				$display		=	3;
			}
		}

		$return					=	null;

		if ( in_array( $display, array( 1, 3, 5, 7, 9, 11 ) ) ) {
			if ( $oReq ) {
				$return			.=	' ' . cbFieldTip( $ui, CBTxt::Th( 'UE_FIELDREQUIRED FIELD_ICON_REQUIRED_TOOLTIP', 'This Field is required' ), null, null, null, '<span class="fa fa-star text-muted"></span>' );
			}

			if ( $showLabels ) {
				$return			.=	' ' . CBTxt::Th( 'UE_FIELDREQUIRED_SHORT FIELD_ICON_REQUIRED_LABEL', 'Required field' ) . ( ( $display > 1 ) && ( ( $oProfile !== null ) && ( $showLabels !== 2 ) ) ? ' | ' : '' );
			}
		}

		if ( in_array( $display, array( 2, 3, 6, 7, 10, 11 ) ) ) {
			if ( $oProfile !== null ) {
				if ( $oProfile ) {
					$return		.=	' ' . cbFieldTip( $ui, CBTxt::Th( 'UE_FIELDONPROFILE FIELD_ICON_VISIBLE_ON_PROFILE_TOOLTIP', 'This Field IS visible on profile' ), null, null, null, '<span class="fa fa-eye text-muted"></span>' );
				}

				if ( $showLabels ) {
					$return		.=	' ' . CBTxt::Th( 'UE_FIELDNOPROFILE FIELD_ICON_VISIBLE_ON_PROFILE_LABEL', 'Field visible on your profile' ) . " | ";
				}

				if ( ( ! $oProfile ) || $showLabels ) {
					$return		.=	' ' . cbFieldTip( $ui, CBTxt::Th( 'UE_FIELDNOPROFILE_SHORT FIELD_ICON_NOT_VISIBLE_ON_PROFILE_TOOLTIP', 'This Field IS NOT visible on profile' ), null, null, null, '<span class="fa fa-eye-slash text-muted"></span>' );
				}

				if ( $showLabels ) {
					$return		.=	' ' . CBTxt::Th( 'UE_FIELDNOPROFILE_SHORT FIELD_ICON_NOT_VISIBLE_ON_PROFILE_LABEL', 'Field <strong>not</strong> visible on profile' ) . ( $display > 3 ? ' | ' : '' );
				}
			}
		}

		if ( in_array( $display, array( 4, 5, 6, 7, 8, 9, 10, 11 ) ) ) {
			if ( $oDescription ) {
				if ( in_array( $display, array( 8, 9, 10, 11 ) ) ) {
					$return		.=	' <span class="cbFieldDescription">' . CBTxt::Th( $oDescription ) . '</span>';
				} else {
					$return		.=	' ' . cbFieldTip( $ui, CBTxt::Th( $oDescription ), CBTxt::T( $oTitle ), null, null, '<span class="fa fa-info-circle text-muted"></span>' );
				}
			}

			if ( $showLabels === true ) {
				$return			.=	' ' . cbFieldTip(
						$ui,
						CBTxt::Th( 'UE_FIELDDESCRIPTION FIELD_ICON_FIELD_DESCRIPTION_MOUSEOVER_INSTRUCTION_TOOLTIP', 'Field description: Move mouse over icon' ),
						null,
						null,
						null,
						'<span class="fa fa-info-circle text-muted"></span>' ) . ' ' . CBTxt::Th( 'UE_FIELDDESCRIPTION_SHORT FIELD_ICON_FIELD_DESCRIPTION_MOUSEOVER_INSTRUCTION_LABEL', 'Information: Point mouse to icon' );
			}
		}

		return '<span class="cbFieldIcons' . ( ( $showLabels ) ? 'Labels' : null ) .'">' . $return . '</span>';
	}

	/**
	 * Replaces [fieldname] by the content of the user row (except for [password])
	 *
	 * @param  string               $msg
	 * @param  UserTable|\stdClass  $row
	 * @param  boolean|array        $htmlspecialchars  on replaced values only: FALSE : no htmlspecialchars, TRUE: do htmlspecialchars, ARRAY: callback method
	 * @param  boolean              $menuStats
	 * @param  array                $extraStrings
	 * @param  boolean              $translateLanguage  on $msg only
	 * @return string
	 */
	function cbReplaceVars( $msg, $row, $htmlspecialchars = true, $menuStats = true, $extraStrings = null, $translateLanguage = true ){
		if ( $extraStrings === null ) {
			$extraStrings	=	array();
		}
		if ( isset( $row->id ) && is_object( $row ) && ( $row instanceof UserTable ) ) {
			$cbUser			=&	CBuser::getInstance( $row->id );
		} else {
			$cbUser			=	new CBuser();
			$cbUser->loadCbRow( $row );
		}
		return $cbUser->replaceUserVars( $msg, $htmlspecialchars, $menuStats, $extraStrings, $translateLanguage );
	}

	/**
	 * Random string of a-z,A-Z,0-9 generator
	 *
	 * @param  int      $stringLength  Number of chars
	 * @param  boolean  $noCaps        Only lowercase letters and numbers ?
	 * @return string                  Password
	 */
	function cbMakeRandomString( $stringLength = 8, $noCaps = false ) {
		global $_CB_framework;

		if ( $noCaps ) {
			$chars		=	'abchefghjkmnpqrstuvwxyz0123456789';
		} else {
			$chars		=	'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		$len			=	strlen( $chars );
		$rndString		=	'';

		$stat			=	@stat( __FILE__ );
		if ( ! is_array( $stat ) ) {
			$stat		=	array();
		}
		$stat[]			=	@php_uname();
		$stat[]			=	uniqid( '', true );
		$stat[]			=	microtime();
		$stat[]			=	$_CB_framework->getCfg( 'secret' );
		$stat[]			=	mt_rand( 0, mt_getrandmax() );
		mt_srand( crc32( implode( ' ', $stat ) ) );

		for ( $i = 0; $i < $stringLength; $i++ ) {
			$rndString	.=	$chars[mt_rand( 0, $len - 1 )];
		}
		return $rndString;
	}

	/**
	 * CB registration spam protections
	 *
	 * @param  int     $decrement
	 * @param  string  $salt0
	 * @param  string  $salt1
	 * @return array
	 */
	function cbGetRegAntiSpams( $decrement = 0, $salt0 = null, $salt1 = null ) {
		global $_CB_framework;
		if ( ( $salt0 === null ) || ( $salt1 === null ) ) {
			static $formSalt	=	null;
			if ( $formSalt === null ) {
				$formSalt		=	cbMakeRandomString( 16 );
			}
			$salt0				=	$formSalt;
			$salt1				=	$formSalt;
		}
		$time				 	=	time();
		$valtime				=	( (int) ( $time / 10800 )) - $decrement;
		// no IP addresses here, since on AOL it changes all the time.... $hostIPs = cbGetIParray();
		if ( ( strlen( $salt0 ) == 16 ) && ( strlen( $salt1 ) == 16 ) ) {
			$validate = array();
			$validate[0]		=	'cbrv1_' . md5( $salt0 . $_CB_framework->getCfg('secret') . $valtime ) . '_' . $salt0;
			$validate[1]		=	'cbrv1_' . md5( $salt1 . $_CB_framework->getCfg( 'db' )   . $valtime ) . '_' . $salt1;
			return $validate;
		} else {
			_cbExpiredSessionJSterminate();
			exit;
		}
	}

	/**
	 * @access private
	 *
	 * @return string
	 */
	function cbGetRegAntiSpamFieldName() {
		return 'cbrasitway';
	}

	/**
	 * @access private
	 *
	 * @return string
	 */
	function cbGetRegAntiSpamCookieName() {
		return 'cbrvs';
	}

	/**
	 * Gets the HTML hidden input for registration anti-spam
	 *
	 * @param  array|null  $cbGetRegAntiSpams
	 * @return string
	 */
	function cbGetRegAntiSpamInputTag( $cbGetRegAntiSpams = null ) {
		if ( $cbGetRegAntiSpams === null ) {
			$cbGetRegAntiSpams		=	cbGetRegAntiSpams();
		}
		cbimport( 'cb.session' );
		CBCookie::setcookie( cbGetRegAntiSpamCookieName(), $cbGetRegAntiSpams[1], false );
		return "<input type=\"hidden\" name=\"" . cbGetRegAntiSpamFieldName() ."\" value=\"" .  $cbGetRegAntiSpams[0] . "\" />\n";
	}

	/**
	 * Checks the registration anti-spam
	 *
	 * @param  int      $mode  What to do if it fails: 2: Return false, 1: Terminates with exit
	 * @return boolean
	 */
	function cbRegAntiSpamCheck( $mode = 1 ) {
		global $_POST;

		$validateValuePost	 		=	cbGetParam( $_POST, cbGetRegAntiSpamFieldName() );
		$validateCookieName			=	cbGetRegAntiSpamCookieName();
		if ( $validateCookieName === false ) {
			$i						=	2;
		} else {
			cbimport( 'cb.session' );
			$validateValueCookie	=	CBCookie::getcookie( $validateCookieName );
			$parts0					=	explode( '_', $validateValuePost );
			$parts1					=	explode( '_', $validateValueCookie );
			if ( ( count( $parts0 ) == 3 ) && ( count( $parts1 ) == 3 ) ) {
				for($i = 0; $i < 2; $i++) {
					$validate		=	cbGetRegAntiSpams( $i, $parts0[2], $parts1[2] );
					if ( ( $validateValuePost == $validate[0] ) && ( $validateValueCookie == $validate[1] ) ) {
						break;
					}
				}
			} else {
				$i					=	2;
			}
		}
		if ( $i == 2 ) {
			if ( $mode == 2 ) {
				return false;
			}
			_cbExpiredSessionJSterminate( 200 );
			exit;
		}
		return true;
	}

	/**
	 * CB messaging spam protections:
	 *
	 * @param null $salt0
	 * @param null $salt1
	 * @param bool $allowPublic
	 * @return array
	 */
	function cbGetAntiSpams( $salt0 = null, $salt1 = null, $allowPublic = false ) {
		global $_CB_framework;

		if ( ( $salt0 === null ) || ( $salt1 === null ) ) {
			$salt0						=	cbMakeRandomString( 32 );
			$salt1						=	$salt0;
		}

		$myId							=	(int) $_CB_framework->myId();

		if ( ( ! $myId ) && $allowPublic ) {
			$messageNumberSent			=	(int) $_CB_framework->getUserState( 'cb_message_number_sent', 0 );
			$messageLastSent			=	$_CB_framework->getUserState( 'cb_message_last_sent', '0000-00-00 00:00:00' );
			$canSendMessage				=	true;
		} else {
			$user						=	CBuser::getMyUserDataInstance();

			if ( $user ) {
				$messageNumberSent		=	(int) $user->message_number_sent;
				$messageLastSent		=	$user->message_last_sent;
				$canSendMessage			=	true;
			} else {
				$messageNumberSent		=	0;
				$messageLastSent		=	'0000-00-00 00:00:00';
				$canSendMessage			=	false;
			}
		}

		if ( ( strlen( $salt0 ) == 32 ) && ( strlen( $salt1 ) == 32 ) && $canSendMessage ) {
			$validate					=	array();
			$validate[0]				=	'cbsv1_' . md5( $salt0 . $_CB_framework->getCfg('secret') .  $_CB_framework->getCfg( 'db' ) . $messageNumberSent . $messageLastSent . $_CB_framework->myId() )       . '_' . $salt0;
			$validate[1]				=	'cbsv1_' . md5( $salt1 . $_CB_framework->getCfg('secret') .  $_CB_framework->getCfg( 'db' ) . $messageNumberSent . $messageLastSent . $_CB_framework->myUsername() ) . '_' . $salt1;

			return $validate;
		} else {
			_cbExpiredSessionJSterminate();
			exit;
		}
	}

	/**
	 * Returns HTML hidden input tag for messaging anti-spam
	 *
	 * @param  string|null  $salt0
	 * @param  string|null  $salt1
	 * @param  boolean      $allowPublic
	 * @return string
	 */
	function cbGetAntiSpamInputTag( $salt0 = null, $salt1 = null, $allowPublic = false ) {
		$validate	=	cbGetAntiSpams( $salt0, $salt1, $allowPublic );

		cbimport( 'cb.session' );
		CBCookie::setcookie( 'cbvs', $validate[1], false );

		return "<input type=\"hidden\" name=\"cbvssps\" value=\"" .  $validate[0] . "\" />\n";
	}

	/**
	 * Checks messaging anti-spam
	 *
	 * @param  boolean      $autoBack     TRUE: returns code 403 and attempts a "back" in browser with Javascript, FALSE: Returns error text
	 * @param  boolean      $allowPublic  TRUE: Also checks for guests, FALSE: Only for registered and logged-in users
	 * @return null|string                NULL: Ok, String: translated error text
	 */
	function cbAntiSpamCheck( $autoBack = true, $allowPublic = false ) {
		global $_POST;

		$validateValuePost	 	=	cbGetParam( $_POST, 'cbvssps', '' );

		cbimport( 'cb.session' );

		$validateValueCookie	=	CBCookie::getcookie( 'cbvs' );
		$parts0					=	explode( '_', $validateValuePost );
		$parts1					=	explode( '_', $validateValueCookie );

		$match					=	false;

		if ( ( count( $parts0 ) == 3 ) && ( count( $parts1 ) == 3 ) ) {
			$validate			=	cbGetAntiSpams( $parts0[2], $parts1[2], $allowPublic );
			$match				=	( $validateValuePost === $validate[0] ) || ( $validateValueCookie === $validate[1] );
		}

		if ( ! $match ) {
			if ( $autoBack ) {
				_cbExpiredSessionJSterminate();
			} else {
				return CBTxt::Th( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' )
				. ' ' . CBTxt::Th( 'UE_PLEASE_REFRESH', 'Please refresh/reload page before filling-in.' );
			}
		}

		return null;
	}

	/**
	 * CB messaging anti-spam protection for maximum messages per time-frame
	 *
	 * @param  int          $userId       User id
	 * @param  boolean      $count        Should it increment the number of messages or just check ?
	 * @param  boolean      $allowPublic  Should public messaging also be allowed ?
	 * @return null|string
	 */
	function cbSpamProtect( $userId, $count, $allowPublic = false ) {
		global $_CB_framework, $_CB_database, $ueConfig;

		$maxEmailsPerHr							=	( isset( $ueConfig['maxEmailsPerHr'] ) ? (int) $ueConfig['maxEmailsPerHr'] : 10 );		// mails per
		$maxInterval							=	( 24 * 3600 );	// hours (expressed in seconds) limit
		$time									=	time();

		if ( ( ! $userId ) && $allowPublic ) {
			$messageNumberSent					=	(int) $_CB_framework->getUserState( 'cb_message_number_sent', 0 );
			$messageLastSent					=	$_CB_framework->getUserState( 'cb_message_last_sent', '0000-00-00 00:00:00' );
			$canSendMessage						=	true;
		} else {
			$user								=	CBuser::getUserDataInstance( (int) $userId );

			if ( $user ) {
				$messageNumberSent				=	(int) $user->message_number_sent;
				$messageLastSent				=	$user->message_last_sent;
				$canSendMessage					=	true;
			} else {
				$messageNumberSent				=	0;
				$messageLastSent				=	'0000-00-00 00:00:00';
				$canSendMessage					=	false;
			}
		}

		if ( $canSendMessage ) {
			if ( $messageLastSent != '0000-00-00 00:00:00' ) {
				list( $y, $c, $d, $h, $m, $s )	=	sscanf( $messageLastSent, "%4d-%2d-%2d\t%2d:%2d:%2d" );

				$expiryTime						=	( gmmktime( $h, $m, $s, $c, $d, $y ) + $maxInterval );

				if ( $time < $expiryTime ) {
					if ( $messageNumberSent >= $maxEmailsPerHr ) {
						return CBTxt::Th( 'UE_MAXEMAILSLIMIT', 'You exceeded the maximum limit of ||one email|%%NUMBERMAILSPERHOUR%% emails|| per hour| every %%NUMBERHOURS%% hours||. Please try again later.',
							array( '%%NUMBERMAILSPERHOUR%%' => $maxEmailsPerHr, '%%NUMBERHOURS%%' => round( $maxInterval / 3600 ) ) );
					} else {
						if ( $count ) {
							if ( $userId ) {
								$query			=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
									.	"\n SET " . $_CB_database->NameQuote( 'message_number_sent' ) . " = " . (int) ( $messageNumberSent + 1 )
									.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $userId;
								$_CB_database->setQuery( $query );
								$_CB_database->query();
							} else {
								$_CB_framework->setUserState( 'cb_message_number_sent', ( $messageNumberSent + 1 ) );
							}
						}
					}
				} else {
					if ( $count ) {
						if ( $userId ) {
							$query				=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
								.	"\n SET " . $_CB_database->NameQuote( 'message_number_sent' ) . " = 1"
								.	', ' . $_CB_database->NameQuote( 'message_last_sent' ) . ' = ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
								.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $userId;
							$_CB_database->setQuery( $query );
							$_CB_database->query();
						} else {
							$_CB_framework->setUserState( 'cb_message_number_sent', 1 );
							$_CB_framework->setUserState( 'cb_message_last_sent', $_CB_framework->getUTCDate() );
						}
					}
				}
			} else {
				if ( $count ) {
					if ( $userId ) {
						$query					=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
							.	"\n SET " . $_CB_database->NameQuote( 'message_number_sent' ) . " = 1"
							.	', ' . $_CB_database->NameQuote( 'message_last_sent' ) . ' = ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
							.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $userId;
						$_CB_database->setQuery( $query );
						$_CB_database->query();
					} else {
						$_CB_framework->setUserState( 'cb_message_number_sent', 1 );
						$_CB_framework->setUserState( 'cb_message_last_sent', $_CB_framework->getUTCDate() );
					}
				}
			}

			return null;
		} else {
			return 'Not Authorized';
		}
	}

	/**
	 * Deletes a user without any check or warning, and related reports, sessions
	 *
	 * @deprecated 2.0 Use UserTable()->load( $condition or $id )->delete( null, $cbUserOnly )
	 *
	 * @param  int      $id                 User id
	 * @param  string   $condition          ONLY allowed string: "return (\$user->block == 1);" (CBSubs 3.0.0) php condition string on $user e.g. "return (\$user->block == 1);"
	 * @param  boolean  $inComprofilerOnly  deletes user only in CB, not in Mambo/Joomla
	 * @return null|boolean|string          '' if user deleted and found ok, NULL if user not found, FALSE if condition was not met, STRING error in case of error raised by plugin
	 */
	function cbDeleteUser ( $id, $condition = null, $inComprofilerOnly = false ) {
		if ( ! $id ) {
			return null;
		}

		$user	=	new UserTable();

		if ( $inComprofilerOnly ) {
			$user->load( array( 'user_id' => (int) $id ) );
		} else {
			$user->load( (int) $id );
		}

		if ( ! $user->id ) {
			return null;
		}

		if ( ( $condition == null ) || eval( $condition ) ) {
			if ( ! $user->delete( (int) $id, $inComprofilerOnly ) ) {
				return $user->getError();
			}

			return '';
		}

		return false;
	}

	/**
	 * Computes page title, sets page title and pathway
	 *
	 * @param  UserTable  $user
	 * @param  string              $thisUserTitle    Title if it's the user displaying
	 * @param  string              $otherUserTitle   Title if it's another user displayed
	 * @return string    title (plaintext, without htmlspecialchars or slashes)
	 */
	function cbSetTitlePath( $user, $thisUserTitle, $otherUserTitle ) {
		global $ueConfig, $_CB_framework;

		$title			=	null;

		if ( $_CB_framework->myId() == $user->id ) {
			if ( $thisUserTitle ) {
				$title	=	$thisUserTitle;
			}
		} else {
			if ( $otherUserTitle ) {
				$name	=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );
				$title	=	sprintf( $otherUserTitle, $name );
			}
		}

		if ( $title ) {
			$_CB_framework->setPageTitle( $title );
			$_CB_framework->appendPathWay( htmlspecialchars( $title ) );
		}

		return $title;
	}

	/**
	 * redirects a user to a/his profile or a given task with a given tab
	 * @param null|int $uid
	 * @param null|string $message
	 * @param null|string $task
	 * @param null|string $tab
	 */
	function cbRedirectToProfile( $uid, $message, $task = null, $tab = null ) {
		global $_CB_framework;

		if ( ! $task ) {
			$task			=	'userprofile';
		}

		$redirectURL		=	'index.php?option=com_comprofiler&view=' . urlencode( $task );

		if ( $_CB_framework->myId() != $uid ) {
			$redirectURL	.=	'&user=' . urlencode( $uid );
		}

		if ( $tab ) {
			$redirectURL	.=	'&tab=' . urlencode( $tab );
		}

		$redirectURL		.=	getCBprofileItemid( false, $task );

		cbRedirect( cbSef( $redirectURL, false ), $message );
	}

	/**
	 * Adds links to the keywords in the teamCredits() text, one after the other
	 *
	 * @return string
	 */
	function teamCreditsReplacer( ) {
		static $index = 0;
		$l = array( '/community-builder', '/social-networking', '/joomla', '/membership-management',
			'http://extensions.joomla.org/extensions/clients-a-communities/communities/210', 'http://extensions.joomla.org/extensions/clients-a-communities/communities/210',
			'/joomla-templates',
			'/cb-solutions/add-ons', '/cb-solutions/cbsubs', '/cb-solutions/cbsubs', '/cb-solutions/incubator',
			'/online-social-network', '/hosting' );
		return '<a href="'
			. ( isset( $l[$index] ) && $l[$index][0] == '/' ? 'http://www.joomlapolis.com' : '' )
			. ( isset( $l[$index] ) ? $l[$index++] : '/' )
			. ( isset( $l[$index - 1] ) && $l[$index - 1][0] == '/' ? '?pk_campaign=in-cb&amp;pk_kwd=credits' : '' )
			. '" target="_blank">';
	}

	/**
	 * Gives credits display for frontend and backend
	 */
	function teamCredits() {
		global $_CB_framework, $ueConfig;

		$ui		=	$_CB_framework->getUi();

		outputCbTemplate( $ui );
		outputCbJs( $ui );

		?>
		<div class="cbTeamCredits cb_template cb_template_<?php echo selectTemplate( 'dir' ); ?>">
			<div class="container-fluid">
				<div class="row text-center">
					<p>
						<?php
						if ( $ui == 2 ) {
						?>
							<a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits" target="_blank">
								<img src="<?php echo $_CB_framework->getCfg( 'live_site' ); ?>/components/com_comprofiler/images/smcblogo.gif" class="img-responsive-inline" />
							</a>
							<?php echo cbUpdateChecker(); ?>
						<?php
						} else {
						?>
							<strong><?php echo CBTxt::Th( 'UE_SITE_POWEREDBY TEAM_CREDITS_SITE_POWEREDBY', 'This site\'s community features are powered by Community Builder' ); ?></strong>
							<br />
							<a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits" target="_blank">
								<img src="<?php echo $_CB_framework->getCfg( 'live_site' ); ?>/components/com_comprofiler/images/smcblogo.gif" class="img-responsive-inline" />
							</a>
						<?php
						}
						?>
					</p>
				</div>
				<br />
				<div class="row">
					<?php
						$w	=	"<p><strong>Community Builder</strong>&trade; (CB) is the complete <strong>Social Networking software</strong> solution for <strong>Joomla</strong>&trade; that is used by this website to support its <strong>membership management</strong>.</p>
								<p>This <strong>Joomla extension</strong> is the <strong>most popular Joomla social network component on the Joomla Extensions Directory</strong>.</p>
								<p>It comes with a built-in CB template, but more cool and fast <strong>Joomla and CB templates</strong> are available.</p>
								<p>Community Builder has <strong>many CB add-ons</strong>, both free and commercial that can extend the functionality of any Joomla website. One of these is the <strong>paid memberships software</strong> solution, CBSubs&trade;, that can manage <strong>paid subscriptions</strong> to access your website content. Many more exciting CB plugins are in our <strong>CB incubator</strong>.</p>
								<p>Finally, for those wanting a turnkey <strong>Online Social Network</strong>, Joomlapolis.com offers business-class <strong>Joomla hosting</strong>, including a one-click social networking website installer.</p>";

						echo str_replace( '</strong>', '</a>', preg_replace_callback( '/<strong>/', 'teamCreditsReplacer', $w ) );
					?>
					<p><strong>Software: Copyright 2004-2015 joomlapolis.com. This component is released under the GNU/GPL version 2 License. All copyright statements must be kept. Derivate work must prominently duly acknowledge original work and include visible online links. Official site:</strong></p>
					<p class="text-center"><strong><a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits">www.joomlapolis.com</a></strong></p>
					<?php
					if ( $ui == 1 ) {
					?>
						<p><strong>Please note that the authors and distributors of this software are not affiliated nor related in any way with the site owners using this free software here, and declines any warranty regarding the content and functions of this site.</strong></p>
					<?php
					}
					?>
				</div>
				<br />
				<div class="row text-center">
					<strong>Credits:</strong>
					<script type="text/javascript">//<!--
						/*
						 Fading Scroller- By DynamicDrive.com
						 For full source code, and usage terms, visit http://www.dynamicdrive.com
						 This notice MUST stay intact for use
						 fcontent[4]="<h3>damian caynes<br />inspired digital<br /></h3>Logo Design";
						 */
						var delay=1000; //set delay between message change (in miliseconds)
						var fcontent=[];
						begintag=''; //set opening tag, such as font declarations
						fcontent[0]="<h3>CBJoe/JoomlaJoe/MamboJoe<br /></h3>Founder &amp; First Developer";
						fcontent[1]="<h3>DJTrail<br /></h3>Co-Founder";
						fcontent[2]="<h3>Nick A.<br /></h3>Documentation and Public Relations";
						fcontent[3]="<h3>Beat B.<br /></h3>Lead Developer";
						fcontent[4]="<h3>Kyle L.<br /></h3>Developer and Support";
						fcontent[5]="<h3>Lou Griffith<br /></h3>Logo Design";
						closetag='';

						var fwidth='100%';	//'250px' //set scroller width
						var fheight='80px'; //set scroller height

						var fadescheme=0<?php echo ( ( $ui == 2 ) || ($ueConfig['templatedir'] != 'dark') ? 0 : 1 ); ?>; //set 0 to fade text color from (white to black), 1 for (black to white)
						var fadelinks=1; //should links inside scroller content also fade like text? 0 for no, 1 for yes.

						///No need to edit below this line/////////////////

						var hex=(fadescheme==0)? 255 : 0;
						var startcolor=(fadescheme==0)? "rgb(255,255,255)" : "rgb(0,0,0)";
						var endcolor=(fadescheme==0)? "rgb(0,0,0)" : "rgb(255,255,255)";

						var ie4=document.all&&!document.getElementById;
						var ns4=document.layers;
						var DOM2=document.getElementById;
						var faderdelay=0;
						var index=0;
						var linksobj=null;

						if (DOM2)
							faderdelay=2000;

						//function to change content
						function changecontent(){
							if (index>=fcontent.length)
								index=0;
							if (DOM2){
								document.getElementById("fscroller").style.color=startcolor;
								document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag;
								linksobj=document.getElementById("fscroller").getElementsByTagName("A");
								if (fadelinks)
									linkcolorchange(linksobj);
								colorfade();
							}
							index++;
							setTimeout("changecontent()",delay+faderdelay);
						}

						// colorfade() partially by Marcio Galli for Netscape Communications.  ////////////
						// Modified by Dynamicdrive.com

						var frame=20, i;

						function linkcolorchange(obj){
							if (obj.length>0){
								for (i=0;i<obj.length;i++)
									obj[i].style.color="rgb("+hex+","+hex+","+hex+")";
							}
						}

						function colorfade() {
							// 20 frames fading process
							if(frame>0) {
								hex=(fadescheme==0)? hex-12 : hex+12; // increase or decrease color value depd on fadescheme
								document.getElementById("fscroller").style.color="rgb("+hex+","+hex+","+hex+")"; // Set color value.
								if (fadelinks)
									linkcolorchange(linksobj);
								frame--;
								setTimeout("colorfade()",20);
							} else {
								document.getElementById("fscroller").style.color=endcolor;
								frame=20;
								hex=(fadescheme==0)? 255 : 0;
							}
						}

						if (ie4||DOM2)
							document.write('<div id="fscroller" style="border:0 solid black;width:'+fwidth+';height:'+fheight+';padding:2px"></div>');
						window.onload=changecontent;
						//-->
					</script>
				</div>
				<?php
				if ( $ui == 2 ) {
				?>
					<br />
					<div class="row text-center">
						<p><strong>Please note there is a free installation document, as well as a full documentation subscription for this free component available at <a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits">www.joomlapolis.com</a></strong></p>
					</div>
					<br />
					<div class="row text-center">
						<p>If you like the services provided by this free component, <a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits">please consider making a small donation to support the team behind it</a></p>
					</div>
				<?php
				} elseif ( $_CB_framework->myId() ) {
				?>
					<br />
					<div class="row text-center">
						<p><a href="<?php echo cbSef( 'index.php?option=com_comprofiler' .getCBprofileItemid( true ) ); ?>"><?php echo CBTxt::Th( 'TEAM_CREDITS_BACK_TO_YOUR_PROFILE UE_BACK_TO_YOUR_PROFILE', 'Back to your profile' ); ?></a></p>
					</div>
				<?php
				}
				?>
				<br />
				<table class="table table-bordered table-responsive">
					<tr>
						<th colspan="<?php echo ( $ui == 2 ? 3 : 2 ); ?>">Community Builder includes following components</th>
					</tr>
					<tr>
						<th>Application</th>
						<?php
						if ( $ui == 2 ) {
						?>
							<th>Version</th>
						<?php
						}
						?>
						<th>License</th>
					</tr>
					<tr>
						<td>
							<a href="http://www.foood.net" target="_blank">Icons (old icons)</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>N/A</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.foood.net/agreement.htm" target="_blank">License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://nuovext.pwsp.net/" target="_blank">Icons</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>2.2</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">GNU Lesser General Public License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://webfx.eae.net" target="_blank">Tabs</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.02</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.apache.org/licenses/LICENSE-2.0" target="_blank">Apache License, Version 2.0</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://www.dynarch.com/projects/calendar" target="_blank">Calendar</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.1</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">GNU Lesser General Public License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://www.dynamicdrive.com/dynamicindex7/jasoncalendar.htm" target="_blank">Jason&#039;s Calendar</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>2005-09-05</td>
						<?php
						}
						?>
						<td>
							<a href="http://dynamicdrive.com/notice.htm" target="_blank">Dynamic Drive terms of use License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://docs.guzzlephp.org/en/guzzle4/index.html" target="_blank">Guzzle</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>4.1.3</td>
						<?php
						}
						?>
						<td>
							<a href="http://opensource.org/licenses/MIT" target="_blank">MIT</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="https://github.com/php-fig/log" target="_blank">Psr/Log</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.0.0</td>
						<?php
						}
						?>
						<td>
							<a href="https://github.com/php-fig/log/blob/master/LICENSE" target="_blank">MIT</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="https://github.com/avalanche123/Imagine" target="_blank">Imagine</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>0.6.1</td>
						<?php
						}
						?>
						<td>
							<a href="https://github.com/avalanche123/Imagine/blob/develop/LICENSE" target="_blank">MIT and third-party licenses</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://snoopy.sourceforge.net/" target="_blank">Snoopy</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.2.3</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">GNU Lesser General Public License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://www.phpclasses.org/browse/package/2189.html" target="_blank">PHPMailer</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>5.2.8</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">GNU Lesser General Public License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://www.phpclasses.org/browse/package/2189.html" target="_blank">PHP Input Filter</a>
							<a href="http://freshmeat.net/projects/inputfilter/" target="_blank">(forge)</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.2.2+</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits" target="_blank">BestMenus</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td>1.0</td>
						<?php
						}
						?>
						<td>
							<a href="http://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=credits" target="_blank">Open Source GPL (GNU General Public License) v2</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="http://jquery.com/" target="_blank">jQuery</a>
						</td>
						<?php
						if ( $ui == 2 ) {
						?>
							<td><?php echo _CB_JQUERY_VERSION; ?></td>
						<?php
						}
						?>
						<td>
							<a href="http://docs.jquery.com/" target="_blank">MIT license</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}

	/**
	 * Gets an array of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return array of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	function cbGetIParray() {
		global $_SERVER;

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_adr_array		=	explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
		} else {
			$ip_adr_array		=	array();
		}
		$ip_adr_array[]			=	$_SERVER['REMOTE_ADDR'];
		return $ip_adr_array;
	}

	/**
	 * Gets a comma-separated list of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return string of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	function cbGetIPlist() {
		return addslashes(implode(",",cbGetIParray()));
	}

	/**
	 * Records the hit to a user profile
	 *
	 * @access private
	 *
	 * @param  int  $profileId  Viewed user id
	 */
	function _incHits( $profileId ) {
		global $_CB_database;
		$_CB_database->setQuery("UPDATE #__comprofiler SET hits=(hits+1) WHERE id=" . (int) $profileId);
		if (!$_CB_database->query()) {
			echo "<script type=\"text/javascript\"> alert('UpdateHits: ".$_CB_database->getErrorMsg()."');</script>\n";
			// exit();
		}
	}

	/**
	 * records a visit and the hit with timed protection similar to voting protections
	 *
	 * @param  int      $viewerId   Viewing user id
	 * @param  int     $profileId  Viewed user id
	 * @param  string  $ipAddress  IP address of viewing user
	 */
	function recordViewHit( $viewerId, $profileId, $ipAddress ) {
		global $_CB_framework, $_CB_database, $ueConfig;

		if ( ! Application::Config()->get( 'profile_recordviews', 1 ) ) {
			return;
		}

		$query					=	'SELECT ' . $_CB_database->NameQuote( 'lastview' ) . ', ' . $_CB_database->NameQuote( 'lastip' )
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_views' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'viewer_id' ) . " = " . (int) $viewerId
								.	"\n AND " . $_CB_database->NameQuote( 'profile_id' ) . " = " . (int) $profileId
								.	( $viewerId == 0 ? "\n AND " . $_CB_database->NameQuote( 'lastip' ) . " = " . $_CB_database->Quote( $ipAddress ) : null )
								.	"\n ORDER BY " . $_CB_database->NameQuote( 'lastview' ) . " DESC";
		$_CB_database->setQuery( $query );
		$views					=	$_CB_database->loadObjectList();

		if ( count( $views ) == 0 ) {
			// no views yet: insert the view record:
			$query				=	'INSERT INTO ' . $_CB_database->NameQuote( '#__comprofiler_views' )
								.	"\n ( " . $_CB_database->NameQuote( 'viewer_id' )
								.	', ' . $_CB_database->NameQuote( 'profile_id' )
								.	', ' . $_CB_database->NameQuote( 'lastip' )
								.	', ' . $_CB_database->NameQuote( 'lastview' )
								.	', ' . $_CB_database->NameQuote( 'viewscount' ) . ' )'
								.	"\n VALUES ( "
								.	(int) $viewerId
								.	', ' . (int) $profileId
								.	', ' . $_CB_database->Quote( $ipAddress )
								.	', ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
								.	', 1 )';
			$_CB_database->setQuery( $query );
			if ( ! $_CB_database->query() ) {
				echo "<script type=\"text/javascript\">alert( 'InsertViews: " . addslashes( $_CB_database->getErrorMsg() ) . "' );</script>\n";
			}

			_incHits( $profileId );
		} else {
			// we already have view(s):
			$count				=	count( $views );

			$lastview			=	$_CB_framework->getUTCTimestamp( $views[0]->lastview );

			if ( $count > 1 ) {
				// huston, we have a database problem: we have more than one entry for the pair viewer-viewed OR the tripplet (anonymous viewer=0 - viewed - IP address):
				// updating would generate key conflicts: cleanupt that mess please:
				$query			=	'DELETE FROM ' . $_CB_database->NameQuote( '#__comprofiler_views' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'viewer_id' ) . " = " . (int) $viewerId
								.	"\n AND " . $_CB_database->NameQuote( 'profile_id' ) . " = " . (int) $profileId
								.	( $viewerId == 0 ? "\n AND " . $_CB_database->NameQuote( 'lastip' ) . " = " . $_CB_database->Quote( $ipAddress ) : null )
								.	"\n AND " . $_CB_database->NameQuote( 'lastview' ) . " <> " . $_CB_database->Quote( $views[0]->lastview );
				$_CB_database->setQuery( $query );
				if ( ! $_CB_database->query() ) {
					echo "<script type=\"text/javascript\">alert( 'DeleteViews: " . addslashes( $_CB_database->getErrorMsg() ) . "' );</script>\n";
				}
			}

			// ok there was a view, we will count it only if lastview time is greater than the minimum interval configured,
			$needsUpdate		=	( ( $_CB_framework->getUTCTimestamp() - $lastview ) > ( $ueConfig['minHitsInterval'] * 60 ) );

			// but we will update any IP address changes in case of a logged-in user (for guests, the SELECT above is by IP address, so that entry and IP is already same:
			if ( ( $ipAddress != $views[0]->lastip ) || $needsUpdate ) {
				$query			=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_views' )
								.	"\n SET " . $_CB_database->NameQuote( 'lastview' ) . " = " . $_CB_database->Quote( $_CB_framework->getUTCDate() )
								.	', ' . $_CB_database->NameQuote( 'lastip' ) . " = " . $_CB_database->Quote( $ipAddress )
								.	( $needsUpdate ? ', ' . $_CB_database->NameQuote( 'viewscount' ) . " = (" . $_CB_database->NameQuote( 'viewscount' ) . "+1)" : '' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'viewer_id' ) . " = " . (int) $viewerId
								.	"\n AND " . $_CB_database->NameQuote( 'profile_id' ) . " = " . (int) $profileId
								.	( $viewerId == 0 ? "\n AND " . $_CB_database->NameQuote( 'lastip' ) . " = " . $_CB_database->Quote( $ipAddress ) : null );
				$_CB_database->setQuery( $query );
				if ( ! $_CB_database->query() ) {
					echo "<script type=\"text/javascript\">alert( 'UpdateViews: " . addslashes( $_CB_database->getErrorMsg() ) . "' );</script>\n";
				}

				if ( $needsUpdate ) {
					_incHits( $profileId );
				}
			}
		}
	}

	/**
	 * Returns map of extensions to MIME types
	 *
	 * @return array
	 */
	function cbGetMimeMap() {
		$map	=	array(	'ez' => 'application/andrew-inset', 'aw' => 'application/applixware', 'atom' => 'application/atom+xml', 'atomcat' => 'application/atomcat+xml',
							   'atomsvc' => 'application/atomsvc+xml', 'ccxml' => 'application/ccxml+xml', 'cdmia' => 'application/cdmi-capability', 'cdmic' => 'application/cdmi-container',
							   'cdmid' => 'application/cdmi-domain', 'cdmio' => 'application/cdmi-object', 'cdmiq' => 'application/cdmi-queue', 'cu' => 'application/cu-seeme',
							   'davmount' => 'application/davmount+xml', 'dbk' => 'application/docbook+xml', 'dssc' => 'application/dssc+der', 'xdssc' => 'application/dssc+xml',
							   'ecma' => 'application/ecmascript', 'emma' => 'application/emma+xml', 'epub' => 'application/epub+zip', 'exi' => 'application/exi',
							   'pfr' => 'application/font-tdpfr', 'gml' => 'application/gml+xml', 'gpx' => 'application/gpx+xml', 'gxf' => 'application/gxf',
							   'stk' => 'application/hyperstudio', 'ink' => 'application/inkml+xml', 'inkml' => 'application/inkml+xml', 'ipfix' => 'application/ipfix',
							   'jar' => 'application/java-archive', 'ser' => 'application/java-serialized-object', 'class' => 'application/java-vm', 'js' => 'application/javascript',
							   'json' => 'application/json', 'jsonml' => 'application/jsonml+json', 'lostxml' => 'application/lost+xml', 'hqx' => 'application/mac-binhex40',
							   'cpt' => 'application/mac-compactpro', 'mads' => 'application/mads+xml', 'mrc' => 'application/marc', 'mrcx' => 'application/marcxml+xml',
							   'ma' => 'application/mathematica', 'nb' => 'application/mathematica', 'mb' => 'application/mathematica', 'mathml' => 'application/mathml+xml',
							   'mbox' => 'application/mbox', 'mscml' => 'application/mediaservercontrol+xml', 'metalink' => 'application/metalink+xml', 'meta4' => 'application/metalink4+xml',
							   'mets' => 'application/mets+xml', 'mods' => 'application/mods+xml', 'm21' => 'application/mp21', 'mp21' => 'application/mp21',
							   'mp4s' => 'application/mp4', 'doc' => 'application/msword', 'dot' => 'application/msword', 'mxf' => 'application/mxf',
							   'oda' => 'application/oda', 'opf' => 'application/oebps-package+xml', 'ogx' => 'application/ogg', 'omdoc' => 'application/omdoc+xml',
							   'onetoc' => 'application/onenote', 'onetoc2' => 'application/onenote', 'onetmp' => 'application/onenote', 'onepkg' => 'application/onenote',
							   'oxps' => 'application/oxps', 'xer' => 'application/patch-ops-error+xml', 'pdf' => 'application/pdf', 'pgp' => 'application/pgp-encrypted',
							   'asc' => 'application/pgp-signature', 'sig' => 'application/pgp-signature', 'prf' => 'application/pics-rules', 'p10' => 'application/pkcs10',
							   'p7m' => 'application/pkcs7-mime', 'p7c' => 'application/pkcs7-mime', 'p7s' => 'application/pkcs7-signature', 'p8' => 'application/pkcs8',
							   'ac' => 'application/pkix-attr-cert', 'cer' => 'application/pkix-cert', 'crl' => 'application/pkix-crl', 'pkipath' => 'application/pkix-pkipath',
							   'pki' => 'application/pkixcmp', 'pls' => 'application/pls+xml', 'ai' => 'application/postscript', 'eps' => 'application/postscript',
							   'ps' => 'application/postscript', 'cww' => 'application/prs.cww', 'pskcxml' => 'application/pskc+xml', 'rdf' => 'application/rdf+xml',
							   'rif' => 'application/reginfo+xml', 'rnc' => 'application/relax-ng-compact-syntax', 'rl' => 'application/resource-lists+xml', 'rld' => 'application/resource-lists-diff+xml',
							   'rs' => 'application/rls-services+xml', 'gbr' => 'application/rpki-ghostbusters', 'mft' => 'application/rpki-manifest', 'roa' => 'application/rpki-roa',
							   'rsd' => 'application/rsd+xml', 'rss' => 'application/rss+xml', 'rtf' => 'application/rtf', 'sbml' => 'application/sbml+xml',
							   'scq' => 'application/scvp-cv-request', 'scs' => 'application/scvp-cv-response', 'spq' => 'application/scvp-vp-request', 'spp' => 'application/scvp-vp-response',
							   'sdp' => 'application/sdp', 'setpay' => 'application/set-payment-initiation', 'setreg' => 'application/set-registration-initiation', 'shf' => 'application/shf+xml',
							   'smi' => 'application/smil+xml', 'smil' => 'application/smil+xml', 'rq' => 'application/sparql-query', 'srx' => 'application/sparql-results+xml',
							   'gram' => 'application/srgs', 'grxml' => 'application/srgs+xml', 'sru' => 'application/sru+xml', 'ssdl' => 'application/ssdl+xml',
							   'ssml' => 'application/ssml+xml', 'tei' => 'application/tei+xml', 'teicorpus' => 'application/tei+xml', 'tfi' => 'application/thraud+xml',
							   'tsd' => 'application/timestamped-data', 'plb' => 'application/vnd.3gpp.pic-bw-large', 'psb' => 'application/vnd.3gpp.pic-bw-small', 'pvb' => 'application/vnd.3gpp.pic-bw-var',
							   'tcap' => 'application/vnd.3gpp2.tcap', 'pwn' => 'application/vnd.3m.post-it-notes', 'aso' => 'application/vnd.accpac.simply.aso', 'imp' => 'application/vnd.accpac.simply.imp',
							   'acu' => 'application/vnd.acucobol', 'atc' => 'application/vnd.acucorp', 'acutc' => 'application/vnd.acucorp', 'air' => 'application/vnd.adobe.air-application-installer-package+zip',
							   'fcdt' => 'application/vnd.adobe.formscentral.fcdt', 'fxp' => 'application/vnd.adobe.fxp', 'fxpl' => 'application/vnd.adobe.fxp', 'xdp' => 'application/vnd.adobe.xdp+xml',
							   'xfdf' => 'application/vnd.adobe.xfdf', 'ahead' => 'application/vnd.ahead.space', 'azf' => 'application/vnd.airzip.filesecure.azf', 'azs' => 'application/vnd.airzip.filesecure.azs',
							   'azw' => 'application/vnd.amazon.ebook', 'acc' => 'application/vnd.americandynamics.acc', 'ami' => 'application/vnd.amiga.ami', 'apk' => 'application/vnd.android.package-archive',
							   'cii' => 'application/vnd.anser-web-certificate-issue-initiation', 'fti' => 'application/vnd.anser-web-funds-transfer-initiation', 'atx' => 'application/vnd.antix.game-component', 'mpkg' => 'application/vnd.apple.installer+xml',
							   'm3u8' => 'application/vnd.apple.mpegurl', 'swi' => 'application/vnd.aristanetworks.swi', 'iota' => 'application/vnd.astraea-software.iota', 'aep' => 'application/vnd.audiograph',
							   'mpm' => 'application/vnd.blueice.multipass', 'bmi' => 'application/vnd.bmi', 'rep' => 'application/vnd.businessobjects', 'cdxml' => 'application/vnd.chemdraw+xml',
							   'mmd' => 'application/vnd.chipnuts.karaoke-mmd', 'cdy' => 'application/vnd.cinderella', 'cla' => 'application/vnd.claymore', 'rp9' => 'application/vnd.cloanto.rp9',
							   'c4g' => 'application/vnd.clonk.c4group', 'c4d' => 'application/vnd.clonk.c4group', 'c4f' => 'application/vnd.clonk.c4group', 'c4p' => 'application/vnd.clonk.c4group',
							   'c4u' => 'application/vnd.clonk.c4group', 'c11amc' => 'application/vnd.cluetrust.cartomobile-config', 'c11amz' => 'application/vnd.cluetrust.cartomobile-config-pkg', 'csp' => 'application/vnd.commonspace',
							   'cdbcmsg' => 'application/vnd.contact.cmsg', 'cmc' => 'application/vnd.cosmocaller', 'clkx' => 'application/vnd.crick.clicker', 'clkk' => 'application/vnd.crick.clicker.keyboard',
							   'clkp' => 'application/vnd.crick.clicker.palette', 'clkt' => 'application/vnd.crick.clicker.template', 'clkw' => 'application/vnd.crick.clicker.wordbank', 'wbs' => 'application/vnd.criticaltools.wbs+xml',
							   'pml' => 'application/vnd.ctc-posml', 'ppd' => 'application/vnd.cups-ppd', 'car' => 'application/vnd.curl.car', 'pcurl' => 'application/vnd.curl.pcurl',
							   'dart' => 'application/vnd.dart', 'rdz' => 'application/vnd.data-vision.rdz', 'uvf' => 'application/vnd.dece.data', 'uvvf' => 'application/vnd.dece.data',
							   'uvd' => 'application/vnd.dece.data', 'uvvd' => 'application/vnd.dece.data', 'uvt' => 'application/vnd.dece.ttml+xml', 'uvvt' => 'application/vnd.dece.ttml+xml',
							   'uvx' => 'application/vnd.dece.unspecified', 'uvvx' => 'application/vnd.dece.unspecified', 'uvz' => 'application/vnd.dece.zip', 'uvvz' => 'application/vnd.dece.zip',
							   'fe_launch' => 'application/vnd.denovo.fcselayout-link', 'dna' => 'application/vnd.dna', 'mlp' => 'application/vnd.dolby.mlp', 'dpg' => 'application/vnd.dpgraph',
							   'dfac' => 'application/vnd.dreamfactory', 'kpxx' => 'application/vnd.ds-keypoint', 'ait' => 'application/vnd.dvb.ait', 'svc' => 'application/vnd.dvb.service',
							   'geo' => 'application/vnd.dynageo', 'mag' => 'application/vnd.ecowin.chart', 'nml' => 'application/vnd.enliven', 'esf' => 'application/vnd.epson.esf',
							   'msf' => 'application/vnd.epson.msf', 'qam' => 'application/vnd.epson.quickanime', 'slt' => 'application/vnd.epson.salt', 'ssf' => 'application/vnd.epson.ssf',
							   'es3' => 'application/vnd.eszigno3+xml', 'et3' => 'application/vnd.eszigno3+xml', 'ez2' => 'application/vnd.ezpix-album', 'ez3' => 'application/vnd.ezpix-package',
							   'fdf' => 'application/vnd.fdf', 'mseed' => 'application/vnd.fdsn.mseed', 'seed' => 'application/vnd.fdsn.seed', 'dataless' => 'application/vnd.fdsn.seed',
							   'gph' => 'application/vnd.flographit', 'ftc' => 'application/vnd.fluxtime.clip', 'fm' => 'application/vnd.framemaker', 'frame' => 'application/vnd.framemaker',
							   'maker' => 'application/vnd.framemaker', 'book' => 'application/vnd.framemaker', 'fnc' => 'application/vnd.frogans.fnc', 'ltf' => 'application/vnd.frogans.ltf',
							   'fsc' => 'application/vnd.fsc.weblaunch', 'oas' => 'application/vnd.fujitsu.oasys', 'oa2' => 'application/vnd.fujitsu.oasys2', 'oa3' => 'application/vnd.fujitsu.oasys3',
							   'fg5' => 'application/vnd.fujitsu.oasysgp', 'bh2' => 'application/vnd.fujitsu.oasysprs', 'ddd' => 'application/vnd.fujixerox.ddd', 'xdw' => 'application/vnd.fujixerox.docuworks',
							   'xbd' => 'application/vnd.fujixerox.docuworks.binder', 'fzs' => 'application/vnd.fuzzysheet', 'txd' => 'application/vnd.genomatix.tuxedo', 'ggb' => 'application/vnd.geogebra.file',
							   'ggt' => 'application/vnd.geogebra.tool', 'gex' => 'application/vnd.geometry-explorer', 'gre' => 'application/vnd.geometry-explorer', 'gxt' => 'application/vnd.geonext',
							   'g2w' => 'application/vnd.geoplan', 'g3w' => 'application/vnd.geospace', 'gmx' => 'application/vnd.gmx', 'kml' => 'application/vnd.google-earth.kml+xml',
							   'kmz' => 'application/vnd.google-earth.kmz', 'gqf' => 'application/vnd.grafeq', 'gqs' => 'application/vnd.grafeq', 'gac' => 'application/vnd.groove-account',
							   'ghf' => 'application/vnd.groove-help', 'gim' => 'application/vnd.groove-identity-message', 'grv' => 'application/vnd.groove-injector', 'gtm' => 'application/vnd.groove-tool-message',
							   'tpl' => 'application/vnd.groove-tool-template', 'vcg' => 'application/vnd.groove-vcard', 'hal' => 'application/vnd.hal+xml', 'zmm' => 'application/vnd.handheld-entertainment+xml',
							   'hbci' => 'application/vnd.hbci', 'les' => 'application/vnd.hhe.lesson-player', 'hpgl' => 'application/vnd.hp-hpgl', 'hpid' => 'application/vnd.hp-hpid',
							   'hps' => 'application/vnd.hp-hps', 'jlt' => 'application/vnd.hp-jlyt', 'pcl' => 'application/vnd.hp-pcl', 'pclxl' => 'application/vnd.hp-pclxl',
							   'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data', 'mpy' => 'application/vnd.ibm.minipay', 'afp' => 'application/vnd.ibm.modcap', 'listafp' => 'application/vnd.ibm.modcap',
							   'list3820' => 'application/vnd.ibm.modcap', 'irm' => 'application/vnd.ibm.rights-management', 'sc' => 'application/vnd.ibm.secure-container', 'icc' => 'application/vnd.iccprofile',
							   'icm' => 'application/vnd.iccprofile', 'igl' => 'application/vnd.igloader', 'ivp' => 'application/vnd.immervision-ivp', 'ivu' => 'application/vnd.immervision-ivu',
							   'igm' => 'application/vnd.insors.igm', 'xpw' => 'application/vnd.intercon.formnet', 'xpx' => 'application/vnd.intercon.formnet', 'i2g' => 'application/vnd.intergeo',
							   'qbo' => 'application/vnd.intu.qbo', 'qfx' => 'application/vnd.intu.qfx', 'rcprofile' => 'application/vnd.ipunplugged.rcprofile', 'irp' => 'application/vnd.irepository.package+xml',
							   'xpr' => 'application/vnd.is-xpr', 'fcs' => 'application/vnd.isac.fcs', 'jam' => 'application/vnd.jam', 'rms' => 'application/vnd.jcp.javame.midlet-rms',
							   'jisp' => 'application/vnd.jisp', 'joda' => 'application/vnd.joost.joda-archive', 'ktz' => 'application/vnd.kahootz', 'ktr' => 'application/vnd.kahootz',
							   'karbon' => 'application/vnd.kde.karbon', 'chrt' => 'application/vnd.kde.kchart', 'kfo' => 'application/vnd.kde.kformula', 'flw' => 'application/vnd.kde.kivio',
							   'kon' => 'application/vnd.kde.kontour', 'kpr' => 'application/vnd.kde.kpresenter', 'kpt' => 'application/vnd.kde.kpresenter', 'ksp' => 'application/vnd.kde.kspread',
							   'kwd' => 'application/vnd.kde.kword', 'kwt' => 'application/vnd.kde.kword', 'htke' => 'application/vnd.kenameaapp', 'kia' => 'application/vnd.kidspiration',
							   'kne' => 'application/vnd.kinar', 'knp' => 'application/vnd.kinar', 'skp' => 'application/vnd.koan', 'skd' => 'application/vnd.koan',
							   'skt' => 'application/vnd.koan', 'skm' => 'application/vnd.koan', 'sse' => 'application/vnd.kodak-descriptor', 'lasxml' => 'application/vnd.las.las+xml',
							   'lbd' => 'application/vnd.llamagraphics.life-balance.desktop', 'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml', '123' => 'application/vnd.lotus-1-2-3', 'apr' => 'application/vnd.lotus-approach',
							   'pre' => 'application/vnd.lotus-freelance', 'nsf' => 'application/vnd.lotus-notes', 'org' => 'application/vnd.lotus-organizer', 'scm' => 'application/vnd.lotus-screencam',
							   'lwp' => 'application/vnd.lotus-wordpro', 'portpkg' => 'application/vnd.macports.portpkg', 'mcd' => 'application/vnd.mcd', 'mc1' => 'application/vnd.medcalcdata',
							   'cdkey' => 'application/vnd.mediastation.cdkey', 'mwf' => 'application/vnd.mfer', 'mfm' => 'application/vnd.mfmp', 'flo' => 'application/vnd.micrografx.flo',
							   'igx' => 'application/vnd.micrografx.igx', 'mif' => 'application/vnd.mif', 'daf' => 'application/vnd.mobius.daf', 'dis' => 'application/vnd.mobius.dis',
							   'mbk' => 'application/vnd.mobius.mbk', 'mqy' => 'application/vnd.mobius.mqy', 'msl' => 'application/vnd.mobius.msl', 'plc' => 'application/vnd.mobius.plc',
							   'txf' => 'application/vnd.mobius.txf', 'mpn' => 'application/vnd.mophun.application', 'mpc' => 'application/vnd.mophun.certificate', 'xul' => 'application/vnd.mozilla.xul+xml',
							   'cil' => 'application/vnd.ms-artgalry', 'cab' => 'application/vnd.ms-cab-compressed', 'xls' => 'application/vnd.ms-excel', 'xlm' => 'application/vnd.ms-excel',
							   'xla' => 'application/vnd.ms-excel', 'xlc' => 'application/vnd.ms-excel', 'xlt' => 'application/vnd.ms-excel', 'xlw' => 'application/vnd.ms-excel',
							   'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12', 'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12', 'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12', 'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
							   'eot' => 'application/vnd.ms-fontobject', 'chm' => 'application/vnd.ms-htmlhelp', 'ims' => 'application/vnd.ms-ims', 'lrm' => 'application/vnd.ms-lrm',
							   'thmx' => 'application/vnd.ms-officetheme', 'cat' => 'application/vnd.ms-pki.seccat', 'stl' => 'application/vnd.ms-pki.stl', 'ppt' => 'application/vnd.ms-powerpoint',
							   'pps' => 'application/vnd.ms-powerpoint', 'pot' => 'application/vnd.ms-powerpoint', 'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12', 'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
							   'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12', 'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12', 'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12', 'mpp' => 'application/vnd.ms-project',
							   'mpt' => 'application/vnd.ms-project', 'docm' => 'application/vnd.ms-word.document.macroenabled.12', 'dotm' => 'application/vnd.ms-word.template.macroenabled.12', 'wps' => 'application/vnd.ms-works',
							   'wks' => 'application/vnd.ms-works', 'wcm' => 'application/vnd.ms-works', 'wdb' => 'application/vnd.ms-works', 'wpl' => 'application/vnd.ms-wpl',
							   'xps' => 'application/vnd.ms-xpsdocument', 'mseq' => 'application/vnd.mseq', 'mus' => 'application/vnd.musician', 'msty' => 'application/vnd.muvee.style',
							   'taglet' => 'application/vnd.mynfc', 'nlu' => 'application/vnd.neurolanguage.nlu', 'ntf' => 'application/vnd.nitf', 'nitf' => 'application/vnd.nitf',
							   'nnd' => 'application/vnd.noblenet-directory', 'nns' => 'application/vnd.noblenet-sealer', 'nnw' => 'application/vnd.noblenet-web', 'ngdat' => 'application/vnd.nokia.n-gage.data',
							   'n-gage' => 'application/vnd.nokia.n-gage.symbian.install', 'rpst' => 'application/vnd.nokia.radio-preset', 'rpss' => 'application/vnd.nokia.radio-presets', 'edm' => 'application/vnd.novadigm.edm',
							   'edx' => 'application/vnd.novadigm.edx', 'ext' => 'application/vnd.novadigm.ext', 'odc' => 'application/vnd.oasis.opendocument.chart', 'otc' => 'application/vnd.oasis.opendocument.chart-template',
							   'odb' => 'application/vnd.oasis.opendocument.database', 'odf' => 'application/vnd.oasis.opendocument.formula', 'odft' => 'application/vnd.oasis.opendocument.formula-template', 'odg' => 'application/vnd.oasis.opendocument.graphics',
							   'otg' => 'application/vnd.oasis.opendocument.graphics-template', 'odi' => 'application/vnd.oasis.opendocument.image', 'oti' => 'application/vnd.oasis.opendocument.image-template', 'odp' => 'application/vnd.oasis.opendocument.presentation',
							   'otp' => 'application/vnd.oasis.opendocument.presentation-template', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet', 'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template', 'odt' => 'application/vnd.oasis.opendocument.text',
							   'odm' => 'application/vnd.oasis.opendocument.text-master', 'ott' => 'application/vnd.oasis.opendocument.text-template', 'oth' => 'application/vnd.oasis.opendocument.text-web', 'xo' => 'application/vnd.olpc-sugar',
							   'dd2' => 'application/vnd.oma.dd2+xml', 'oxt' => 'application/vnd.openofficeorg.extension', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
							   'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
							   'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'mgp' => 'application/vnd.osgeo.mapguide.package', 'dp' => 'application/vnd.osgi.dp',
							   'esa' => 'application/vnd.osgi.subsystem', 'pdb' => 'application/vnd.palm', 'pqa' => 'application/vnd.palm', 'oprc' => 'application/vnd.palm',
							   'paw' => 'application/vnd.pawaafile', 'str' => 'application/vnd.pg.format', 'ei6' => 'application/vnd.pg.osasli', 'efif' => 'application/vnd.picsel',
							   'wg' => 'application/vnd.pmi.widget', 'plf' => 'application/vnd.pocketlearn', 'pbd' => 'application/vnd.powerbuilder6', 'box' => 'application/vnd.previewsystems.box',
							   'mgz' => 'application/vnd.proteus.magazine', 'qps' => 'application/vnd.publishare-delta-tree', 'ptid' => 'application/vnd.pvi.ptid1', 'qxd' => 'application/vnd.quark.quarkxpress',
							   'qxt' => 'application/vnd.quark.quarkxpress', 'qwd' => 'application/vnd.quark.quarkxpress', 'qwt' => 'application/vnd.quark.quarkxpress', 'qxl' => 'application/vnd.quark.quarkxpress',
							   'qxb' => 'application/vnd.quark.quarkxpress', 'bed' => 'application/vnd.realvnc.bed', 'mxl' => 'application/vnd.recordare.musicxml', 'musicxml' => 'application/vnd.recordare.musicxml+xml',
							   'cryptonote' => 'application/vnd.rig.cryptonote', 'cod' => 'application/vnd.rim.cod', 'rm' => 'application/vnd.rn-realmedia', 'rmvb' => 'application/vnd.rn-realmedia-vbr',
							   'link66' => 'application/vnd.route66.link66+xml', 'st' => 'application/vnd.sailingtracker.track', 'see' => 'application/vnd.seemail', 'sema' => 'application/vnd.sema',
							   'semd' => 'application/vnd.semd', 'semf' => 'application/vnd.semf', 'ifm' => 'application/vnd.shana.informed.formdata', 'itp' => 'application/vnd.shana.informed.formtemplate',
							   'iif' => 'application/vnd.shana.informed.interchange', 'ipk' => 'application/vnd.shana.informed.package', 'twd' => 'application/vnd.simtech-mindmapper', 'twds' => 'application/vnd.simtech-mindmapper',
							   'mmf' => 'application/vnd.smaf', 'teacher' => 'application/vnd.smart.teacher', 'sdkm' => 'application/vnd.solent.sdkm+xml', 'sdkd' => 'application/vnd.solent.sdkm+xml',
							   'dxp' => 'application/vnd.spotfire.dxp', 'sfs' => 'application/vnd.spotfire.sfs', 'sdc' => 'application/vnd.stardivision.calc', 'sda' => 'application/vnd.stardivision.draw',
							   'sdd' => 'application/vnd.stardivision.impress', 'smf' => 'application/vnd.stardivision.math', 'sdw' => 'application/vnd.stardivision.writer', 'vor' => 'application/vnd.stardivision.writer',
							   'sgl' => 'application/vnd.stardivision.writer-global', 'smzip' => 'application/vnd.stepmania.package', 'sm' => 'application/vnd.stepmania.stepchart', 'sxc' => 'application/vnd.sun.xml.calc',
							   'stc' => 'application/vnd.sun.xml.calc.template', 'sxd' => 'application/vnd.sun.xml.draw', 'std' => 'application/vnd.sun.xml.draw.template', 'sxi' => 'application/vnd.sun.xml.impress',
							   'sti' => 'application/vnd.sun.xml.impress.template', 'sxm' => 'application/vnd.sun.xml.math', 'sxw' => 'application/vnd.sun.xml.writer', 'sxg' => 'application/vnd.sun.xml.writer.global',
							   'stw' => 'application/vnd.sun.xml.writer.template', 'sus' => 'application/vnd.sus-calendar', 'susp' => 'application/vnd.sus-calendar', 'svd' => 'application/vnd.svd',
							   'sis' => 'application/vnd.symbian.install', 'sisx' => 'application/vnd.symbian.install', 'xsm' => 'application/vnd.syncml+xml', 'bdm' => 'application/vnd.syncml.dm+wbxml',
							   'xdm' => 'application/vnd.syncml.dm+xml', 'tao' => 'application/vnd.tao.intent-module-archive', 'pcap' => 'application/vnd.tcpdump.pcap', 'cap' => 'application/vnd.tcpdump.pcap',
							   'dmp' => 'application/vnd.tcpdump.pcap', 'tmo' => 'application/vnd.tmobile-livetv', 'tpt' => 'application/vnd.trid.tpt', 'mxs' => 'application/vnd.triscape.mxs',
							   'tra' => 'application/vnd.trueapp', 'ufd' => 'application/vnd.ufdl', 'ufdl' => 'application/vnd.ufdl', 'utz' => 'application/vnd.uiq.theme',
							   'umj' => 'application/vnd.umajin', 'unityweb' => 'application/vnd.unity', 'uoml' => 'application/vnd.uoml+xml', 'vcx' => 'application/vnd.vcx',
							   'vsd' => 'application/vnd.visio', 'vst' => 'application/vnd.visio', 'vss' => 'application/vnd.visio', 'vsw' => 'application/vnd.visio',
							   'vis' => 'application/vnd.visionary', 'vsf' => 'application/vnd.vsf', 'wbxml' => 'application/vnd.wap.wbxml', 'wmlc' => 'application/vnd.wap.wmlc',
							   'wmlsc' => 'application/vnd.wap.wmlscriptc', 'wtb' => 'application/vnd.webturbo', 'nbp' => 'application/vnd.wolfram.player', 'wpd' => 'application/vnd.wordperfect',
							   'wqd' => 'application/vnd.wqd', 'stf' => 'application/vnd.wt.stf', 'xar' => 'application/vnd.xara', 'xfdl' => 'application/vnd.xfdl',
							   'hvd' => 'application/vnd.yamaha.hv-dic', 'hvs' => 'application/vnd.yamaha.hv-script', 'hvp' => 'application/vnd.yamaha.hv-voice', 'osf' => 'application/vnd.yamaha.openscoreformat',
							   'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml', 'saf' => 'application/vnd.yamaha.smaf-audio', 'spf' => 'application/vnd.yamaha.smaf-phrase', 'cmp' => 'application/vnd.yellowriver-custom-menu',
							   'zir' => 'application/vnd.zul', 'zirz' => 'application/vnd.zul', 'zaz' => 'application/vnd.zzazz.deck+xml', 'vxml' => 'application/voicexml+xml',
							   'wgt' => 'application/widget', 'hlp' => 'application/winhlp', 'wsdl' => 'application/wsdl+xml', 'wspolicy' => 'application/wspolicy+xml',
							   '7z' => 'application/x-7z-compressed', 'abw' => 'application/x-abiword', 'ace' => 'application/x-ace-compressed', 'dmg' => 'application/x-apple-diskimage',
							   'aab' => 'application/x-authorware-bin', 'x32' => 'application/x-authorware-bin', 'u32' => 'application/x-authorware-bin', 'vox' => 'application/x-authorware-bin',
							   'aam' => 'application/x-authorware-map', 'aas' => 'application/x-authorware-seg', 'bcpio' => 'application/x-bcpio', 'torrent' => 'application/x-bittorrent',
							   'blb' => 'application/x-blorb', 'blorb' => 'application/x-blorb', 'bz' => 'application/x-bzip', 'bz2' => 'application/x-bzip2',
							   'boz' => 'application/x-bzip2', 'cbr' => 'application/x-cbr', 'cba' => 'application/x-cbr', 'cbt' => 'application/x-cbr',
							   'cbz' => 'application/x-cbr', 'cb7' => 'application/x-cbr', 'vcd' => 'application/x-cdlink', 'cfs' => 'application/x-cfs-compressed',
							   'chat' => 'application/x-chat', 'pgn' => 'application/x-chess-pgn', 'nsc' => 'application/x-conference', 'cpio' => 'application/x-cpio',
							   'csh' => 'application/x-csh', 'deb' => 'application/x-debian-package', 'udeb' => 'application/x-debian-package', 'dgc' => 'application/x-dgc-compressed',
							   'wad' => 'application/x-doom', 'ncx' => 'application/x-dtbncx+xml', 'dtb' => 'application/x-dtbook+xml', 'res' => 'application/x-dtbresource+xml',
							   'dvi' => 'application/x-dvi', 'evy' => 'application/x-envoy', 'eva' => 'application/x-eva', 'bdf' => 'application/x-font-bdf',
							   'gsf' => 'application/x-font-ghostscript', 'psf' => 'application/x-font-linux-psf', 'otf' => 'application/x-font-otf', 'pcf' => 'application/x-font-pcf',
							   'snf' => 'application/x-font-snf', 'ttf' => 'application/x-font-ttf', 'ttc' => 'application/x-font-ttf', 'pfa' => 'application/x-font-type1',
							   'pfb' => 'application/x-font-type1', 'pfm' => 'application/x-font-type1', 'afm' => 'application/x-font-type1', 'woff' => 'application/font-woff',
							   'arc' => 'application/x-freearc', 'spl' => 'application/x-futuresplash', 'gca' => 'application/x-gca-compressed', 'ulx' => 'application/x-glulx',
							   'gnumeric' => 'application/x-gnumeric', 'gramps' => 'application/x-gramps-xml', 'gtar' => 'application/x-gtar', 'hdf' => 'application/x-hdf',
							   'install' => 'application/x-install-instructions', 'iso' => 'application/x-iso9660-image', 'jnlp' => 'application/x-java-jnlp-file', 'latex' => 'application/x-latex',
							   'lzh' => 'application/x-lzh-compressed', 'lha' => 'application/x-lzh-compressed', 'mie' => 'application/x-mie', 'prc' => 'application/x-mobipocket-ebook',
							   'mobi' => 'application/x-mobipocket-ebook', 'application' => 'application/x-ms-application', 'lnk' => 'application/x-ms-shortcut', 'wmd' => 'application/x-ms-wmd',
							   'wmz' => 'application/x-ms-wmz', 'xbap' => 'application/x-ms-xbap', 'mdb' => 'application/x-msaccess', 'obd' => 'application/x-msbinder',
							   'crd' => 'application/x-mscardfile', 'clp' => 'application/x-msclip', 'exe' => 'application/x-msdownload', 'dll' => 'application/x-msdownload',
							   'com' => 'application/x-msdownload', 'bat' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'mvb' => 'application/x-msmediaview',
							   'm13' => 'application/x-msmediaview', 'm14' => 'application/x-msmediaview', 'wmf' => 'application/x-msmetafile',
							   'emf' => 'application/x-msmetafile', 'emz' => 'application/x-msmetafile', 'mny' => 'application/x-msmoney', 'pub' => 'application/x-mspublisher',
							   'scd' => 'application/x-msschedule', 'trm' => 'application/x-msterminal', 'wri' => 'application/x-mswrite', 'nc' => 'application/x-netcdf',
							   'cdf' => 'application/x-netcdf', 'nzb' => 'application/x-nzb', 'p12' => 'application/x-pkcs12', 'pfx' => 'application/x-pkcs12',
							   'p7b' => 'application/x-pkcs7-certificates', 'spc' => 'application/x-pkcs7-certificates', 'p7r' => 'application/x-pkcs7-certreqresp', 'rar' => 'application/x-rar-compressed',
							   'ris' => 'application/x-research-info-systems', 'sh' => 'application/x-sh', 'shar' => 'application/x-shar', 'swf' => 'application/x-shockwave-flash',
							   'xap' => 'application/x-silverlight-app', 'sql' => 'application/x-sql', 'sit' => 'application/x-stuffit', 'sitx' => 'application/x-stuffitx',
							   'srt' => 'application/x-subrip', 'sv4cpio' => 'application/x-sv4cpio', 'sv4crc' => 'application/x-sv4crc', 't3' => 'application/x-t3vm-image',
							   'gam' => 'application/x-tads', 'tar' => 'application/x-tar', 'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex',
							   'tfm' => 'application/x-tex-tfm', 'texinfo' => 'application/x-texinfo', 'texi' => 'application/x-texinfo', 'obj' => 'application/x-tgif',
							   'ustar' => 'application/x-ustar', 'src' => 'application/x-wais-source', 'der' => 'application/x-x509-ca-cert', 'crt' => 'application/x-x509-ca-cert',
							   'fig' => 'application/x-xfig', 'xlf' => 'application/x-xliff+xml', 'xpi' => 'application/x-xpinstall', 'xz' => 'application/x-xz',
							   'z1' => 'application/x-zmachine', 'z2' => 'application/x-zmachine', 'z3' => 'application/x-zmachine', 'z4' => 'application/x-zmachine',
							   'z5' => 'application/x-zmachine', 'z6' => 'application/x-zmachine', 'z7' => 'application/x-zmachine', 'z8' => 'application/x-zmachine',
							   'xaml' => 'application/xaml+xml', 'xdf' => 'application/xcap-diff+xml', 'xenc' => 'application/xenc+xml', 'xhtml' => 'application/xhtml+xml',
							   'xht' => 'application/xhtml+xml', 'xml' => 'application/xml', 'xsl' => 'application/xml', 'dtd' => 'application/xml-dtd',
							   'xop' => 'application/xop+xml', 'xpl' => 'application/xproc+xml', 'xslt' => 'application/xslt+xml', 'xspf' => 'application/xspf+xml',
							   'mxml' => 'application/xv+xml', 'xhvml' => 'application/xv+xml', 'xvml' => 'application/xv+xml', 'xvm' => 'application/xv+xml',
							   'yang' => 'application/yang', 'yin' => 'application/yin+xml', 'zip' => 'application/zip', 'adp' => 'audio/adpcm',
							   'au' => 'audio/basic', 'snd' => 'audio/basic', 'mid' => 'audio/midi', 'midi' => 'audio/midi',
							   'kar' => 'audio/midi', 'rmi' => 'audio/midi', 'mp4a' => 'audio/mp4', 'mpga' => 'audio/mpeg',
							   'mp2' => 'audio/mpeg', 'mp2a' => 'audio/mpeg', 'mp3' => array( 'audio/mpeg', 'audio/mp3' ), 'm2a' => 'audio/mpeg',
							   'm3a' => 'audio/mpeg', 'm4a' => 'audio/mp4','oga' => 'audio/ogg', 'ogg' => 'audio/ogg', 'spx' => 'audio/ogg',
							   's3m' => 'audio/s3m', 'sil' => 'audio/silk', 'uva' => 'audio/vnd.dece.audio', 'uvva' => 'audio/vnd.dece.audio',
							   'eol' => 'audio/vnd.digital-winds', 'dra' => 'audio/vnd.dra', 'dts' => 'audio/vnd.dts', 'dtshd' => 'audio/vnd.dts.hd',
							   'lvp' => 'audio/vnd.lucent.voice', 'pya' => 'audio/vnd.ms-playready.media.pya', 'ecelp4800' => 'audio/vnd.nuera.ecelp4800', 'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
							   'ecelp9600' => 'audio/vnd.nuera.ecelp9600', 'rip' => 'audio/vnd.rip', 'weba' => 'audio/webm', 'aac' => 'audio/x-aac',
							   'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'caf' => 'audio/x-caf',
							   'flac' => 'audio/x-flac', 'mka' => 'audio/x-matroska', 'm3u' => 'audio/x-mpegurl', 'wax' => 'audio/x-ms-wax',
							   'wma' => 'audio/x-ms-wma', 'ram' => 'audio/x-pn-realaudio', 'ra' => 'audio/x-pn-realaudio', 'rmp' => 'audio/x-pn-realaudio-plugin',
							   'wav' => array( 'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/vnd.wave' ), 'wave' => array( 'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/vnd.wave' ),
							   'xm' => 'audio/xm', 'cdx' => 'chemical/x-cdx', 'cif' => 'chemical/x-cif',
							   'cmdf' => 'chemical/x-cmdf', 'cml' => 'chemical/x-cml', 'csml' => 'chemical/x-csml', 'xyz' => 'chemical/x-xyz',
							   'bmp' => 'image/bmp', 'cgm' => 'image/cgm', 'g3' => 'image/g3fax', 'gif' => 'image/gif',
							   'ief' => 'image/ief', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'jpe' => 'image/jpeg',
							   'ktx' => 'image/ktx', 'png' => 'image/png', 'btif' => 'image/prs.btif', 'sgi' => 'image/sgi',
							   'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml', 'tiff' => 'image/tiff', 'tif' => 'image/tiff',
							   'psd' => 'image/vnd.adobe.photoshop', 'uvi' => 'image/vnd.dece.graphic', 'uvvi' => 'image/vnd.dece.graphic', 'uvg' => 'image/vnd.dece.graphic',
							   'uvvg' => 'image/vnd.dece.graphic', 'sub' => 'image/vnd.dvb.subtitle', 'djvu' => 'image/vnd.djvu', 'djv' => 'image/vnd.djvu',
							   'dwg' => 'image/vnd.dwg', 'dxf' => 'image/vnd.dxf', 'fbs' => 'image/vnd.fastbidsheet', 'fpx' => 'image/vnd.fpx',
							   'fst' => 'image/vnd.fst', 'mmr' => 'image/vnd.fujixerox.edmics-mmr', 'rlc' => 'image/vnd.fujixerox.edmics-rlc', 'mdi' => 'image/vnd.ms-modi',
							   'wdp' => 'image/vnd.ms-photo', 'npx' => 'image/vnd.net-fpx', 'wbmp' => 'image/vnd.wap.wbmp', 'xif' => 'image/vnd.xiff',
							   'webp' => 'image/webp', '3ds' => 'image/x-3ds', 'ras' => 'image/x-cmu-raster', 'cmx' => 'image/x-cmx',
							   'fh' => 'image/x-freehand', 'fhc' => 'image/x-freehand', 'fh4' => 'image/x-freehand', 'fh5' => 'image/x-freehand',
							   'fh7' => 'image/x-freehand', 'ico' => 'image/x-icon', 'sid' => 'image/x-mrsid-image', 'pcx' => 'image/x-pcx',
							   'pic' => 'image/x-pict', 'pct' => 'image/x-pict', 'pnm' => 'image/x-portable-anymap', 'pbm' => 'image/x-portable-bitmap',
							   'pgm' => 'image/x-portable-graymap', 'ppm' => 'image/x-portable-pixmap', 'rgb' => 'image/x-rgb', 'tga' => 'image/x-tga',
							   'xbm' => 'image/x-xbitmap', 'xpm' => 'image/x-xpixmap', 'xwd' => 'image/x-xwindowdump', 'eml' => 'message/rfc822',
							   'mime' => 'message/rfc822', 'igs' => 'model/iges', 'iges' => 'model/iges', 'msh' => 'model/mesh',
							   'mesh' => 'model/mesh', 'silo' => 'model/mesh', 'dae' => 'model/vnd.collada+xml', 'dwf' => 'model/vnd.dwf',
							   'gdl' => 'model/vnd.gdl', 'gtw' => 'model/vnd.gtw', 'mts' => 'model/vnd.mts', 'vtu' => 'model/vnd.vtu',
							   'wrl' => 'model/vrml', 'vrml' => 'model/vrml', 'x3db' => 'model/x3d+binary', 'x3dbz' => 'model/x3d+binary',
							   'x3dv' => 'model/x3d+vrml', 'x3dvz' => 'model/x3d+vrml', 'x3d' => 'model/x3d+xml', 'x3dz' => 'model/x3d+xml',
							   'appcache' => 'text/cache-manifest', 'ics' => 'text/calendar', 'ifb' => 'text/calendar', 'css' => 'text/css',
							   'csv' => 'text/csv', 'html' => 'text/html', 'htm' => 'text/html', 'n3' => 'text/n3',
							   'txt' => 'text/plain', 'text' => 'text/plain', 'conf' => 'text/plain', 'def' => 'text/plain',
							   'list' => 'text/plain', 'log' => 'text/plain', 'in' => 'text/plain', 'dsc' => 'text/prs.lines.tag',
							   'rtx' => 'text/richtext', 'sgml' => 'text/sgml', 'sgm' => 'text/sgml', 'tsv' => 'text/tab-separated-values',
							   't' => 'text/troff', 'tr' => 'text/troff', 'roff' => 'text/troff', 'man' => 'text/troff',
							   'me' => 'text/troff', 'ms' => 'text/troff', 'ttl' => 'text/turtle', 'uri' => 'text/uri-list',
							   'uris' => 'text/uri-list', 'urls' => 'text/uri-list', 'vcard' => 'text/vcard', 'curl' => 'text/vnd.curl',
							   'dcurl' => 'text/vnd.curl.dcurl', 'scurl' => 'text/vnd.curl.scurl', 'mcurl' => 'text/vnd.curl.mcurl',
							   'fly' => 'text/vnd.fly', 'flx' => 'text/vnd.fmi.flexstor', 'gv' => 'text/vnd.graphviz', '3dml' => 'text/vnd.in3d.3dml',
							   'spot' => 'text/vnd.in3d.spot', 'jad' => 'text/vnd.sun.j2me.app-descriptor', 'wml' => 'text/vnd.wap.wml', 'wmls' => 'text/vnd.wap.wmlscript',
							   's' => 'text/x-asm', 'asm' => 'text/x-asm', 'c' => 'text/x-c', 'cc' => 'text/x-c',
							   'cxx' => 'text/x-c', 'cpp' => 'text/x-c', 'h' => 'text/x-c', 'hh' => 'text/x-c',
							   'dic' => 'text/x-c', 'f' => 'text/x-fortran', 'for' => 'text/x-fortran', 'f77' => 'text/x-fortran',
							   'f90' => 'text/x-fortran', 'java' => 'text/x-java-source', 'opml' => 'text/x-opml', 'p' => 'text/x-pascal',
							   'pas' => 'text/x-pascal', 'nfo' => 'text/x-nfo', 'etx' => 'text/x-setext', 'sfv' => 'text/x-sfv',
							   'uu' => 'text/x-uuencode', 'vcs' => 'text/x-vcalendar', 'vcf' => 'text/x-vcard', '3gp' => 'video/3gpp',
							   '3g2' => 'video/3gpp2', 'h261' => 'video/h261', 'h263' => 'video/h263', 'h264' => 'video/h264',
							   'jpgv' => 'video/jpeg', 'jpm' => 'video/jpm', 'jpgm' => 'video/jpm', 'mj2' => 'video/mj2',
							   'mjp2' => 'video/mj2', 'mp4' => array( 'video/mp4', 'audio/mp4' ), 'mp4v' => 'video/mp4', 'mpg4' => 'video/mp4',
							   'mpeg' => array( 'video/mpeg', 'audio/mpeg' ), 'mpg' => array( 'video/mpeg', 'audio/mpeg' ), 'mpe' => 'video/mpeg', 'm1v' => 'video/mpeg',
							   'm2v' => 'video/mpeg', 'ogv' => 'video/ogg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',
							   'uvh' => 'video/vnd.dece.hd', 'uvvh' => 'video/vnd.dece.hd', 'uvm' => 'video/vnd.dece.mobile', 'uvvm' => 'video/vnd.dece.mobile',
							   'uvp' => 'video/vnd.dece.pd', 'uvvp' => 'video/vnd.dece.pd', 'uvs' => 'video/vnd.dece.sd', 'uvvs' => 'video/vnd.dece.sd',
							   'uvv' => 'video/vnd.dece.video', 'uvvv' => 'video/vnd.dece.video', 'dvb' => 'video/vnd.dvb.file', 'fvt' => 'video/vnd.fvt',
							   'mxu' => 'video/vnd.mpegurl', 'm4u' => 'video/vnd.mpegurl', 'pyv' => 'video/vnd.ms-playready.media.pyv', 'uvu' => 'video/vnd.uvvu.mp4',
							   'uvvu' => 'video/vnd.uvvu.mp4', 'viv' => 'video/vnd.vivo', 'webm' => 'video/webm', 'f4v' => 'video/x-f4v',
							   'fli' => 'video/x-fli', 'flv' => array( 'video/flv', 'audio/flv', 'video/x-flv', 'audio/x-flv' ), 'm4v' => 'video/m4v', 'mkv' => 'video/x-matroska',
							   'mk3d' => 'video/x-matroska', 'mks' => 'video/x-matroska', 'mng' => 'video/x-mng', 'asf' => 'video/x-ms-asf',
							   'asx' => 'video/x-ms-asf', 'vob' => 'video/x-ms-vob', 'wm' => 'video/x-ms-wm', 'wmv' => 'video/x-ms-wmv',
							   'wmx' => 'video/x-ms-wmx', 'wvx' => 'video/x-ms-wvx', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie',
							   'smv' => 'video/x-smv', 'ice' => 'x-conference/x-cooltalk'
		);

		return $map;
	}

	/**
	 * Gets the MIME type for $ext file extension (one if string, for each if array)
	 *
	 * @param  string|array  $ext
	 * @return string|array
	 */
	function cbGetMimeFromExt( $ext ) {
		static $cache						=	array();

		if ( is_array( $ext ) ) {
			$cacheId						=	md5( serialize( $ext ) );

			if ( ! isset( $cache[$cacheId] ) ) {
				$mimes						=	array();

				if ( $ext ) {
					$map					=	cbGetMimeMap();

					foreach ( $ext as $e ) {
						if ( isset( $map[$e] ) ) {
							if ( is_array( $map[$e] ) ) {
								$mimes		=	array_merge( $mimes, $map[$e] );
							} else {
								$mimes[]	=	$map[$e];
							}
						}
					}
				}

				$cache[$cacheId]			=	array_unique( $mimes );
			}
		} else {
			$cacheId						=	$ext;

			if ( ! isset( $cache[$cacheId] ) ) {
				$mime						=	'application/octet-stream';

				if ( $ext ) {
					$map					=	cbGetMimeMap();
					$mime					=	( isset( $map[$ext] ) ? ( is_array( $map[$ext] ) ? $map[$ext][0] : $map[$ext] ) : 'application/octet-stream' );
				}

				$cache[$cacheId]			=	$mime;
			}
		}

		return $cache[$cacheId];
	}

	/**
	 * class cbCalendars and class cbTabs and class cbPMS and class cbConnection and class cbNotification are now in libraries/CBLib/CB/Legacy folder
	 */

	/**
	 * Translates connection types list |*|-separated to be listed, ', '-separated
	 *
	 * @param  string $types
	 * @return string
	 */
	function getConnectionTypes( $types ) {
		$typelist	=	null;
		$types		=	explode( "|*|", $types );
		foreach( $types AS $type ) {
			if( $typelist == null ) {
				$typelist	=	CBTxt::T( $type );
			} else {
				$typelist	.=	", " . CBTxt::T( $type );
			}
		}
		return $typelist;
	}

	function cbPoweredBy()
	{
		global $ueConfig;

		if ( isset( $ueConfig['poweredBy'] ) && ( ! $ueConfig['poweredBy'] ) ) {
			return null;
		}

		$input				=	Application::Input();
		$url				=	$input->get( 'server/SERVER_NAME', null, GetterInterface::STRING ) . $input->get( 'server/REQUEST_URI', null, GetterInterface::STRING );

		$urls				=	array(
										array( 'title' => 'social network platform', 'url' => 'http://www.joomlapolis.com/social-networking?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'community software', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'online community software', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'social networking software', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'open source social networking', 'url' => 'http://www.joomlapolis.com/social-networking?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'social network script', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'social community software', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'online social networking', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'social websites', 'url' => 'http://www.joomlapolis.com/social-networking?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'online community sites', 'url' => 'http://www.joomlapolis.com/community-builder?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'how to build a social networking site', 'url' => 'http://www.joomlapolis.com?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'how to create a social network', 'url' => 'http://www.joomlapolis.com?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'online membership sites', 'url' => 'http://www.joomlapolis.com/cb-solutions/cbsubs?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'online paid subscription sites', 'url' => 'http://www.joomlapolis.com/cb-solutions/cbsubs?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'membership sites', 'url' => 'http://www.joomlapolis.com/cb-solutions/cbsubs?pk_campaign=in-cb&pk_kwd=poweredby' ),
										array( 'title' => 'paid membership sites', 'url' => 'http://www.joomlapolis.com/cb-solutions/cbsubs?pk_campaign=in-cb&pk_kwd=poweredby' )
									);

		list( $urlBits )	=	sscanf( substr( md5( $url ), -4 ), '%4x' );

		$key				=	( $urlBits % count( $urls ) );

		$return				=	'<div class="cbPoweredBy cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
							.		'<div class="text-center text-small content-spacer">'
							.			'<a title="' . htmlspecialchars( $urls[$key]['title'] ) . '" href="' . htmlspecialchars( $urls[$key]['url'] ) . '" target="_blank">'
							.				'Powered by Community Builder'
							.			'</a>'
							.		'</div>'
							.	'</div>';

		return $return;
	}

	/**
	 * CB 1.x ACL DEPRECIATED functions:
	 */

	/**
	 * TODO: We need to convert remaining uses of this function!
	 * @deprecated 2.0 use Application::User( (int) $user_id )->isGlobalModerator();
	 * @see User::isGlobalModerator()
	 */
	function isModerator( $oID ) {
		global $_CB_framework;

		return $_CB_framework->acl->get_user_moderator( $oID );
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * @deprecated 2.0
	 */
	function allowAccess( $accessgroupid, $recurse, $usersgroupid ) {
		global $_CB_framework;

		return $_CB_framework->acl->get_allowed_access( $accessgroupid, $recurse, $usersgroupid );
	}

	/**
	 * TODO: We need to convert remaining uses of this function! And to decide on how to do it!
	 * @deprecated 2.0
	 */
	function cbGetAllUsergroupsBelowMe() {
		global $_CB_framework;

		return $_CB_framework->acl->get_groups_below_me();
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * @deprecated 2.0
	 */
	function getParentGIDS( $gid = null ) {
		global $_CB_framework;

		return $_CB_framework->acl->get_group_parent_ids( $gid );
	}

	/**
	 * TODO: We need to convert remaining uses of this function!
	 * @deprecated 2.0 No use anymore for such functionality, since we have Permissions for that and we should not be depending on groups
	 */
	function checkCBpermissions( $cid, $actionName, $allowActionToMyself = false ) {
		global $_CB_framework;

		return $_CB_framework->acl->get_users_permission( $cid, $actionName, $allowActionToMyself );
	}

	/**
	 * This FRONT-END function checks if the logged-in user is allowed to edit another user $uid as a moderator
	 * TODO: We need to convert remaining uses of this function! Current uses are only: cbCheckIfUserCanPerformUserTask( $user->id, 'allowModeratorsUserEdit' )
	 * We need to add that Config allowModeratorsUserEdit as param when we remove its use.
	 *
	 * @deprecated 2.0
	 *
	 * @param  int     $uid              The other user to edit
	 * @param  string  $ueConfigVarName  'allowModeratorsUserEdit' ONLY !
	 * @return boolean
	 */
	function cbCheckIfUserCanPerformUserTask( $uid, $ueConfigVarName ) {
		global $_CB_framework;

		return $_CB_framework->acl->get_user_permission_task( $uid, $ueConfigVarName );
	}
}
