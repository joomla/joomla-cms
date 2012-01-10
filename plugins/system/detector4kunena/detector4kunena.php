<?php
/**
* detector4kunena system plugin
* Embedd a spam report on Joomla! Kunena component
* @author: Alikon
* @version: 1.7.0
* @release: 18/09/2011 17.03
* @package: Alikonweb.detector 4 Kunena
* @copyright: (C) 2007-2011 Alikonweb.it
* @license: http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*
*
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemDetector4kunena extends JPlugin
{     
	/**
	 * Constructor function
	 *
	 * @param object $subject
	 * @param object $config
	 * @return plgSystemDetector4kunena
	 */   
	var $cfg = null;
    var $mailfrom = null;
    var	$fromname = null;     	
	function plgSystemDetector4kunena( &$subject, $config )
    {
    	$this->cfg = JFactory::getConfig();
     	$this->mailfrom = $this->cfg->get('mailfrom');
     	$this->fromname = $this->cfg->get('fromname'); 	
     	//$this->tbprefix = $this->cfg->dbprefix; 
		parent::__construct( $subject, $config );      
    }      
    
    
    
    function onAfterRoute()
  	//public function onAfterRender()
     { 
     		$app = JFactory::getApplication();
     		if ($app->isAdmin()) {
      		return ;
        }				
     	 $lang = JFactory :: getLanguage();
	$lang->load('plg_system_detector4kunena', JPATH_ADMINISTRATOR);
	//Get the plugin
    $plugin =& JPluginHelper::getPlugin('system', 'detector4kunena');   	
    //Define parameters	
	$mail2mod = $this->params->get( 'sendmail2mod',0 );
	
	$mail2usr = $this->params->get( 'sendmail2usr',0 );
	$blockspammer = $this->params->get( 'blockspammer',0 );
	$hidemessage = $this->params->get( 'hidemessage',0 );
	$checktype = $this->params->get('checktype',0 );
    if ($checktype==0){
	 // check only text akismet & defensio
	 $ptm=1;
	}else{
	  // check all ip and text + akismet & defensio
	 $ptm=2;
	}
  $user = & JFactory::getUser();	
  if ($user->get('guest') == 1) {
  	 $ptm=1;
  }
 // var_dump('typo:'.$ptm);
	$option = JRequest::getCmd('option', '');	
	$task = JRequest::getCmd ( 'task', '' );
			if (($option =='com_weblinks')&&($task =='weblink.save'))  {
		    	$datiform=	JRequest::getVar('jform');
			    //var_dump(	$datiform['description']);
			    JPluginHelper::importPlugin( 'alikonweb' );	      
		      $dispatcher =& JDispatcher::getInstance();	
		      $info_detector = $dispatcher->trigger( 'onDetect',array(3,null,$user->get('email'),$user->get('name'),$datiform['description'],$datiform['url']) ); 			
          //var_dump(	JRequest::getVar('jform'));Jexit();
     	    //return	JError::raiseWarning(392, 'dplug->d:'. $datiform['description'].' - o:'.$option.' - t:'.$task);	
     	    //JError::raiseNotice( 102, ' checked by Detector plugin '.$info_detector[0]['text'] );	    
     	    	if( $info_detector[0]['score'] >=4){
     	        $app->enqueueMessage(JText::_('COM_WEBLINK_SUBMSSION_FAILED_XSPAM').$info_detector[0]['text']);
              $app->Redirect(JRoute::_('index.php?option=com_weblinks', false));	
          }
          JError::raiseNotice( 102, ' checked by Detector plugin '.$info_detector[0]['text'] );	 
	   }
	   		if (($option =='com_content')&&($task =='article.save'))  {
		    	$datiform=	JRequest::getVar('jform');
			    //var_dump(	$datiform['description']);
			    JPluginHelper::importPlugin( 'alikonweb' );	      
		      $dispatcher =& JDispatcher::getInstance();	
		      $info_detector = $dispatcher->trigger( 'onDetect',array(3,null,$user->get('email'),$user->get('name'),$datiform['articletext'],'') ); 			
          //var_dump(	JRequest::getVar('jform'));Jexit();
     	    //return	JError::raiseWarning(392, 'dplug->d:'. $datiform['description'].' - o:'.$option.' - t:'.$task);	
     	    //JError::raiseNotice( 102, ' checked by Detector plugin '.$info_detector[0]['text'] );	 
     	    if( $info_detector[0]['score'] >=4){   
     	       $app->enqueueMessage(JText::_('COM_CONTENT_SUBMSSION_FAILED_XSPAM').$info_detector[0]['text']);
            $app->Redirect(JRoute::_('index.php?option=com_content', false));	
          }
          JError::raiseNotice( 102, ' checked by Detector plugin '.$info_detector[0]['text'] );	 
	   }
	   
		return ;
     } 	
    /**
     * Use this to check the spam
     *
     * @return boolean
     */
	
	//
