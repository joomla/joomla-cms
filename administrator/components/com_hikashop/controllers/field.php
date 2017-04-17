<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class FieldController extends hikashopController{
	var $pkey = 'field_id';
	var $table = 'field';
	var $groupMap = '';
	var $groupVal = '';
	var $orderingMap ='field_ordering';

	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[]='state';
		$this->modify_views[]='parentfield';
	}

	function store($new=false){
		JRequest::checkToken() || die( 'Invalid Token' );

		$app = JFactory::getApplication();

		$class = hikashop_get('class.field');
		$status = $class->saveForm();
		if($status){
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));

			$translationHelper = hikashop_get('helper.translation');
			if($translationHelper->isMulti(true)){
				$update = hikashop_get('helper.update');
				$update->addJoomfishElements(false);
			}
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() || die( 'Invalid Token' );

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );

		$class = hikashop_get('class.field');
		$num = $class->delete($cids);

		if($num){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		}

		return $this->listing();
	}
	function state(){
		JRequest::setVar( 'layout', 'state' );
		return parent::display();
	}

	function parentfield(){
		$type = JRequest::getVar('type');
		$namekey = JRequest::getVar('namekey');
		$value = JRequest::getString('value');
		if(!empty($namekey) && !empty($type)){
			$class = hikashop_get('class.field');
			$field = $class->getField($namekey,$type);
			echo $class->display($field,$value,'field_options[parent_value]',false,'',true);
		}
		exit;
	}

}
