<?php
/**
 * @version		$Id: controller.php 4645 2006-08-22 10:23:04Z eddiea $
 * @package		Joomla
 * @subpackage	MailTo
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * @package		Joomla
 * @subpackage	MailTo
 */
class MailtoController extends JController
{
	function send()
	{
		global $mainframe;
		
		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		} 

		$db	=& JFactory::getDBO();

		jimport( 'joomla.utilities.mail' );

		$SiteName 	= $mainframe->getCfg('sitename');
		$MailFrom 	= $mainframe->getCfg('mailfrom');
		$FromName 	= $mainframe->getCfg('fromname');

		$link 		= urldecode( JRequest::getVar( 'link', '', 'post' ) );

		// An array of e-mail headers we do not want to allow as input
		$headers = array (	'Content-Type:',
							'MIME-Version:',
							'Content-Transfer-Encoding:',
							'bcc:',
							'cc:');

		// An array of the input fields to scan for injected headers
		$fields = array ('mailto',
						 'sender',
						 'from',
						 'subject',
						 );

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we fine one, send an unauthorized header and die.
		 */
		foreach ($fields as $field)
		{
			foreach ($headers as $header)
			{
				if (strpos($_POST[$field], $header) !== false)
				{
					JError::raiseError(403, '');
				}
			}
		}

		/*
		 * Free up memory
		 */
		unset ($headers, $fields);

		$email 				= JRequest::getVar( 'mailto', '', 'post' );
		$sender 			= JRequest::getVar( 'sender', '', 'post' );
		$from 				= JRequest::getVar( 'from', '', 'post' );
		$subject_default 	= JText::sprintf('Item sent by', $sender);
		$subject 			= JRequest::getVar( 'subject', $subject_default, 'post' );

		if (!$email || !$from || (JMailHelper::isEmailAddress($email) == false) || (JMailHelper::isEmailAddress($from) == false)) {
			ContentView :: userInputError(JText :: _('EMAIL_ERR_NOINFO'));
		}

		// Build the link to send in the email
		$link = JRoute::_($link);

		// Build the message to send
		$msg = JText :: _('EMAIL_MSG');
		$body = sprintf( $msg, $SiteName, $sender, $from, $link);

		// Clean the email data
		$subject = JMailHelper::cleanSubject($subject);
		$body	 = JMailHelper::cleanBody($body);
		$sender	 = JMailHelper::cleanAddress($sender);

		// Send the email
		JUtility::sendMail($from, $sender, $email, $subject, $body);

		JRequest::setVar( 'view', 'sent' );
		$this->display();
	}
}
?>
