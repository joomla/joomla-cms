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
class ModulesController extends hikashopController{
	var $toggle = array();
	var $type='modules';

	function __construct(){
		parent::__construct();
		$this->display[]='selectmodules';
		$this->display[]='savemodules';
		$this->display[]='showoptions';
	}

	function selectmodules(){
		JRequest::setVar( 'layout', 'selectmodules'  );
		return parent::display();
	}

	function savemodules(){
		JRequest::setVar( 'layout', 'savemodules'  );
		return parent::display();
	}

	function edit(){
		if(JRequest::getInt('fromjoomla')){
			$app = JFactory::getApplication();
			$context = 'com_modules.edit.module';
			$id = hikashop_getCID('id');
			if($id){
				$values = (array) $app->getUserState($context . '.id');
				$index = array_search((int) $id, $values, true);
				if (is_int($index)){
					unset($values[$index]);
					$app->setUserState($context . '.id', $values);
				}
			}
		}
		return parent::edit();
	}

	function showoptions(){
		$js = '';
		jimport('joomla.html.parameter');
		$params = new hikaParameter();
		$params->set('id',JRequest::getVar('id','product'));
		$params->set('name',JRequest::getVar('id','product'));
		$value = unserialize(JRequest::getVar('value'));
		$value['content_type'] = JRequest::getVar('content_type','product');
		$params->set('value',$value);
		echo hikashop_getLayout('modules','options',$params,$js);
	}
}
