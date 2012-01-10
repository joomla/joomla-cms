<?php
/**
 * @version		$Id: fieldattachunidad.php 15 2011-09-02 18:37:15Z cristian $
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
class fieldsattachControllerfieldsattachunidad extends JControllerForm
{

            /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'fieldsattachunidad', $prefix = 'fieldsattachModel')
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
                 $this->setRedirect('index.php?option=com_fieldsattach&view=fieldsattachunidades', $msg);

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
                 $model = $this->getModel();
                 if(!$model->store())
                 {
                     return JError::raiseWarning( 500, $model->getError() );
                 }
                 if($task == "apply"){
                    //$msg ="";
                    //$link= JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachunidad&layout=edit&id='.$data['id']) ;
                    $this->setRedirect('index.php?option=com_fieldsattach&view=fieldsattachunidad&layout=edit&id='.$model->id, JText::_("COM_SAVE_SUCCESS"));
                 }

                 if($task == "save"){
                    //$msg ="";
                    //$link= JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachunidades') ;
                    $this->setRedirect('index.php?option=com_fieldsattach&view=fieldsattachunidades', JText::_("COM_SAVE_SUCCESS"));
                 }

                  if($task == "save2new"){
                    //$msg ="";
                    //$link= JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachunidad&layout=edit') ;
                    $this->setRedirect('index.php?option=com_fieldsattach&view=fieldsattachunidad&layout=edit', JText::_("COM_SAVE_SUCCESS"));
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
                $this->setRedirect('index.php?option=com_fieldsattach&view=fieldsattachunidades', JText::_("COM_DELETE_SUCCESS"));
         }

         
        
         
}
