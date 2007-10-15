<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
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

jimport( 'joomla.application.component.controller' );

/**
 * Contact Component Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.5
 */
class ContactController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document =& JFactory::getDocument();

		$viewName	= JRequest::getVar('view', 'category', 'default', 'cmd');
		$viewType	= $document->getType();

		// interceptors to support legacy urls
		switch ($this->getTask())
		{
			//index.php?option=com_contact&task=category&id=0&Itemid=4
			case 'category':
				$viewName	= 'category';
				$layout		= 'default';
				break;
			case 'view':
				$viewName	= 'contact';
				$layout		= 'default';
				break;
		}

		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// Push a model into the view
		$model	= &$this->getModel( $viewName );
		if (!JError::isError( $model )) {
			$view->setModel( $model, true );
		}

		// Workaround for the item view
		if ($viewName == 'contact')
		{
			$modelCat	= &$this->getModel( 'category' );
			$view->setModel( $modelCat );
		}

		// Display the view
		$view->assign('error', $this->getError());
		$view->display();
	}

	/**
	 * Method to send an email to a contact
	 *
	 * @static
	 * @since 1.0
	 */
	function submit()
	{
		global $mainframe;

		//check the token before we do anything else
		$token	= JUtility::getToken();
		if (!JRequest::getInt( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$SiteName	= $mainframe->getCfg('sitename');

		$default	= JText::sprintf( 'MAILENQUIRY', $SiteName );
		$contactId	= JRequest::getInt( 'id',			0,			'post' );
		$name		= JRequest::getVar( 'name',			'',			'post' );
		$email		= JRequest::getVar( 'email',		'',			'post' );
		$subject	= JRequest::getVar( 'subject',		$default,	'post' );
		$body		= JRequest::getVar( 'text',			'',			'post' );
		$emailCopy	= JRequest::getInt( 'email_copy', 	0,			'post' );

		 // load the contact details
		$model		= &$this->getModel('contact');

		// query options
		$qOptions['id']	= $contactId;
		$contact		= $model->getContact( $qOptions );

		if($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		/*
		 * If there is no valid email address or message body then we throw an
		 * error and return false.
		 */
		jimport('joomla.mail.helper');
		if (!$email || !$body || (JMailHelper::isEmailAddress($email) == false))
		{
			$this->setError(JText::_('CONTACT_FORM_NC'));
			$this->display();
			return false;
		}

		// Contact plugins
		JPluginHelper::importPlugin( 'contact' );
		$dispatcher	=& JDispatcher::getInstance();

		// Input validation
		if  (!$this->_validateInputs( $contact, $email, $subject, $body ) ) {
			JError::raiseWarning( 0, $this->getError() );
			return false;
		}

		// Custom handlers
		$post		= JRequest::get( 'post' );
		$results	= $dispatcher->trigger( 'onValidateContact', array( &$contact, &$post ) );

		foreach ($results as $result)
		{
			if (JError::isError( $result )) {
				return false;
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$results	= $dispatcher->trigger( 'onSubmitContact', array( &$contact, &$post ) );

		$pparams = &$mainframe->getParams('com_contact');
		if (!$pparams->get( 'custom_reply' ))
		{
			$MailFrom 	= $mainframe->getCfg('mailfrom');
			$FromName 	= $mainframe->getCfg('fromname');

			// Prepare email body
			$prefix = JText::sprintf('ENQUIRY_TEXT', JURI::base());
			$body 	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($body);

			$mail = JFactory::getMailer();

			$mail->addRecipient( $contact->email_to );
			$mail->setSender( array( $email, $name ) );
			$mail->setSubject( $FromName.': '.$subject );
			$mail->setBody( $body );

			$sent = $mail->Send();

			/*
			 * If we are supposed to copy the admin, do so.
			 */
			// parameter check
			$params = new JParameter( $contact->params );
			$emailcopyCheck = $params->get( 'show_email_copy', 0 );

			// check whether email copy function activated
			if ( $emailCopy && $emailcopyCheck )
			{
				$copyText 		= JText::sprintf('Copy of:', $contact->name, $SiteName);
				$copyText 		.= "\r\n\r\n".$body;
				$copySubject 	= JText::_('Copy of:')." ".$subject;

				$mail = JFactory::getMailer();

				$mail->addRecipient( $email );
				$mail->setSender( array( $MailFrom, $FromName ) );
				$mail->setSubject( $copySubject );
				$mail->setBody( $copyText );

				$sent = $mail->Send();
			}
		}

		$this->setError( JText::_( 'Thank you for your e-mail'));
		$this->display();
	}

	/**
	 * Method to output a vCard
	 *
	 * @static
	 * @since 1.0
	 */
	function vcard()
	{
		global $mainframe;

		// Initialize some variables
		$db = & JFactory::getDBO();

		$SiteName = $mainframe->getCfg('sitename');
		$contactId = JRequest::getVar('contact_id', 0, '', 'int');
		// Get a Contact table object and load the selected contact details
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_contact'.DS.'tables');
		$contact =& JTable::getInstance('contact', 'Table');
		$contact->load($contactId);

		// Get the contact detail parameters
		$pparams = &$mainframe->getParams('com_contact');

		// Should we show the vcard?
		if ($pparams->get('allow_vcard', 0))
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
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_contact'.DS.'helpers'.DS.'vcard.php');
			$v = new JvCard();

			$v->setPhoneNumber($contact->telephone, 'PREF;WORK;VOICE');
			$v->setPhoneNumber($contact->fax, 'WORK;FAX');
			$v->setName($surname, $firstname, $middlename, '');
			$v->setAddress('', '', $contact->address, $contact->suburb, $contact->state, $contact->postcode, $contact->country, 'WORK;POSTAL');
			$v->setEmail($contact->email_to);
			$v->setNote($contact->misc);
			$v->setURL( JURI::base(), 'WORK');
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
			JError::raiseWarning('SOME_ERROR_CODE', 'ContactController::vCard: '.JText::_('NOTAUTH'));
			return false;
		}
	}

	/**
	 * Validates some inputs based on component configuration
	 *
	 * @param Object	$contact	JTable Object
	 * @param String	$email		Email address
	 * @param String	$subject	Email subject
	 * @param String	$body		Email body
	 * @return Boolean
	 * @access protected
	 * @since 1.5
	 */
	function _validateInputs( $contact, $email, $subject, $body )
	{
		global $mainframe;

		$session =& JFactory::getSession();

		/**
		$model		= $this->getModel('contact');
		$options['category_id']	= $contact->catid;
		$options['order by']	= 'a.default_con DESC, a.ordering ASC';
		$contact 		= $model->getContact( $options );
		**/

		// Get params and component configurations
		$params		= new JParameter($contact->params);
		$pparams	= &$mainframe->getParams('com_contact');

		// check for session cookie
		$sessionCheck 	= $pparams->get( 'validate_session', 1 );
		$sessionName	= $session->getName();
		if  ( $sessionCheck ) {
			if ( !isset($_COOKIE[$sessionName]) ) {
				$this->setError( JText::_('ALERTNOTAUTH') );
				return false;
			}
		}

		// Determine banned e-mails
		$configEmail	= $pparams->get( 'banned_email', '' );
		$paramsEmail	= $params->get( 'banned_mail', '' );
		$bannedEmail 	= $configEmail . ($paramsEmail ? ';'.$paramsEmail : '');

		// Prevent form submission if one of the banned text is discovered in the email field
		if ( $bannedEmail ) {
			$bannedEmail = explode( ';', $bannedEmail );
			foreach ($bannedEmail as $value) {

				if ( JString::stristr($email, $value) ) {
					$this->setError( JText::sprintf('MESGHASBANNEDTEXT', 'Email') );
					return false;
				}
			}
		}

		// Determine banned subjects
		$configSubject	= $pparams->get( 'banned_subject', '' );
		$paramsSubject	= $params->get( 'banned_subject', '' );
		$bannedSubject 	= $configSubject . ( $paramsSubject ? ';'.$paramsSubject : '');

		// Prevent form submission if one of the banned text is discovered in the subject field
		if ( $bannedSubject ) {
			$bannedSubject = explode( ';', $bannedSubject );
			foreach ($bannedSubject as $value) {
				if ( JString::stristr($subject, $value) ) {
					$this->setError( JText::sprintf('MESGHASBANNEDTEXT', 'Subject') );
					return false;
				}
			}
		}

		// Determine banned Text
		$configText		= $pparams->get( 'banned_text', '' );
		$paramsText		= $params->get( 'banned_text', '' );
		$bannedText 	= $configText . ( $paramsText ? ';'.$paramsText : '' );

		// Prevent form submission if one of the banned text is discovered in the text field
		if ( $bannedText ) {
			$bannedText = explode( ';', $bannedText );
			foreach ($bannedText as $value) {
				if ( JString::stristr($body, $value) ) {
					$this->setError( JText::sprintf('MESGHASBANNEDTEXT', 'Message') );
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
}