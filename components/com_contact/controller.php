<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
class JContactController extends JController {

	function display()
	{
		$cParams	= &JComponentHelper::getControlParams();
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
	 * Method to send an email to a contact
	 *
	 * @static
	 * @since 1.0
	 */
	function sendmail()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();

		$SiteName 	= $mainframe->getCfg('sitename');
		$MailFrom 	= $mainframe->getCfg('mailfrom');
		$FromName 	= $mainframe->getCfg('fromname');
		$validate 	= mosHash( $mainframe->getCfg('db') );

		$default 	= sprintf(JText::_('MAILENQUIRY'), $SiteName);
		$option 	= JRequest::getVar('option');
		$contactId 	= JRequest::getVar('con_id');
		$validate 	= JRequest::getVar($validate, 		0, 			'post');
		$email 		= JRequest::getVar('email', 		'', 		'post');
		$text 		= JRequest::getVar('text', 			'', 		'post');
		$name 		= JRequest::getVar('name', 			'', 		'post');
		$subject 	= JRequest::getVar('subject', 		$default, 	'post');
		$emailCopy 	= JRequest::getVar('email_copy', 	0, 			'post');

		// probably a spoofing attack
		if (!$validate) {
			mosErrorAlert( _NOT_AUTH );
		}

		/*
		 * This obviously won't catch all attempts, but it does not hurt to make
		 * sure the request came from a client with a user agent string.
		 */
		if (!isset ($_SERVER['HTTP_USER_AGENT'])) {
			mosErrorAlert( _NOT_AUTH );
		}

		/*
		 * This obviously won't catch all attempts either, but we ought to check
		 * to make sure that the request was posted as well.
		 */
		if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
			mosErrorAlert( _NOT_AUTH );
		}

		// An array of e-mail headers we do not want to allow as input
		$headers = array ('Content-Type:',
						  'MIME-Version:',
						  'Content-Transfer-Encoding:',
						  'bcc:',
						  'cc:');

		// An array of the input fields to scan for injected headers
		$fields = array ('email',
						 'text',
						 'name',
						 'subject',
						 'email_copy');

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we fine one, send an unauthorized header and die.
		 */
		foreach ($fields as $field) {
			foreach ($headers as $header) {
				if (strpos($_POST[$field], $header) !== false) {
					mosErrorAlert( _NOT_AUTH );
				}
			}
		}

		/*
		 * Now that we have passed the header injection tests lets free up the
		 * used memory and continue.
		 */
		unset ($fields, $field, $headers, $header);

		/*
		 * Load the contact details
		 */
		$contact = new JTableContact($db);
		$contact->load($contactId);

		/*
		 * If there is no valid email address or message body then we throw an
		 * error and return false.
		 */
		jimport('joomla.utilities.mail');
		if (!$email || !$text || (JMailHelper::isEmailAddress($email) == false)) {
			JContactView::emailError();
		} else {
			$config = &JComponentHelper::getParams( 'com_contact' );
			$bannedEmail 	= $config->get( 'bannedEmail', 	'' );
			$bannedSubject 	= $config->get( 'bannedSubject', 	'' );
			$bannedText 	= $config->get( 'bannedText', 		'' );
			$sessionCheck 	= $config->get( 'sessionCheck', 	1 );

			// check for session cookie
			if  ( $sessionCheck ) {
				if ( !isset($_COOKIE[JSession::name()]) ) {
					mosErrorAlert( _NOT_AUTH );
				}
			}

			// Prevent form submission if one of the banned text is discovered in the email field
			if ( $bannedEmail ) {
				$bannedEmail = explode( ';', $bannedEmail );
				foreach ($bannedEmail as $value) {
					if ( JString::stristr($email, $value) ) {
						mosErrorAlert( _NOT_AUTH );
					}
				}
			}
			// Prevent form submission if one of the banned text is discovered in the subject field
			if ( $bannedSubject ) {
				$bannedSubject = explode( ';', $bannedSubject );
				foreach ($bannedSubject as $value) {
					if ( JString::stristr($subject, $value) ) {
						mosErrorAlert( _NOT_AUTH );
					}
				}
			}
			// Prevent form submission if one of the banned text is discovered in the text field
			if ( $bannedText ) {
				$bannedText = explode( ';', $bannedText );
				foreach ($bannedText as $value) {
					if ( JString::stristr($text, $value) ) {
						mosErrorAlert( _NOT_AUTH );
					}
				}
			}

			// test to ensure that only one email address is entered
			$check = explode( '@', $email );
			if ( strpos( $email, ';' ) || strpos( $email, ',' ) || strpos( $email, ' ' ) || count( $check ) > 2 ) {
				mosErrorAlert( JText::_( 'You cannot enter more than one email address', true ) );
			}

			/*
			 * Prepare email body
			 */
			$prefix = sprintf(JText::_('ENQUIRY_TEXT'), $mainframe->getBaseURL());
			$text 	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($text);

			// Send mail
			josMail($email, $name, $contact->email_to, $FromName.': '.$subject, $text);

			/*
			 * If we are supposed to copy the admin, do so.
			 */
			// parameter check
			$menuParams 		= new JParameter( $contact->params );
			$emailcopyCheck = $menuParams->get( 'email_copy', 0 );

			// check whether email copy function activated
			if ( $emailCopy && $emailcopyCheck ) {
				$copyText 		= sprintf(JText::_('Copy of:'), $contact->name, $SiteName);
				$copyText 		.= "\r\n\r\n".$text;
				$copySubject 	= JText::_('Copy of:')." ".$subject;
				josMail($MailFrom, $FromName, $email, $copySubject, $copyText);
			}

			$link = sefRelToAbs( 'index.php?option=com_contact&task=view&contact_id='. $contactId .'&Itemid='. $Itemid );
			$text = JText::_( 'Thank you for your e-mail', true );

			josRedirect( $link, $text );
		}
	}

	/**
	 * Method to output a vCard
	 *
	 * @static
	 * @since 1.0
	 */
	function vCard() {
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();

		$SiteName = $mainframe->getCfg('sitename');
		$contactId = JRequest::getVar('contact_id', 0, '', 'int');

		/*
		 * Get a JContact table object and load the selected contact details
		 */
		$contact = new JTableContact($db);
		$contact->load($contactId);

		/*
		 * Get the contact detail parameters
		 */
		$menuParams = new JParameter($contact->params);
		$show 	= $menuParams->get('vcard', 0);

		/*
		 * Should we show the vcard?
		 */
		if ($show) {
			/*
			 * We need to parse the contact name field and build the name
			 * information for the vcard.
			 */
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

			/*
			 * Create a new vcard object and populate the fields
			 */
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