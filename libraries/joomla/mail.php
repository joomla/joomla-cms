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

jimport('phpmailer.phpmailer');

/**
 * E-Mail Class.  Provides a common interface to send e-mail from the Joomla! Framework
 *
 * @author		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @since 1.1
 */
class JMail extends PHPMailer {

	/**
	 * Constructor
	 * 
	 * @param
	 */
	function JMail() {
		global $mainframe;

		$this->PluginDir = JPATH_LIBRARIES.DS.'phpmailer'.DS;
		$this->SetLanguage('en', JPATH_LIBRARIES.DS.'includes'.DS.'phpmailer'.DS.'language'.DS);
		$this->CharSet = "utf-8";

		/*
		 * Set the default mail sender address and name
		 */
		$this->setSender(array ($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));

		/*
		 * Default mailer is to use PHP's mail function
		 */
		switch ($mainframe->getCfg('mailer')) {
			case 'smtp' :
				$this->useSMTP();
				break;
			case 'sendmail' :
				$this->useSendmail();
				break;
			default :
				$this->IsMail();
				break;
		}
	}

	/**
	 * Returns a reference to a global e-mail object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $mail =& JMail :: getInstance();</pre>
	 * 
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Joomla'.
	 *
	 * @static
	 * @access public
	 * @param string $id The id string for the JMail instance [optional]
	 * @return object The global JMail object
	 * @since 1.1
	 */
	function & getInstance($id = 'Joomla') {
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = new JAuth();
		}

		return $instances[$id];
	}

	/**
	 * Set the E-Mail sender
	 * 
	 * @access public
	 * @param array $from E-Mail address and Name of sender
	 * 		<pre>
	 * 			array( [0] => E-Mail Address [1] => Name )
	 * 		</pre>
	 * @return void
	 * @since 1.1
	 */
	function setSender($from) {

		/*
		 * First offset is the e-mail address
		 * Second offset is the name
		 */
		$this->From = $from[0];
		$this->FromName = $from[1];
	}

	/**
	 * Set the E-Mail subject
	 * 
	 * @access public
	 * @param string $subject Subject of the e-mail
	 * @return void
	 * @since 1.1
	 */
	function setSubject($subject) {

		$this->Subject = $subject;
	}

	/**
	 * Set the E-Mail body
	 * 
	 * @access public
	 * @param string $content Body of the e-mail
	 * @return void
	 * @since 1.1
	 */
	function setBody($content) {

		/*
		 * Filter the Body
		 * TODO: Check for XSS
		 */
		$this->Body = $content;
	}

	/**
	 * Add recipients to the email
	 * 
	 * @access public
	 * @param mixed $recipient Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.1
	 */
	function addRecipient($recipient) {

		/*
		 * If the recipient is an aray, add each recipient... otherwise just add the one
		 */
		if (is_array($recipient)) {
			foreach ($recipient as $to) {
				$this->AddAddress($to);
			}
		} else {
			$this->AddAddress($recipient);
		}
	}

	/**
	 * Add carbon copy recipients to the email
	 * 
	 * @access public
	 * @param mixed $cc Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.1
	 */
	function addCC($cc) {

		/*
		 * If the carbon copy recipient is an aray, add each recipient... otherwise just add the one
		 */
		if (isset ($cc)) {
			if (is_array($cc)) {
				foreach ($cc as $to) {
					parent :: AddCC($to);
				}
			} else {
				parent :: AddCC($cc);
			}
		}
	}

	/**
	 * Add blind carbon copy recipients to the email
	 * 
	 * @access public
	 * @param mixed $cc Either a string or array of strings [e-mail address(es)]
	 * @return void
	 * @since 1.1
	 */
	function addBCC($bcc) {

		/*
		 * If the blind carbon copy recipient is an aray, add each recipient... otherwise just add the one
		 */
		if (isset ($bcc)) {
			if (is_array($bcc)) {
				foreach ($bcc as $to) {
					parent :: AddBCC($to);
				}
			} else {
				parent :: AddBCC($bcc);
			}
		}
	}

	/**
	 * Add file attachments to the email
	 * 
	 * @access public
	 * @param mixed $attachment Either a string or array of strings [filenames]
	 * @return void
	 * @since 1.1
	 */
	function addAttachment($attachment) {

		/*
		 * If the file attachments is an aray, add each file... otherwise just add the one
		 */
		if (isset ($attachment)) {
			if (is_array($attachment)) {
				foreach ($attachment as $file) {
					parent :: AddAttachment($file);
				}
			} else {
				parent :: AddAttachment($attachment);
			}
		}
	}

	/**
	 * Add Reply to e-mail address(es) to the e-mail
	 * 
	 * @access public
	 * @param array $reply Either an array or multi-array of form
	 * 		<pre>
	 * 			array( [0] => E-Mail Address [1] => Name )
	 * 		</pre>
	 * @return void
	 * @since 1.1
	 */
	function addReplyTo($replyto) {

		/*
		 * Take care of reply email addresses
		 */
		if (is_array($replyto[0])) {
			foreach ($replyto as $to) {
				parent :: AddReplyTo($to[0], $to[1]);
			}
		} else {
			parent :: AddReplyTo($replyto[0], $replyto[1]);
		}
	}

	/**
	 * Use sendmail for sending the e-mail
	 * 
	 * @access public
	 * @param string $sendmail Path to sendmail [optional]
	 * @return boolean True on success
	 * @since 1.1
	 */
	function useSendmail($sendmail = null) {
		global $mainframe;

		$this->Sendmail = (empty ($sendmail)) ? $mainframe->getCfg('sendmail') : $sendmail;

		if (!empty ($this->Sendmail)) {
			$this->IsSendmail();
			return true;
		} else {
			$this->IsMail();
			return false;
		}
	}

	/**
	 * Use SMTP for sending the e-mail
	 * 
	 * @access public
	 * @param string $auth SMTP Authentication [optional]
	 * @param string $host SMTP Host [optional]
	 * @param string $user SMTP Username [optional]
	 * @param string $pass SMTP Password [optional]
	 * @return boolean True on success
	 * @since 1.1
	 */
	function useSMTP($auth = null, $host = null, $user = null, $pass = null) {
		global $mainframe;

		$this->SMTPAuth = (empty ($auth)) ? $mainframe->getCfg('smtpauth') : $auth;
		$this->Host = (empty ($host)) ? $mainframe->getCfg('smtphost') : $host;
		$this->Username = (empty ($user)) ? $mainframe->getCfg('smtpuser') : $user;
		$this->Password = (empty ($pass)) ? $mainframe->getCfg('smtppass') : $pass;

		if (!empty ($this->SMTPAuth) && !empty ($this->Host) && !empty ($this->Username) && !empty ($this->Password)) {
			$this->IsSMTP();
			return true;
		} else {
			$this->IsMail();
			return false;
		}
	}
}

