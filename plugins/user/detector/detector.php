<?php
/**
 * @version		$Id: detector.php 11/05/2011 20.49
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2011 Alikonweb.it All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since		1.5
 */
class plgUserDetector extends JPlugin
{
	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param	array		$user	Holds the old user data.
	 * @param	boolean		$isnew	True if a new user is stored.
	 * @param	array		$new	Holds the new user data.
	 *
	 * @return	void
	 * @since	1.6
	 * @throws	Exception on error.
	 */
	public function onUserBeforeSave($user, $isnew, $new)
	{
		$app = JFactory::getApplication();
    $lang = JFactory :: getLanguage();
		$lang->load('plg_user_detector', JPATH_ADMINISTRATOR);

		if ($isnew) {
			// Call a function in the external app to create the user
			// ThirdPartyApp::createUser($user['id'], $args);
			if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){					
					   JPluginHelper::importPlugin( 'alikonweb' );
					   $dispatcher2 =& JDispatcher::getInstance();
					   $info_detector = $dispatcher2->trigger( 'onDetect',array(0,null,$user['email'],$user['username'],' ',' ') );
					   if ($info_detector[0]['score'] >4 ){						    
						    //return JError::raiseError(401, JText::_('DENIED_FOR_SPAM').$info_detector[0]['text'].$info_detector[0]['score']);
						     JError::raiseWarning(401, JText::_('DETECTD_SPAM'));
			      $app->enqueueMessage(JText::_('COM_USERS_SUBMSSION_FAILED_XSPAM').$info_detector[0]['text']);
			      $app->Redirect(JRoute::_('index.php?option=com_users', false));
						return false;
				    } 						
				  }				

		}
	}
	
	/**
	 * Example store user method
	 *
	 * Method is called after user data is stored in the database
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was succesfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 * @throws	Exception on error.
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$app = JFactory::getApplication();
       
		// convert the user parameters passed to the event
		// to a format the external application

		$args = array();
		$args['username']	= $user['username'];
		$args['email']		= $user['email'];
		$args['fullname']	= $user['name'];
		$args['password']	= $user['password'];

		if ($isnew) {
		    if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){					
				JPluginHelper::importPlugin( 'alikonweb' );
				$dispatcher2 =& JDispatcher::getInstance();
				$info_detector = $dispatcher2->trigger( 'onDetect',array(5,null,$user['email'],$user['username'],' ',' ') );
			// Call a function in the external app to create the user
			// ThirdPartyApp::createUser($user['id'], $args);
			   $this->_insertextras($user,$info_detector);
			}    
		} else {
			// Call a function in the external app to update the user
			// ThirdPartyApp::updateUser($user['id'], $args);
		}
	}
	
	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Extra options.
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options)
	{
		// Initialise variables.
		$success = true;
     $instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($user['username'])))  {
			$instance->load($id);
			$this->_checkextras($instance->get('id'));	  
		}

		return $success;
	}
	/**
	 * Example store user method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data.
	 * @param	boolean		$succes	True if user was succesfully stored in the database.
	 * @param	string		$msg	Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$app = JFactory::getApplication();

		// only the $user['id'] exists and carries valid information

		// Call a function in the external app to delete the user
		// ThirdPartyApp::deleteUser($user['id']);
		$this->_deleteextras($user['id']);
	}

	  function _checkextras($userid){
	     $db = JFactory::getDbo();
	     $db->setQuery( 
	          'select * FROM #__userextras WHERE id='.(int) $userid
	                	 );
	     	$id = $db->loadResult();
		
       if ($db->getErrorNum()) {
			   JError::raiseNotice(500, $db->getErrorMsg());
			   return false;           
			 }  	 
			 if ($id) {
			 	$this->_updateextras($userid);
			 }else{	
	  	    if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){					
				    JPluginHelper::importPlugin( 'alikonweb' );
			    	$dispatcher2 =& JDispatcher::getInstance();
				    $info_detector = $dispatcher2->trigger( 'onDetect',array(5,null,'email','username',' ',' ') );
			      // Call a function in the external app to create the user
			      $user['id']=$userid;
			      $this->_insertextras($user,$info_detector);
			    }    	  	
			 }	
  	}
  	function _updateextras($userid){
	     $db = JFactory::getDbo();
	     $db->setQuery( 
	          'UPDATE #__userextras SET nvisit = nvisit +  1 WHERE id='.(int) $userid
	                	 );
	     $db->query();
		
       if ($db->getErrorNum()) {
			   JError::raiseNotice(500, $db->getErrorMsg());
			   return false;           
			 }  	 
  	}
		function _deleteextras($userid){
	     $db = JFactory::getDbo();
	     $db->setQuery( 
	          'DELETE FROM #__userextras WHERE id='.(int) $userid
	                	 );
	     $db->query();
		
       if ($db->getErrorNum()) {
			   JError::raiseNotice(500, $db->getErrorMsg());
			   return false;           
			 }  	 
  	}
	
	function _insertextras($user,&$info_detector){
	    $db = JFactory::getDbo();   
		
	    $extend=60; 
		$spazio="";
		$jnow		=& JFactory::getDate();
		$now		= substr($jnow->toMySQL(),0,10);				       
		$now=$jnow->toMySQL();
		$db->setQuery(
	    'INSERT INTO #__userextras VALUES ( '.(int) $user['id'].' , 1 , 0, 0, ADDDATE("'.$now.'", '.$extend.'),'.$db->Quote($info_detector[0]['ip']).
			     ','.$db->Quote($info_detector[0]['city']).','.$db->Quote($info_detector[0]['country_code']).','.$db->Quote($info_detector[0]['country_name']).','.$db->Quote($info_detector[0]['latitude']). 
			     ','.$db->Quote($info_detector[0]['longitude']).','.$db->Quote($spazio).','.$db->Quote($spazio).   ','.$db->Quote($spazio).',0 )'
				 );
				 
        $db->query();
		
        if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}
		/*
        $que= 'INSERT INTO #__userextras VALUES ( '.(int) $user['id'].' , 0 , 0, 0, ADDDATE("'.$now.'", '.$extend.'),'.$db->Quote($info_detector[1]['ip']).
			     ','.$db->Quote($info_detector[1]['city']).','.$db->Quote($info_detector[1]['country_code']).','.$db->Quote($info_detector[1]['country_name']).','.$db->Quote($info_detector[1]['latitude']). 
			     ','.$db->Quote($info_detector[1]['longitude']).','.$db->Quote($spazio).','.$db->Quote($spazio).   ','.$db->Quote($spazio).',0 )';		
        
		echo 'ip:'. 	$info_detector[1]['ip'].'<br><br>'			 ;
		jexit('ip:'.var_dump($info_detector).' - '.$que);
		*/
	}	

}