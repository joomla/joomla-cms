<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Site
 * @subpackage	Contacts
 */
class ContactViewContact extends JView
{
	protected $state = null;
	protected $contact = null;
	
	function display($tpl = null)
	{
		$app		= &JFactory::getApplication();
		$user		= &JFactory::getUser();
		$pathway	= &$app->getPathway();
		$document	= & JFactory::getDocument();
		$state 		= $this->get('State');
		$contact 	= $this->get('Contact');
		
		// report any errors and exit if they exist
		$this->reportErrors($this->get('Errors'));
		
		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu    = $menus->getActive();

		$pparams = &$app->getParams('com_contact');

		// check if access is registered/special
		$groups	= $user->authorisedLevels();

		$return ="";
		if ((!in_array($contact->access, $groups)) || (!in_array($contact->category_access, $groups))) {
			$uri		= JFactory::getURI();
			$return		= $uri->toString();

			$url  = 'index.php?option=com_users&view=login';
			$url .= '&return='.base64_encode($return);

			$app->redirect($url, JText::_('YOU_MUST_LOGIN_FIRST'));

		}

		$options['category_id']	= $contact->catid;
		$options['order by']	= 'cd.default_con DESC, cd.ordering ASC';

		$contacts = &$this->getModel('Category')->getContacts($options);

		// Set the document page title
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'contact' && isset($menu->query['id']) && $menu->query['id'] == $contact->id) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$pparams->set('page_title',	$contact->name);
			}
		} else {
			$pparams->set('page_title',	$contact->name);
		}
		$document->setTitle($pparams->get('page_title'));

		//set breadcrumbs
		if (isset($menu) && isset($menu->query['view']) && $menu->query['view'] != 'contact'){
			$pathway->addItem($contact->name, '');
		}

		// Adds parameter handling
		$contact->params = new JParameter($contact->params);

		$pparams->merge($contact->params);



		// Handle email cloaking
		if ($contact->email_to && $contact->params->get('show_email')) {
			$contact->email_to = JHtml::_('email.cloak', $contact->email_to);
		}

		if ($contact->params->get('show_street_address') || $contact->params->get('show_suburb') || $contact->params->get('show_state') || $contact->params->get('show_postcode') || $contact->params->get('show_country'))
		{
			if (!empty ($contact->address) || !empty ($contact->suburb) || !empty ($contact->state) || !empty ($contact->country) || !empty ($contact->postcode)) {
				$contact->params->set('address_check', 1);
			}
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
				$contact->params->set('marker_mobile',		JText::_('Mobile').": ");
				$contact->params->set('marker_misc', 		JText::_('Information').": ");
				$contact->params->set('marker_class', 		'jicons-text');
				break;

			case 2 :
				// none
				$contact->params->set('marker_address', 	'');
				$contact->params->set('marker_email', 		'');
				$contact->params->set('marker_telephone', 	'');
				$contact->params->set('marker_mobile', 	'');
				$contact->params->set('marker_fax', 		'');
				$contact->params->set('marker_misc', 		'');
				$contact->params->set('marker_class', 		'jicons-none');
				break;

			default :
				// icons
				$image1 = JHtml::_('image.site', 'con_address.png', 	'/images/system/', $contact->params->get('icon_address'), 	'/images/system/', JText::_('Address').": ");
				$image2 = JHtml::_('image.site', 'emailButton.png', 	'/images/system/', $contact->params->get('icon_email'), 		'/images/system/', JText::_('Email').": ");
				$image3 = JHtml::_('image.site', 'con_tel.png', 		'/images/system/', $contact->params->get('icon_telephone'), 	'/images/system/', JText::_('Telephone').": ");
				$image4 = JHtml::_('image.site', 'con_fax.png', 		'/images/system/', $contact->params->get('icon_fax'), 		'/images/system/', JText::_('Fax').": ");
				$image5 = JHtml::_('image.site', 'con_info.png', 		'/images/system/', $contact->params->get('icon_misc'), 		'/images/system/', JText::_('Information').": ");
				$image6 = JHtml::_('image.site', 'con_mobile.png', 		'/images/system/', $contact->params->get('icon_mobile'), 	'/images/system/', JText::_('Mobile').": ");

				$contact->params->set('marker_address', 	$image1);
				$contact->params->set('marker_email', 		$image2);
				$contact->params->set('marker_telephone', 	$image3);
				$contact->params->set('marker_fax', 		$image4);
				$contact->params->set('marker_misc',		$image5);
				$contact->params->set('marker_mobile', 		$image6);
				$contact->params->set('marker_class', 		'jicons-icons');
				break;
		}
	JHtml::_('behavior.formvalidation');

		$this->assignRef('contact',		$contact);
		$this->assignRef('contacts',	$contacts);
		$this->assignRef('params',		$pparams);
		$this->assignRef('return',		$return);

		parent::display($tpl);
	}
	/**
	 * Checks for errors and exits with messages if found
	 * @param	Array of errors
	 * @return	null
	 */
	function reportErrors($errors)
	{
		if (!$errors || !is_array($errors)) {
			return;
		}
		foreach ($errors as &$error)
		{
			if ($error instanceof Exception)
			{
				if ($error->getCode() == 404)
				{
					// If there is a 404, throw a hard error.
					JError::raiseError(404, $error->getMessage());
					return false;
				}
				else
				{
					JError::raiseError(500, $error->getMessage());
				}
			}
			else
			{
				JError::raiseWarning(500, $error);
			}
		}
		return false;

	}
}
		