function onAfterSaveKunenaPost($msgID)
    {
     $app = JFactory::getApplication();      	
     if ($app->isAdmin()) {
      		return ;
     }				
     // Check if I am a Super Admin
      $my = JFactory::getUser();
			$iAmSuperAdmin	= $my->authorise('core.admin');
		  if ($iAmSuperAdmin) {
      		return ;
     }					
			
	 // devo escludere superadmin e moderator
    //Get the DB 
     $db=JFactory::getDBO();
     $query = 'SELECT userid'.
              ' FROM #__kunena_users '.
              ' WHERE moderator =1'.
              ' AND userid = '.$my->get('id');
     $db->setQuery( $query );
		 if ($db->getErrorNum()) {
		     JError::raiseError(292, $db->getErrorMsg() );
     }			              
		$iAmModerator = $db->loadResult();
    if ($iAmModerator) {
      		return ;
     }					
     
     
     
     
     
	 //this event is only triggered by Kunena 1.6.x so don't need to do check version
     /*
      $table16=$this->tbprefix.'fb_version';
       $sql = "show tables LIKE ".$db->Quote($table16); 
	     $db->setQuery($sql) ;
	    	$version16 = $db->loadResult();	
	    	
	    	if (!$db->query()) {
				   return JError::raiseWarning( 591, $db->getErrorMsg() );
		  	}				  
		  
		  	if ($version16==$table16) {
				  // return JError::raiseWarning( 431, 'The Detector4Kunena plugin run only with Kunena 1.6' );
				    return;
		  	}				     		  
	*/	       
	 $lang = JFactory :: getLanguage();
	$lang->load('plg_system_detector4kunena', JPATH_ADMINISTRATOR);
	//Get the plugin
    $plugin =& JPluginHelper::getPlugin('system', 'detector4kunena');
    //load parameters
    $pluginParams = new JParameter( $plugin->params );
   	
    //Define parameters	
	$mail2mod = $pluginParams->get( 'sendmail2mod',0 );
	
	$mail2usr = $pluginParams->get( 'sendmail2usr',0 );
	$blockspammer = $pluginParams->get( 'blockspammer',0 );
	$hidemessage = $pluginParams->get( 'hidemessage',0 );
	$checktype = $pluginParams->get( 'checktype',0 );
    if ($checktype==0){
	 // check only text akismet & defensio
	 $ptm=1;
	}else{
	  // check all ip and text + akismet & defensio
	 $ptm=2;
	}
 // jexit('mm:'.$mail2mod.' ma:'.$mail2adm.' mu:'.$mail2usr.' bs:'.$blockspammer.' hm:'.$hidemessage);
    
	$user = & JFactory::getUser();
	$action = JRequest::getVar('action', '') ;
	$func = JRequest::getVar('func', '') ;
	$option = JRequest::getCmd('option', '');
	$do = JRequest::getVar('do', '') ;	
	$task = JRequest::getCmd ( 'task', '' );
		 	
			
	if (($option == 'com_kunena' && ($func == 'post' && $action== 'post')) && (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector'))){					
			//jexit('kunenaplug->a:'. $action.' - f:'.$func.' - d:'.$do.' - o:'.$option.' - t:'.$task); 	
	   	
	   	$messaggio=JRequest::getVar('message', '', 'post');
		$forumUrl = JURI::base().'index.php?option=com_kunena';	
		JPluginHelper::importPlugin( 'alikonweb' );	      
		$dispatcher =& JDispatcher::getInstance();	
		$info_detector = $dispatcher->trigger( 'onDetect',array($ptm,null,$user->get('email'),$user->get('name'),$messaggio,$forumUrl) ); 			
		//jexit('4opt:'.$option.' act:'.$action.' func:'.$func.' do:'.$do.' tk:'.$task);         	   
		//jexit('kunenaplug:'. $info_detector[0]['score']); 	  	   
		if( $info_detector[0]['score'] >=4){
			 if($hidemessage == '1'){
	           if (isset($msgID)) {
					     $messageId = $msgID ;
				     } else {
					     $sql = 'SELECT MAX(id) FROM #__kunena_messages WHERE userid='.$user->get('id') ;
	    	   		     $db->setQuery($sql) ;
	    	   		     $messageId = $db->loadResult();	
					     if (!$db->query()) {
				 	        return JError::raiseWarning( 551, $db->getErrorMsg() );
		  	       }				  
				     }			
				     $sql = 'SELECT message FROM #__kunena_messages_text WHERE mesid='.$messageId;
				     $db->setQuery($sql);
				     $messageText = $db->loadResult();	
		        if (!$db->query()) {
		        		return JError::raiseWarning( 553, $db->getErrorMsg() );
		         }			
				     $sql = 'UPDATE #__kunena_messages SET hold = 1 WHERE id='.$messageId;
				     $db->setQuery($sql);
				     $db->query();			
			       if (!$db->query()) {
				 	    return JError::raiseWarning( 555, $db->getErrorMsg() );
		  	     }		
        }  
						
        if(($mail2mod == '1')||($mail2usr == '1')){ 
        	// Set the link to confirm the user email.
        	//diiferenziare ilmsg tra usr e mod
    			$uri = JURI::getInstance();
		    	$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			    
          $query = 'SELECT c.name as category, m.name, m.subject ,m.email ,m.catid'.
                                ' FROM #__kunena_messages m, #__kunena_categories c'.
                                ' WHERE m.catid=c.id'.
                                ' AND m.id = '.$messageId;
          $db->setQuery( $query );
          $data = $db->loadObject();
					if (!$db->query()) {
		                   JError::raiseError(392, $db->getErrorMsg() );
          }			              
          $pendingurl = $base.JRoute::_('index.php?option=com_kunena&func=review&action=list&catid='.$data->catid, false);
          $subject='[Kunena forum] '.$data->subject.' ('.$data->category.') is under review';
          $mbody=$messageText.'<br /> <br />  posted by '.$data->name.' <br /> <br /> '.$info_detector[0]['text'];	
          $mbody	= JText::sprintf(
				    'PLG_KUNENA_EMAIL_PENDING_BODY',
				    $messageText,
				    $data->name,
				    $info_detector[0]['text'],
				    $pendingurl 
		     	);
          if($mail2usr == '1'){  				 
					  
					  
                      $this->_mandamail($app,$this->mailfrom,$this->fromname,$user->get('email'),$subject,$mbody);
					}
					if($mail2mod == '1'){  
					  $query = 'SELECT name, email, sendEmail'.
                                ' FROM #__kunena_users , #__users'.
                                ' WHERE moderator =1'.
                                ' AND userid = id';
             $db->setQuery( $query );
					   if (!$db->query()) {
		                   JError::raiseError(392, $db->getErrorMsg() );
             }			              
		         $mods = $db->loadObjectList();
		         
		         
					   //$subject='Moderator take a look at pending post msgid#'.$messageId;
             //$mbody=$messageText.'\n\n'.$info_detector[0]['text'];	
             //$mbody= $mbody.'<br /> <br /> '.$pendingurl;
             foreach ( $mods as $mod ) { 					   
					     $this->_mandamail($app,$this->mailfrom,$this->fromname,$mod->email,$subject,$mbody);
					   }
					}
				 }					
    			
                 // block user
				  if($blockspammer == '1'){ 
				 	         $user->block = 1;
	        			 	 $user->save();   
				 	         //global $mainframe ;
				 	         $app->logout() ; 
				  }
				  //
				  //$app->enqueueMessage(JText::_('COM_KUNENA_SUBMSSION_PENDING').$info_detector[0]['text']);
				  $app->enqueueMessage(JText::_('PLG_KUNENA_SUBMSSION_PENDING'));
				  JError::raiseWarning( 551, $info_detector[0]['text'] );
				  return;
			    }			  		
		 JError::raiseNotice( 102, ' checked by Detector plugin '.$info_detector[0]['text'] );	      			
	 }	

return;        	
}	
function _mandamail($app,$danome,$dasito,$anome,$soggetto,$testo){
// Assemble the email data...the sexy way!
$dasito='[Kunena Forum] '.$dasito;
$mail = JFactory::getMailer()->
        setSender(array($danome,$dasito))->
		    addRecipient($anome)->
		    setSubject($soggetto)->
		    setBody($testo);
		// jexit('s:'.$dasito.' dn:'.$danome.' an:'.$anome.' s:'.$soggetto.' t:'.$testo);
 if (!$mail->Send()) {
	// 
	//JError::raiseWarning(500, JText::_('ERROR_SENDING_EMAIL').$dasito.' '.$danome.' '.$anome.' '.$soggetto.' '.$testo);
    $app->enqueueMessage($dasito.' '.$danome.' '.$anome.' '.$soggetto.' '.$testo);	
 }		 
}
             
}