<?php
/**
 * @version		$Id: controller.php 11/05/2011 18.42
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2011 alikonweb.it All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Config Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @since 1.5
 */
class Aa4jController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'application';

	/**
	 * Method to display the view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getCmd('view', 'application');
		$vFormat	= $document->getType();
		$lName		= JRequest::getCmd('layout', 'default');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat)) {
			//if (($vName != 'close')&&($vName != 'component')) {
			if ($vName != 'close') {
				// Get the model for the view.
				$model = $this->getModel($vName);

				// Access check.
				if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option'))) {
					return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				}

				// Push the model into the view (as default).
				$view->setModel($model, true);
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
	public function copyOverride()
	{
  // echo 'copia</br>';
  
	 $templates='';
	 /*
	 $db = JFactory::getDbo();			
	 $query	= $db->getQuery(true);
	 $query->select('template');			
	 $query->from('#__template_styles');
	 $query->where('client_id=0 AND home = 1');
	 $db->setQuery($query);		
	 $templates = $db->loadResult();
	 */
	 $templates=$this->_getTemplate();
	 if ($templates=='beez_20')	{
	  // echo 'Your defaul template is '.$templates .' override not needed';
	    JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_NOVERRIDE').' '.$templates, 'info');
	 } else {
	 echo'</br> Overriding submit contact layout </br>';
	  
	   $s1 = JPATH_SITE .DS. 'templates'.DS . 'beez_20'. DS.'html'.DS.'com_contact' ;
	   $d1 = JPATH_SITE .DS.'templates'. DS . $templates. DS.'html'.DS.'com_contact' ;
	   $t1 ='contact';
     $this->copyLayout($s1,$d1,$t1); 
	   echo'</br> Overriding submit content layout </br>';
	   $s2 = JPATH_SITE .DS. 'templates'.DS . 'beez_20'. DS.'html'.DS.'com_content' ;
	   $d2 = JPATH_SITE .DS.'templates'. DS . $templates. DS.'html'.DS.'com_content' ;
	   $t2='content';
     $this->copyLayout($s2,$d2,$t2); 
	   echo'</br> Overriding submit weblink layout </br>';
	   $s3 = JPATH_SITE .DS. 'templates'.DS . 'beez_20'. DS.'html'.DS.'com_weblinks' ;
	   $d3 = JPATH_SITE .DS.'templates'. DS . $templates. DS.'html'.DS.'com_weblinks' ;
	   $t3='weblinks';
     $this->copyLayout($s3,$d3,$t3); 
	   echo'</br> Overriding component login layout </br>';
	   $s4 = JPATH_SITE .DS. 'templates'.DS . 'beez_20'. DS.'html'.DS.'com_users' ;
	   $d4 = JPATH_SITE .DS.'templates'. DS . $templates. DS.'html'.DS.'com_users' ;
	   $t4='users';
     $this->copyLayout($s4,$d4,$t4); 
   
   }
     //$this->display();
     	$this->setRedirect(JRoute::_('index.php?option=com_aa4j&view=application', false));
	}
	//
	public function copyLayout($source,$destination,$text)  { 
	   echo 'copy from :' .$source.' </br>to :'.$destination;
	   if (!JFolder::exists($source)) {
	  //  	 JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_FOLDER_FOUND').' '.$source, 'info');
    // } else {
         JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_SOU_FOLDER_NOT_FOUND').' '.$text, 'error');
         return false;
     }

     if (JFolder::exists($destination)) {
        JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_FOLDER_ALREADY_EXISTS').' '.$text, 'warning');
		    $results=JFolder::copy($source, $destination,null,true);
			  if ($results == false) {
           JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_COPY_FOLDER_FAILED').' '.$text, 'error');
           return false;
        }
           // return false;
     } else {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_FOLDER_NOT_EXISTS').' '.$text, 'warning');
            if (JFolder::create($destination)) {
			          $results=JFolder::copy($source, $destination,null,true);
			          if ($results == false) {
                   JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_COPY_FOLDER_FAILED').' '.$text, 'error');
                   return false;
                }
            } else {
			          JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_CREATE_FOLDER_FAILED').' '.$text, 'error');
			          return false;   	
			      }
		 }	
		 JFactory::getApplication()->enqueueMessage(JText::_('COM_AA4J_DEST_CREATE_FOLDER_OK').' '.$text, 'info');
  }		
	//
	function _getTemplate()	{
	 $templates='';
	 $db = JFactory::getDbo();			
	 $query	= $db->getQuery(true);
	 $query->select('template');			
	 $query->from('#__template_styles');
	 $query->where('client_id=0 AND home = 1');
	 $db->setQuery($query);		
	 $templates = $db->loadResult();
	 return $templates;
	}	
	
	
	
	
	
	public function checksum() {
	   #directory for checking integrity
       //$dir = "./";
	   $dir=JPATH_ROOT;
       #file for storing fingerprints, should be writeable in case of fingerprints update
       $file = "./fingerprints";
       #set this value to false if you do not want to update fingerprints
       $can_update = true;
       #set this to value to true if you want to update fingerprints of modified files
       #you should do this only if you had modified files yourself
       $force_update = true;
       #the output parameters
       $output["new"] = true;
       $output["success"] = true;
       $output["failed"] = true;
         
        $hashes = array();
        if (!$this->lookDir($dir))  {        
            JFactory::getApplication()->enqueueMessage( "Could not open the directory ".$dir."\n", 'info');
			/*
           if ($can_update)         
              if (file_put_contents($file, serialize($hashes)))                 
                 JFactory::getApplication()->enqueueMessage( "Fingerprints were updated\n",'info');         
              else                 
                 JFactory::getApplication()->enqueueMessage( "The file cannot be opened for writing! Fingerprints were not updated\n",'info');
           else 
           */		   
       }    
         JFactory::getApplication()->enqueueMessage( "Fingerprints were not updated\n",'info');
	}	
	function lookDir($path) {         
   $handle = @opendir($path);         
   if (!$handle)                 
      return false;         
   while ($item = readdir($handle)) {                 
         if ($item!="." && $item!="..") {                         
            if (is_dir($path."/".$item))                                 
               $this->lookDir($path."/".$item);                         
            else                                 
               $this->checkFile($path."/".$item);                 
        }         
   }         
   closedir($handle);         
   return true;
}
function checkFile($file) {         
/*
  global $hashes;         
  global $output;         
  global $force_update; 
*/  
  if (is_readable($file))      {
      // JFactory::getApplication()->enqueueMessage( 'leggo:'.$file."\t\t\n",'info');                   
       $row=$this->sel_db($file);
	   $hashes_del_file =  md5_file($file);	
	   if (count($row)==0) {              
           JFactory::getApplication()->enqueueMessage( 'non presente nel db'.$file."\t\t\n".$hashes_del_file,'info');                 
		   $this->ins_db($file,$hashes_del_file,'new');		   
	   }else{
	       if ($row['md5'] != $hashes_del_file) {
		     // JFactory::getApplication()->enqueueMessage( 'presente e  OK'.$file."\t\t\n".$hashes_del_file,'info');   
			 //continue;
		   //}else{
		      JFactory::getApplication()->enqueueMessage( 'presente e failed'.$file."\t\t\n",'info');
			  JFactory::getApplication()->enqueueMessage( 'new:'.$hashes_del_file.'old:'.$row['md5']."\t\t\n",'info');
			  JFactory::getApplication()->enqueueMessage( 'old:'.$row['md5']."\t\t\n",'info');
		   }
	   }
  }
	  /*
      if (count($row)==0) {                         
         $hashes[$file] =  md5_file($file);                         
		
        if ($output["new"])                                 
             JFactory::getApplication()->enqueueMessage( 'non presente nel db'.$file."\t\t\n".$hashes[$file],'info');                 
			 $this->ins_db($file,$hashes[$file],'new');
        } elseif ($row['md5'] == md5_file($file)) {                         
                if ($output["success"])                                 
                     JFactory::getApplication()->enqueueMessage( 'presente e  OK'.$file."\t\t\n".$hashes[$file],'info');                 
					$this->upd_db($file,$hashes[$file],'success');
        }                 
        else {                         
              if ($output["failed"])                                 
                 if ($force_update) {                                         
                    $hashes[$file]=md5_file($file);                                         
                     JFactory::getApplication()->enqueueMessage( 'presente e  forced'.$file."\t\t\n".$hashes[$file],'info');           
                    $this->upd_db($file,$hashes[$file],'Update forced');					 
                }                 
                else                                         
                     JFactory::getApplication()->enqueueMessage( 'presente e failed'.$file."\t\t\n".$hashes[$file],'info');                         
					 $this->upd_db($file,$hashes[$file],'Failed');					 
        }
*/		
}
function ins_db ($nomefile,$hash,$status){
 //$status='New';
 $db = JFactory::getDbo();   
 $db->setQuery(
	    'INSERT INTO #__checksum VALUES ( '.$db->Quote($nomefile).','.$db->Quote($status).','.$db->Quote($hash).' )'
				 );
				 
        $db->query();
		
        if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}
}
function upd_db ($nomefile,$hash,$status){
 //$status='New';
 $db = JFactory::getDbo();   
 $db->setQuery(
	    'UPDATE #__checksum SET status = '.$db->Quote($status).' , md5= '.$db->Quote($hash)
				 );
				 
        $db->query();
		
        if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}
}
function sel_db ($nomefile){
 //$status='New';
 $db = JFactory::getDbo();   
 $db->setQuery(
	    'SELECT * FROM #__checksum WHERE file = '.$db->Quote($nomefile)
		);
				 
        $db->query();
		
        if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}
		//$num_rows = $db->getNumRows();
        //print_r($num_rows);

		$row = $db->loadAssoc();
        return $row;

}
}