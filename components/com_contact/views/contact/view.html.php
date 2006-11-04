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

jimport('joomla.application.component.view');

/**
 * @package Joomla
 * @subpackage Contacts
 */
class ContactViewContact extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid;

		$user	  = &JFactory::getUser();
		$pathway  = & $mainframe->getPathWay();
		$document = & JFactory::getDocument();
		$model	  = &$this->getModel();

		// Get the paramaters of the active menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();
		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$params->def('header', 					$menu->name );
		$params->def('print', 					!$contentConfig->get('hidePrint'));
		$params->def('email_description_text', JText::_('Send an Email to this Contact:'));
		$params->def('icons', 					$contentConfig->get('icons'));

		// Push a model into the view
		$model		= &$this->getModel();
		$modelCat	= &$this->getModel( 'ContactModelCategory' );

		// Selected Request vars
		$contactId	= JRequest::getVar( 'id', $params->get('id', 0 ), '', 'int' );

		// query options
		$options['id']	= $contactId;
		$options['gid']	= $user->get('gid');

		$contact  = $model->getContact( $options );

		// check if we have a contact
		if (!is_object( $contact )) {
			JError::raiseError( 404, 'Contact not found' );
			return;
		}

		$options['category_id']  = $contact->catid;
		$options['order by']	 = 'a.default_con DESC, a.ordering ASC';

		$contacts = $modelCat->getContacts( $options );

		// Set the document page title
		$mainframe->setPageTitle(JText::_('Contact').' - '.$contact->name);

		// Add the breadcrumbs items
		if (!$params->get('hideCatCrumbs')) {
			$pathway->addItem($contact->category_name, "index.php?option=com_contact&catid=$contact->catid&Itemid=$Itemid");
		}
		$pathway->addItem($contact->name, '');

		// Adds parameter handling
		$contact->params = new JParameter($contact->params);

		$contact->params->def( 'name', 				1 );
		$contact->params->def( 'email', 			0 );
		$contact->params->def( 'street_address', 	1 );
		$contact->params->def( 'suburb', 			1 );
		$contact->params->def( 'state', 			1 );
		$contact->params->def( 'country', 			1 );
		$contact->params->def( 'postcode', 			1 );
		$contact->params->def( 'telephone', 		1 );
		$contact->params->def( 'fax', 				1 );
		$contact->params->def( 'misc', 				1 );
		$contact->params->def( 'image', 			1 );

		if ($contact->email_to && $params->get('email')) {
			$contact->email = JHTML::emailCloaking($contact->email_to);
		}

		if ($contact->params->get('email_description')) {
			$contact->params->set('email_description', $params->get('email_description_text'));
		} else {
			$contact->params->set('email_description', '');
		}

		if (!empty ($contact->address) || !empty ($contact->suburb) || !empty ($contact->state) || !empty ($contact->country) || !empty ($contact->postcode)) {
			$contact->params->set('address_check', 1);
		} else {
			$contact->params->set('address_check', 0);
		}

		 // Manage the display mode for contact detail groups
		switch ($contact->params->get('contact_icons'))
		{
			case 1 :
				// text
				$contact->params->set('marker_address', 	JText::_('Address').": ");
				$contact->params->set('marker_email', 		JText::_('Email').": ");
				$contact->params->set('marker_telephone', 	JText::_('Telephone').": ");
				$contact->params->set('marker_fax', 		JText::_('Fax').": ");
				$contact->params->set('marker_misc', 		JText::_('Information').": ");
				$contact->params->set('column_width', 		'100');
				break;

			case 2 :
				// none
				$contact->params->set('marker_address', 	'');
				$contact->params->set('marker_email', 		'');
				$contact->params->set('marker_telephone', 	'');
				$contact->params->set('marker_fax', 		'');
				$contact->params->set('marker_misc', 		'');
				$contact->params->set('column_width', 		'0');
				break;

			default :
				// icons
				$image1 = JAdminMenus::ImageCheck('con_address.png', 	'/images/M_images/', $contact->params->get('icon_address'), 	'/images/M_images/', JText::_('Address').": ", 		JText::_('Address').": ");
				$image2 = JAdminMenus::ImageCheck('emailButton.png', 	'/images/M_images/', $contact->params->get('icon_email'), 		'/images/M_images/', JText::_('Email').": ", 		JText::_('Email').": ");
				$image3 = JAdminMenus::ImageCheck('con_tel.png', 		'/images/M_images/', $contact->params->get('icon_telephone'), 	'/images/M_images/', JText::_('Telephone').": ", 	JText::_('Telephone').": ");
				$image4 = JAdminMenus::ImageCheck('con_fax.png', 		'/images/M_images/', $contact->params->get('icon_fax'), 		'/images/M_images/', JText::_('Fax').": ", 			JText::_('Fax').": ");
				$image5 = JAdminMenus::ImageCheck('con_info.png', 	'/images/M_images/', $contact->params->get('icon_misc'), 		'/images/M_images/', JText::_('Information').": ", 	JText::_('Information').": ");
				$contact->params->set('marker_address', 	$image1);
				$contact->params->set('marker_email', 		$image2);
				$contact->params->set('marker_telephone', 	$image3);
				$contact->params->set('marker_fax', 		$image4);
				$contact->params->set('marker_misc',		$image5);
				$contact->params->set('column_width', 		'40');
				break;
		}

		$document->addScript('includes/js/joomla/common.js');
		$document->addScript('includes/js/joomla/validation.js');

		$this->assignRef('contacts' , $contacts);
		$this->assignRef('contact'  , $contact);
		$this->assignRef('params'   , $params);

		parent::display($tpl);
	}
}
?>