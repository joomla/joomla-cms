<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.controller' );

/**
 * Contact Component Controller
 *
 * @static
 * @package Joomla
 * @subpackage Contact
 * @since 1.5
 */
class JContactControllerDefault extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$cParams	= &JSiteHelper::getControlParams();
		$task		= $this->getTask();

		// interceptor to support old request formats
		// ?option=com_contact&task=view     - contact item
		// ?option=com_contact&task=category - list a category (default)
		switch ($task)
		{
			case 'view':
				JRequest::setVar( 'view',	'contact' );
				break;
		}

		// Set the default view name from the Request
		$viewName = JRequest::getVar( 'view', $cParams->get( 'view_name', 'category' ) );

		$this->setViewName( $viewName, 'com_contact', 'JContactView' );
		$view = &$this->getView();

		// Display the view
		$view->display();
	}

	/**
	 * Validates some inputs based on component configuration
	 * @return boolean
	 */
	function validateInputs( $email, $subject, $body )
	{
		$config = &JComponentHelper::getParams( 'com_contact' );
		$bannedEmail 	= $config->get( 'bannedEmail', 	'' );
		$bannedSubject 	= $config->get( 'bannedSubject', 	'' );
		$bannedText 	= $config->get( 'bannedText', 		'' );
		$sessionCheck 	= $config->get( 'sessionCheck', 	1 );

		// check for session cookie
		if  ( $sessionCheck ) {
			if ( !isset($_COOKIE[JSession::name()]) ) {
				$this->setError( _NOT_AUTH );
				return false;
			}
		}

		// Prevent form submission if one of the banned text is discovered in the email field
		if ( $bannedEmail ) {
			$bannedEmail = explode( ';', $bannedEmail );
			foreach ($bannedEmail as $value) {
				if ( JString::stristr($email, $value) ) {
					$this->setError( _NOT_AUTH );
					return false;
				}
			}
		}
		// Prevent form submission if one of the banned text is discovered in the subject field
		if ( $bannedSubject ) {
			$bannedSubject = explode( ';', $bannedSubject );
			foreach ($bannedSubject as $value) {
				if ( JString::stristr($subject, $value) ) {
					$this->setError( _NOT_AUTH );
					return false;
				}
			}
		}
		// Prevent form submission if one of the banned text is discovered in the text field
		if ( $bannedText ) {
			$bannedText = explode( ';', $bannedText );
			foreach ($bannedText as $value) {
				if ( JString::stristr($body, $value) ) {
					$this->setError( _NOT_AUTH );
					return false;
				}
			}
		}

		// test to ensure that only one email address is entered
		$check = explode( '@', $email );
		if ( strpos( $email, ';' ) || strpos( $email, ',' ) || strpos( $email, ' ' ) || count( $check ) > 2 ) {
			$this->setError( JText::_( 'You cannot enter more than one email address', true ) );
			return false;
		}


		return true;
	}

	/**
	 * Method to send an email to a contact
	 *
	 * @static
	 * @since 1.0
	 */
	function sendmail()
	{
		global $Itemid;

		// Initialize some variables
		$app		= &$this->getApplication();
		$db			= & JFactory::getDBO();

		$SiteName 	= $app->getCfg('sitename');
		$MailFrom 	= $app->getCfg('mailfrom');
		$FromName 	= $app->getCfg('fromname');

		$default 	= sprintf(JText::_('MAILENQUIRY'), $SiteName);
		$contactId 	= JRequest::getVar('contact_id', 	0, 			'post', 'int');
		$name 		= JRequest::getVar('name', 			'', 		'post');
		$email 		= JRequest::getVar('email', 		'', 		'post');
		$subject 	= JRequest::getVar('subject', 		$default, 	'post');
		$body 		= JRequest::getVar('body', 			'', 		'post');
		$emailCopy 	= JRequest::getVar('email_copy', 	0, 			'post', 'int');

		// probably a spoofing attack
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return false;
		}

		/*
		 * Load the contact details
		 */
		$model		= &$this->getModel('contact', 'JContactModel');

		// query options
		$qOptions['id']	= $contactId;
		$contact		= $model->getContact( $qOptions );

		/*
		 * If there is no valid email address or message body then we throw an
		 * error and return false.
		 */
		jimport('joomla.utilities.mail');
		if (!$email || !$body || (JMailHelper::isEmailAddress($email) == false))
		{
			JRequest::setVar( 'view', '_error' );
			$this->display();
			return false;
		}

		// input validation
		if  (!$this->validateInputs( $email, $subject, $body ) ) {
			JError::raiseWarning( 0, $this->getError() );
			return false;
		}

		// Prepare email body
		$prefix = sprintf(JText::_('ENQUIRY_TEXT'), $app->getBaseURL());
		$body 	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($body);

		$mail = new JMail();

		$mail->addRecipient( $contact->email_to );
		$mail->setSender( array( $email, $name ) );
		$mail->setSubject( $FromName.': '.$subject );
		$mail->setBody( $body );

		$sent = $mail->Send();
		echo (int)$sent;

		/*
		 * If we are supposed to copy the admin, do so.
		 */
		// parameter check
		$menuParams 		= new JParameter( $contact->params );
		$emailcopyCheck = $menuParams->get( 'email_copy', 0 );

		// check whether email copy function activated
		if ( $emailCopy && $emailcopyCheck ) {
			$copyText 		= sprintf(JText::_('Copy of:'), $contact->name, $SiteName);
			$copyText 		.= "\r\n\r\n".$body;
			$copySubject 	= JText::_('Copy of:')." ".$subject;

			$mail = new JMail();

			$mail->addRecipient( $email );
			$mail->setSender( array( $MailFrom, $FromName ) );
			$mail->setSubject( $copySubject );
			$mail->setBody( $copyText );

			$sent = $mail->Send();
			echo (int)$sent;
		}

		$link = sefRelToAbs( 'index.php?option=com_contact&task=view&contact_id='. $contactId .'&Itemid='. $Itemid );
		$text = JText::_( 'Thank you for your e-mail', true );

		$this->setRedirect( $link, $text );
	}

	/**
	 * Method to output a vCard
	 *
	 * @static
	 * @since 1.0
	 */
	function vCard() 
	{
		global $mainframe;

		// Initialize some variables
		$db = & JFactory::getDBO();

		$SiteName = $mainframe->getCfg('sitename');
		$contactId = JRequest::getVar('contact_id', 0, '', 'int');

		// Get a JContact table object and load the selected contact details
		$contact = new JTableContact($db);
		$contact->load($contactId);

		// Get the contact detail parameters
		$menuParams = new JParameter($contact->params);
		$show 	= $menuParams->get('vcard', 0);

		// Should we show the vcard?
		if ($show) 
		{
			// Parse the contact name field and build the nam information for the vcard.
			$firstname 	= null;
			$middlename = null;
			$surname 	= null;

			// How many parts do we have?
			$parts = explode(' ', $contact->name);
			$count = count($parts);

			switch ($count) {
				case 1 :
					// only a first name
					$firstname = $parts[0];
					break;

				case 2 :
					// first and last name
					$firstname = $parts[0];
					$surname = $parts[1];
					break;

				default :
					// we have full name info
					$firstname = $parts[0];
					$surname = $parts[$count -1];
					for ($i = 1; $i < $count -1; $i ++) {
						$middlename .= $parts[$i].' ';
					}
					break;
			}
			// quick cleanup for the middlename value
			$middlename = trim($middlename);

			// Create a new vcard object and populate the fields
			$v = new JvCard();

			$v->setPhoneNumber($contact->telephone, 'PREF;WORK;VOICE');
			$v->setPhoneNumber($contact->fax, 'WORK;FAX');
			$v->setName($surname, $firstname, $middlename, '');
			$v->setAddress('', '', $contact->address, $contact->suburb, $contact->state, $contact->postcode, $contact->country, 'WORK;POSTAL');
			$v->setEmail($contact->email_to);
			$v->setNote($contact->misc);
			$v->setURL( $mainframe->getBaseURL(), 'WORK');
			$v->setTitle($contact->con_position);
			$v->setOrg($SiteName);

			$filename = str_replace(' ', '_', $contact->name);
			$v->setFilename($filename);

			$output = $v->getVCard($SiteName);
			$filename = $v->getFileName();

			// Send vCard file headers
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Length: '.strlen($output));
			header('Connection: close');
			header('Content-Type: text/x-vCard; name='.$filename);
			header('Cache-Control: store, cache');
			header('Pragma: cache');

			print $output;
		} else {
			JError::raiseWarning('SOME_ERROR_CODE', 'JContactController::vCard: '.JText::_('NOTAUTH'));
			return false;
		}
	}
}
?>