/**
 * E-Mail helper class, provides static methods to perform various tasks relevant
 * to the Joomla e-mail routines.
 * 
 * TODO: Test these methods as the regex work is first run and not tested thoroughly
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @static
 * @since 1.1
 */
class JMailHelper {

	/**
	 * This method cleans any injected headers from the E-Mail body
	 * 
	 * @static
	 * @param string $body E-Mail body string
	 * @return string Cleaned E-Mail body string
	 * @since 1.1
	 */
	function cleanBody(& $body) {
		/*
		 * Strip all E-Mail headers from the body
		 */
		return preg_replace("/((From:|To:|Cc:|Bcc:|Subject:|Content-type:) ([\S]+))/", "", $body);
	}

	/**
	 * This method cleans any injected headers from the subject string.
	 * 
	 * @static
	 * @param string $subject E-Mail subject string
	 * @return string Cleaned E-Mail subject string
	 * @since 1.1
	 */
	function cleanSubject($subject) {
		
		return preg_replace("/((From:|To:|Cc:|Bcc:|Content-type:) ([\S]+))/", "", $subject);
	}

	/**
	 * This method verifies that an e-mail address does not have any extra headers
	 * injected into it.  Tests one e-mail address.
	 * 
	 * @static
	 * @param string $address E-Mail address
	 * @return mixed E-Mail address string or boolean false if injected headers are present
	 * @since 1.1
	 */
	function cleanAddress($address) {
		if (preg_match("[\s;,]", $address)) {
			return false;
		}
		return $address;
	}
}
?>