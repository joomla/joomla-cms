<?php
/**
 * @version		$Id: fieldattachimage.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * fieldsattach Controller
 */
class fieldsattachControllerfieldsattachimage extends JControllerForm
{
        
            /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'fieldsattachimage', $prefix = 'fieldsattachModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
        /**
	 * cancel function
	 *
	 * @return      nothing
	 */
        public function cancel( )
	{
		 //echo "cancel";
                 $msg ="";
                 $link=  'index.php?option=com_fieldsattach&view=fieldsattachimages&tmpl=component'  ;
                 $this->setRedirect($link, $msg);

	}
        /**
	 * save function
	 *
	 * @return      nothing
	 */
         public function save( )
	{
                 //$ids	= JRequest::getVar('cid', array(), '', 'array');
                  // Get posted form variables.
                 $task           = $this->getTask();
                 $data           = JRequest::getVar('jform', array(), 'post', 'array');
                  //echo "ECHO:: ".$data['id'];
                 //  echo " task:: ".$task;

                 $session =& JFactory::getSession();
                 $articleid =  $session->get('articleid');
                 $fieldsattachid =  $session->get('fieldsattachid');


                 $model = $this->getModel();
                 if(!$model->store())
                 {
                     return JError::raiseWarning( 500, $model->getError() );
                 }
                 if($task == "apply"){
                    $msg ="";
                    $lastid = $data['id'];
                    if(empty($lastid)) $lastid = $model->lastid;
                    $link= 'index.php?option=com_fieldsattach&view=fieldsattachimage&layout=edit&id='.$lastid.'&tmpl=component' ;
                     $this->setRedirect($link, $msg);
                 }

                 if($task == "save"){
                    $msg ="";
                    //$link= 'index.php?option=com_fieldsattach&view=fieldsattachimages&tmpl=component&articleid='.$articleid.'&fieldsattachid='.$fieldsattachid.'&reset=1'  ;
                    $link= 'index.php?option=com_fieldsattach&view=fieldsattachimages&tmpl=component'  ;
                   $this->setRedirect($link, $msg);
                 } 
 

	}
        /**
	 * delete function
	 *
	 * @return      nothing
	 */
         public function delete( )
	{
                //echo "delete";
               
                $model = $this->getModel();
                $model->delete();
                $link= JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachimages') ;
                $this->setRedirect($link, $msg);
         }

         
        
         
}
