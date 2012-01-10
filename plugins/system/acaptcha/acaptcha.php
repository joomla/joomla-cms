<?php
/**
 * @version		$Id: acaptcha.php 06/01/2012 10.58
 * @copyright	Copyright (C) 2005 - 2012 alikonweb. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
//jimport('joomla.utilities.date');

/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgSystemAcaptcha extends JPlugin
{
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		// Load user_profile plugin language
  		$lang = JFactory::getLanguage();
		  $lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		//var_dump('sp:'.$form->getName());
		// Detection frontend/backedn
		$app = JFactory::getApplication();
		$backend=false;
		if ($app->isAdmin())
		{
			$backend=true;
		}
		// detection for visitor/users
		if ($this->params->get('forguestonly', 1)==0){
			$member=false;
		}	else		{
			
		  $member=false;
		  $user = JFactory::getUser();
			if (!$user->guest)
			{
			$member=true;
			}
	  }	
     
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_login','mod_login.captcha','com_weblinks.weblink','com_content.article','com_contact.contact','com_users.reset_request', 'com_users.registration','com_users.remind','com_users.login'))) {
			return true;
		}
		
    // Toggle whether the captcha field is required.
		if (
		
		   (($this->params->get('ajax-captcha_editweblink', 1) > 0) && ($form->getName()==='com_weblinks.weblink') && (!$member)) ||
		   
		   (($this->params->get('ajax-captcha_editarticle', 1) > 0) && ($form->getName()==='com_content.article') && (!$member)) ||
		   
		   (($this->params->get('ajax-captcha_registration', 1) > 0) && ($form->getName()==='com_users.registration')) ||
		 
		   (($this->params->get('ajax-captcha_remind', 1) > 0)       && ($form->getName()==='com_users.remind')) ||
	
		   (($this->params->get('ajax-captcha_reset', 1) > 0)        && ($form->getName()==='com_users.reset_request')) ||
		   
		   (($this->params->get('ajax-captcha_contact', 1) > 0)       && ($form->getName()==='com_contact.contact')&& (!$member)) ||
		   
		   (($this->params->get('ajax-captcha_login', 1) > 0)       && ($form->getName()==='com_users.login')) ||

		    (($this->params->get('ajax-captcha_modblogin', 1) > 0)       && ($form->getName()==='mod_login.captcha') && ($backend)) ||
		   
		    (($this->params->get('ajax-captcha_modflogin', 1) > 0)       && ($form->getName()==='mod_login.captcha') && (!$backend))
		 
		   )
		{  
			
			//   var_dump('sp:'.$form->getName());
			if ($form->getName()==='mod_login.captcha')  {  
						//	 var_dump('md:'.$this->params->get('ajax-captcha_modblogin', 1));
				  JForm::addFormPath(dirname(__FILE__).DS.'acaptcha');
				 $form->loadFile('modcaptcha', true);
				} else {	
			   JForm::addFormPath(dirname(__FILE__).DS.'acaptcha');
			   $form->loadFile('acaptcha', true);
			 }		   
		}
		else {
			$form->removeField('acaptcha', 'acaptcha');
		}

		

		return true;
	}


}
