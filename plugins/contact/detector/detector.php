<?php
/**
 * @version		$Id: detector.php 18/09/2011 11.49
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2011 Alikonweb.it, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Contact Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since		1.5
 */
class plgContactDetector extends JPlugin
{
	
       
	/**
	
	 */
	public function onValidateContact($contact, $post)
	{
		$app = JFactory::getApplication();
		$lang = JFactory :: getLanguage();
    $lang->load('plg_contact_detector', JPATH_ADMINISTRATOR);
    //load parameters
    //$plugin =& JPluginHelper::getPlugin('contact', 'detector');
    //$pluginParams = new JParameter( $plugin->params );   	
    //$mail2con = $pluginParams->get( 'sendmail2con',0 );	
	  //$mail2usr = $pluginParams->get( 'sendmail2usr',0 );
	  //$blockspammer = $pluginParams->get( 'blockspammer',0 );
	  //$checktype = $pluginParams->get( 'checktype',0 );
	  	// Get the data from POST
	//	$data = JRequest::getVar('jform', array(), 'post', 'array');
/*	 var_dump('p:'.$post['contact_email']);
    jexit('post');*/
    //Define parameters	
     $mail2con = $this->params->get('sendmail2con', 0 );
	   $mail2usr = $this->params->get( 'sendmail2usr',0 );
	   $blockspammer = $this->params->get( 'blockspammer',0 );
	   $checktype = $this->params->get( 'checktype',0 );
    
    if( $app->isSite() ){ 
		 
		      if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')) {
		         JPluginHelper::importPlugin( 'alikonweb' );
	
		         $dispatcher =& JDispatcher::getInstance();
             $info_detector = $dispatcher->trigger( 'onDetect',array(3,null,$post['contact_email'],$post['contact_name'],$post['contact_message'],' ') );       
         		if ($info_detector[0]['score'] >=4){
         			
         			if(($mail2con == '1')||($mail2usr == '1')){ 
         				 $subject='[Detector4Contact] '.$post['contact_subject'].' from:('.$post['contact_name'].') ';
         				 $mbody	= JText::sprintf(
				         'PLG_CONTACT_EMAIL_PENDING_BODY',
				         $post['contact_message'],
				         $post['contact_name'],
				         $info_detector[0]['text'],
				         $post['contact_email']
		     	       );
         				 if($mail2usr == '1'){  				 					  					  
                      $this->_mandamail($app,$contact->email_to,$subject,$mbody);
				         }
				         if($mail2con == '1'){  				 					  					  
                      $this->_mandamail($app,$contact->email_to,$subject,$mbody);
				         }
         			} 
	
		           // Redirect back to the edit screen.
			//$this->setMessage(JText::sprintf('COM_USERS_REGISTRATION_FAILED_XSPAM', $info_detector[0]['text']), 'warning');
			  
			      JError::raiseWarning(401, JText::_('DETECTD_SPAM'));
			      $app->enqueueMessage(JText::_('COM_CONTACT_SUBMSSION_FAILED_XSPAM').$info_detector[0]['text']);
			      $app->Redirect(JRoute::_('index.php?option=com_contact', false));			   				
			      return false;
		        } 
		     } 	    
		// throw new Exception('Some error occurred. Please do not save me');
	}


   }
function _mandamail($app,$anome,$soggetto,$testo){
	 $cfg = JFactory::getConfig();
   $dasito = $cfg->get('mailfrom');
   $daname = $cfg->get('fromname'); 	
// Assemble the email data...the sexy way!
    $dasito='[Detector4Contact] '.$dasito;
    $mail = JFactory::getMailer()->
        setSender(array($danome,$dasito))->
		    addRecipient($anome)->
		    setSubject($soggetto)->
		    setBody($testo);
	//  jexit('s:'.$dasito.' dn:'.$danome.' an:'.$anome.' s:'.$soggetto.' t:'.$testo);
    if (!$mail->Send()) {
	  // TODO: Probably should raise a plugin error but this event is not error checked.
  	//JError::raiseWarning(500, JText::_('ERROR_SENDING_EMAIL').$dasito.' '.$danome.' '.$anome.' '.$soggetto.' '.$testo);
       $app->enqueueMessage($dasito.' '.$danome.' '.$anome.' '.$soggetto.' '.$testo);	
   }		 
 }
}