<?php
/**
 * @version		$Id: controller.php 01 2012-11-14 11:37:09Z maverick $
 * @package		CoreJoomla.CjBlog
 * @subpackage	Components.admin
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

class CjLibController extends JControllerLegacy {
	
    function __construct() {
    	
        parent::__construct();
        
        $this->registerDefaultTask('show_cpanel');
		$this->registerTask('save','save_config');
		$this->registerTask('queue', 'get_email_queue');
		$this->registerTask('countries', 'get_countries_listing');
		$this->registerTask('delete_queue','delete_queue');
		$this->registerTask('process_queue','process_queue');
		$this->registerTask('add_language', 'add_country_language');
		$this->registerTask('save_country_name', 'save_country_name');
    }
    
	public function show_cpanel(){
    	
		$view = $this->getView('default', 'html');
		$model = $this->getModel('config');
		$view->setModel($model, true);
		$view->display();
    }
    
    public function save_config(){

    	$model = $this->getModel('config');
    	
    	if(!$model->save()){
    			
    		$this->setRedirect('index.php?option=com_cjlib', JText::_('MSG_ERROR_PROCESSING'));
    	}else{
    			
    		$this->setRedirect('index.php?option=com_cjlib', JText::_('MSG_CONFIG_SAVED'));
    	}
    }
    
    public function get_email_queue(){
    	
    	$view = $this->getView('queue', 'html');
    	$model = $this->getModel('queue');
    	$view->setModel($model, true);
    	$view->display();
    }
    
    public function get_countries_listing(){
    	
    	$view = $this->getView('countries', 'html');
    	$model = $this->getModel('countries');
    	$view->setModel($model, true);
    	$view->display();
    }
    
    public function add_country_language(){
    	
    	$app = JFactory::getApplication();
    	$model = $this->getModel('countries');
    	$language = $app->input->getCmd('filter_language');
    	
    	if($model->add_language($language)){
    		
    		$this->setRedirect('index.php?option=com_cjlib&task=countries', JText::_('COM_CJLIB_MSG_COMPLETED'));
    	} else {
    		
    		$this->setRedirect('index.php?option=com_cjlib&task=countries', JText::_('COM_CJLIB_MSG_COMPLETED'));
    	}
    }

	function delete_queue(){
	
		$app = JFactory::getApplication();
		$ids = $app->input->getArray(array('cid'=>'array'));
	
		JArrayHelper::toInteger($ids['cid']);
	
		if(empty($ids['cid'])){
				
			$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('COM_CJLIB_MSG_NO_ITEM_SELECTED'));
		}else{
				
			$model = $this->getModel('queue');
				
			if($model->delete_queue($ids['cid'])){
					
				$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('COM_CJLIB_MSG_COMPLETED'));
			} else {
	
				$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('MSG_ERROR_PROCESSING'));
			}
		}
	}

	function process_queue(){
	
		$app = JFactory::getApplication();
		$ids = $app->input->getArray(array('cid'=>'array'));
	
		JArrayHelper::toInteger($ids['cid']);
	
		if(empty($ids['cid'])){
				
			$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('COM_CJLIB_MSG_NO_ITEM_SELECTED'));
		}else{
				
			$model = $this->getModel('queue');
				
			if($model->process_queue($ids['cid'])){
					
				$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('COM_CJLIB_MSG_COMPLETED'));
			} else {
	
				$this->setRedirect('index.php?option=com_cjlib&task=queue', JText::_('MSG_ERROR_PROCESSING'));
			}
		}
	}
    
    public function save_country_name(){
    	
        $user = JFactory::getUser();
        
        if(!$user->authorise('core.edit', 'com_cjlib')) {
        	
            echo json_encode(array('error'=>JText::_('COM_CJLIB_MSG_NOT_AUTHORIZED')));
        }else {
        	
        	$app = JFactory::getApplication();
            $model = $this->getModel('countries');
            
            $id = $app->input->getInt('id', 0);
            $name = $app->input->getString('country_name', '');
            
            if($id && !empty($name) && $model->save_country_name($id, $name)){

            	echo json_encode(array('data'=>1));
            }else{
	            	
            	echo json_encode(array('error'=>JText::_('MSG_ERROR_PROCESSING')));
        	}
        }
        
        jexit();
    }
}
?>