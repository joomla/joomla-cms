<?php
/**
 * @version		1.7.3 plugins/system/aa4j.php
 * @package	Joomla!applications
 * @subpackage	plg_system_aa4j
 * @since		1.7.3
 *
 * @author	
 * @link		
 * @copyright	Copyright (C) 2010-2011 . All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * AA4J  is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

class plgSystemAA4J extends JPlugin
{
	var $params = null;
	/**
	 * CONSTRUCTOR
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function __construct(&$subject, $params)
	{
		$this->params = $params;
		parent::__construct($subject, $params);
		JPlugin::loadLanguage('com_aa4j', JPATH_ADMINISTRATOR);
	}
  function onAfterRoute()
  	//public function onAfterRender()
     { 
     		$app = JFactory::getApplication();
     		$db = JFactory::getDbo();		 
     		if($app->getName() != 'administrator') {
			   return true;
			  } 
			  $option = JRequest::getVar('option');		 
		    $task = JRequest::getVar('task');		 
		   if (($option == 'com_users') && ($task=='detecttask')){
		   	  $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
   // $row =& $this->getTable();
    if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){					
			   JPluginHelper::importPlugin( 'alikonweb' );
			   $dispatcher2 =& JDispatcher::getInstance();
          foreach($cids as $cid) {
            $db->setQuery( 
	           'select * FROM #__userextras e , #__users u WHERE e.id='.(int) $cid
	          .' and u.id=e.id'
	                	 );
	                	 
	           	$user = $db->loadObject();
	     	       if (!$db->query()) {
		                   JError::raiseError(392, $db->getErrorMsg() );
		                    return false;    
               }			              
	    	 //jexit(var_dump($user));
		        	 $info_detector = $dispatcher2->trigger( 'onDetect',array(0,$user->ip,$user->email,$user->username,' ',' ') );
		     	    	if( $info_detector[0]['score'] >=4){		     	 				     	 		
                  $db->setQuery( 
	                   'UPDATE #__users SET block =   1 WHERE id='.(int) $cid
	                	 );
	                $db->query();
		
                 if ($db->getErrorNum()) {
			              JError::raiseNotice(500, $db->getErrorMsg());
			           return false;           
	           		 }  	 
		     	 	 }     	
		     	 
           $app->enqueueMessage(JText::sprintf('COM_AA4J_DETECT_RESULT',$info_detector[0]['text'], $user->id,$user->username,$user->ip));
         }     
    }
		    
		    
		    $app->Redirect(JRoute::_('index.php?option=com_users', false));	
		 }
		  return true;
		}
	/**
	 * Method is called by index.php and administrator/index.php
	 *
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		$app =& JFactory::getApplication();
		if($app->getName() != 'administrator') {
			return true;
		}
		

/*
		$enabled = JComponentHelper::getComponent('com_aa4j', true);
		if (!$enabled->enabled) 
			return true; 

		if (!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_aa4j'.DS.'buttons'.DS.'standard2.php'))
			return true; 
		if (!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_aa4j'.DS.'buttons'.DS.'send.php'))
			return true; 
	*/					
		$option = JRequest::getVar('option');
		$section = JRequest::getVar('section');
		$task = JRequest::getVar('task');
		$layout = JRequest::getVar('layout');
		$toolbar =& JToolBar::getInstance('toolbar');
		//$toolbar->addButtonPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_aa4j'.DS.'buttons');
		//$toolbar->loadButtonType('Standard2', true);
		$doc =& JFactory::getDocument();
		$icon_32_send = " .icon-32-j2xml_send {background:url(components/com_aa4j/assets/images/toolbar/icon-32-send.png) no-repeat; }"; 
		$doc->addStyleDeclaration($icon_32_send);
		$icon_32_export = " .icon-32-j2xml_export {background:url(components/com_aa4j/assets/images/toolbar/icon-32-export.png) no-repeat; }"; 
		$doc->addStyleDeclaration($icon_32_export);
    if (($option == 'com_users') && ($layout == ''))
		{
			//$toolbar->prependButton('Send', 'j2xml_send', 'COM_J2XML_BUTTON_SEND', 'j2xml.users.send');
			//$toolbar->prependButton('Standard', 'j2xml_export', 'COM_J2XML_BUTTON_EXPORT', 'j2xml.users.export');
			$url = JRoute::_('index.php?option=com_aa4j&task=detecttask');
			$toolbar->prependButton( 'standard', 'refresh', 'Detect', 'detecttask', true );
			
			JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_VIEW_GEO_TITLE'),
			'index.php?option=com_aa4j&view=component&component=all',
			'ausers'
		);
    	JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_VIEW_USERS_TITLE'),
			'index.php?option=com_aa4j&view=users',
			'ausers'
		);			
		}			
		if (($option == 'com_users') && ($layout == 'edit'))
		{
			$id = JRequest::getVar('id');
		//	$toolbar->prependButton('Popup', 'help', 'Info', 'index.php?option=com_aa4j&view=component&component='.$id);
			$toolbar->prependButton('Popup', 'help', 'Info', 'index.php?option=com_aa4j&view=component&component='.$id,false);
			$menu = JToolBar::getInstance('submenu');
		  $menu->appendButton('COM_AA4J_VIEW_GEO_TITLE', 'index.php?option=com_aa4j&view=component', true);
				
			
    }
		return true;
	}
}
?>