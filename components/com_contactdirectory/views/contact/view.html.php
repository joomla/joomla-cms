<?php
/**
 * @version		$Id: view.html.php 10094 2008-03-02 04:35:10Z instance $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class ContactdirectoryViewContact extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$user		= &JFactory::getUser();
		$pathway	= &$mainframe->getPathway();
		$document	= & JFactory::getDocument();

		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		$pparams = &$mainframe->getParams('com_contactdirectory');

		$cparams = JComponentHelper::getParams ('com_media');

		// Push a model into the view
		$model	= &$this->getModel();

		//get the contact
		$contact	=& $model->getData($user->get('aid', 0));

		//get the fields
		$fields =& $model->getFields();

		// check if we have a contact
		if (!is_object( $contact )) {
			JError::raiseError( 404, 'CONTACT NOT FOUND' );
			return;
		}

		// check if we have the fields
		if (!is_array($fields)) {
			JError::raiseError( 404, 'CONTACT NOT FOUND' );
			return;
		}

		// Adds parameter handling
		$contact->params = new JParameter($contact->params);
		$pparams->merge($contact->params);

		$email = null;
		foreach($fields as $field){
			$field->params = new JParameter($field->params);

			if($field->type == 'image'){
				if($field->data){
					if($field->pos == 'right'){
						$field->data = JHtml::_('image', $cparams->get('image_path') . '/'.$field->data, JText::_( 'CONTACT' ), array('align' => 'right'));
					}else{
						$field->data = JHtml::_('image', $cparams->get('image_path') . '/'.$field->data, JText::_( 'CONTACT' ), array('align' => 'left'));
					}

				}
			}

			if($field->type == 'textarea'){
				$field->data = nl2br($field->data);
			}

			if($field->type == 'url'){
				if(!empty($field->data)){
					$field->data = '<a href="http://'.$field->data.'">'.$field->data.'</a>';
				}
			}

			// Handle email cloaking
			if($field->type == 'email') {
				jimport('joomla.mail.helper');
				$field->data = trim($field->data);
				if(!empty($field->data) && JMailHelper::isEmailAddress($field->data)) {
					$field->data = JHtml::_('email.cloak', $field->data);
				}else{
					$field->data = '';
				}
				if($field->id == 1){
					$email = $field;
				}
			}

			// Manage the display mode for the field title
			switch ($field->params->get('field_title'))
			{
				case 0 :
					// text
					$field->params->set('marker_title', 	JText::_($field->title).": ");
					break;
				case 1:
					//icon and text
					$image = JHtml::_('image.site', 'arrow.png', 	'/images/M_images/', $field->params->get('choose_icon'), 	'/images/M_images/', JText::_($field->title).": ");
					$field->params->set('marker_title', 	$image);
					break;
				case 2 :
					// icons
					$image = JHtml::_('image.site', 'arrow.png', 	'/images/M_images/', $field->params->get('choose_icon'), 	'/images/M_images/', JText::_($field->title).": ");
					$field->params->set('marker_title', 	$image." ".JText::_($field->title).": ");
					break;
				case 3 :
					// none
					$field->params->set('marker_title', 	'');
					break;
			}

			switch ($field->pos){
				case 'title':
					$pos_title[] = $field;
					break;
				case 'top':
					$pos_top[] = $field;
					break;
				case 'left':
					$pos_left[] = $field;
					break;
				case 'main':
					$pos_main[] = $field;
					break;
				case 'right':
					$pos_right[] = $field;
					break;
				case 'bottom':
					$pos_bottom[] = $field;
					break;
			}
		}

		// Set the document page title
		$document->setTitle(JText::_('CONTACT').' - '.$contact->name);

		//set breadcrumbs
		if (isset( $menu ) && isset($menu->query['view']) && $menu->query['view'] != 'contact'){
			$pathway->addItem($contact->name, '');
		}

		JHtml::_('behavior.formvalidation');

		$captcha = null;
		if($contact->params->get('show_captcha')) {
			 $captcha = JHtml::image('index.php?option=com_contactdirectory&task=captcha&amp;format=raw&amp;sid=' . md5(uniqid(time())), 'captcha', array('id'=>'captcha-img'));
		}

		$showFormTitle = false;
		$showFormTop = false;
		$showFormLeft = false;
		$showFormMain = false;
		$showFormRight = false;
		$showFormBottom = false;

		if($contact->params->get('email_form_pos') == 'title' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormTitle = true;
		}

		if($contact->params->get('email_form_pos') == 'top' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormTop = true;
		}

		if($contact->params->get('email_form_pos') == 'left' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormLeft = true;
		}

		if($contact->params->get('email_form_pos') == 'main' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormMain = true;
		}

		if($contact->params->get('email_form_pos') == 'right' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormRight = true;
		}

		if($contact->params->get('email_form_pos') == 'bottom' &&
			$contact->params->get('show_email_form') &&
			($email->data || $contact->user_id) &&
			$contact->params->get('email_form_access') <= $user->get('aid', 0)){
				$showFormBottom = true;
		}

		// Fill up the form with the original data after summit error
		$data =& $this->get('FormData');

		JHtml::stylesheet('contactdirectory.css', 'components/com_contactdirectory/css/');

		$this->assignRef('contact',	$contact);
		$this->assignRef('pos_title', $pos_title);
		$this->assignRef('pos_top',	$pos_top);
		$this->assignRef('pos_left',	$pos_left);
		$this->assignRef('pos_main', $pos_main);
		$this->assignRef('pos_right', $pos_right);
		$this->assignRef('pos_bottom', $pos_bottom);
		$this->assignRef('showFormTitle', $showFormTitle);
		$this->assignRef('showFormTop', $showFormTop);
		$this->assignRef('showFormLeft', $showFormLeft);
		$this->assignRef('showFormMain', $showFormMain);
		$this->assignRef('showFormRight', $showFormRight);
		$this->assignRef('showFormBottom', $showFormBottom);
		$this->assignRef('params',	$pparams);
		$this->assignRef('email', $email);
		$this->assignRef('captcha', $captcha);
		$this->assignRef('user',	$user);
		$this->assignRef('data', $data);

		parent::display($tpl);
	}
}
