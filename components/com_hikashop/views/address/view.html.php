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
class addressViewAddress extends HikaShopView {

	function display($tpl = null, $params = null) {

		if(empty($params))
			$params = new HikaParameter('');
		$this->assignRef('params',$params);

		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$user_id = hikashop_loadUser();
		$addresses = array();
		$fields = null;
		if($user_id){
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($user_id);
			if(!empty($addresses)){
				$addressClass->loadZone($addresses);
				$fields =& $addressClass->fields;
			}
		}
		$this->assignRef('user_id',$user_id);
		$this->assignRef('fields',$fields);
		$this->assignRef('addresses',$addresses);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);

		hikashop_setPageTitle('ADDRESSES');
	}

	function show() {
		$app = JFactory::getApplication();
		$this->assignRef('app', $app);
		$config = hikashop_config();
		$this->assignRef('config', $config);

		if(!empty($this->params->type))
			$type = $this->params->type;
		else {
			$type = JRequest::getCmd('address_type', '');
			if(empty($type))
				$type = JRequest::getCmd('subtask', 'billing');
			if(substr($type, -8) == '_address')
				$type = substr($type, 0, -8);
		}

		if(!empty($this->params->address_id))
			$address_id = $this->params->address_id;
		else
			$address_id = hikashop_getCID();

		if(!empty($this->params->fieldset_id))
			$fieldset_id = $this->params->fieldset_id;
		else
			$fieldset_id = JRequest::getVar('fid', '');

		$this->assignRef('type', $type);
		$this->assignRef('address_id', $address_id);
		$this->assignRef('fieldset_id', $fieldset_id);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$edit = false;
		if(JRequest::getVar('edition', false) === true) {
			$edit = true;
		}
		if(isset($this->params->edit))
			$edit = $this->params->edit;
		$this->assignRef('edit', $edit);

		$user_id = hikashop_loadUser();
		$address = new stdClass();
		if(!empty($address_id)) {
			$addressClass = hikashop_get('class.address');
			$address = $addressClass->get($address_id);
			if($address->address_user_id != $user_id) {
				$address = new stdClass();
				$address_id = 0;
			}
			if(!$edit) {
				$addresses = array(&$address);
				$addressClass->loadZone($addresses);
			}
		} else {
			$userCMS = JFactory::getUser();
			if(!$userCMS->guest) {
				$name = $userCMS->get('name');
				$pos = strpos($name, ' ');
				if($pos !== false) {
					$address->address_firstname = substr($name, 0, $pos);
					$name = substr($name, $pos + 1);
				}
				$address->address_lastname = $name;
			}
		}
		$this->assignRef('address', $address);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);
		$extraFields = array();
		$extraFields['address'] = $fieldsClass->getFields('frontcomp' ,$address, 'address', 'checkout&task=state'.$url_itemid);
		$this->assignRef('fields', $extraFields['address']);

		$init_js = '';
		$this->assignRef('init_js', $init_js);

		static $jsInit = array();
		if(empty($jsInit[$type])) {
			$null = array();
			$fieldsClass->addJS($null,$null,$null);
			if($edit) {
				$parents = $fieldsClass->getParents($extraFields['address']);
				if(!empty($parents)) {
					$p = reset($parents);
					$p->type = $type.'_address';
				} else {
					$p = new stdClass();
					$p->type = $type.'_address';
					$parents = array($p);
				}
				$init_js = $fieldsClass->initJSToggle($parents, $address, 0);
			} else {
				foreach($extraFields['address'] as &$p) {
					$p->field_table = $type.'_address';
				}
				unset($p);
				$fieldsClass->jsToggle($extraFields['address'],$address,0);
				$requiredFields = array();
				$validMessages = array();
				$values = array('address'=>$address);
				$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
				$fieldsClass->addJS($requiredFields,$validMessages,array('address'));
			}
		}
		$jsInit[$type] = true;
	}

	function form(){
		$user_id = hikashop_loadUser();
		$this->assignRef('user_id',$user_id);
		$address_id = hikashop_getCID('address_id');
		$address = JRequest::getVar('fail');
		if(empty($address)){
			$address = new stdClass();
			if(!empty($address_id)){
				$class=hikashop_get('class.address');
				$address = $class->get($address_id);
				if($address->address_user_id!=$user_id){
					$address = new stdClass();
					$address_id = 0;
				}
			}else{
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest){
					$name = $userCMS->get('name');
					$pos = strpos($name,' ');
					if($pos!==false){
						$address->address_firstname = substr($name,0,$pos);
						$name = substr($name,$pos+1);
					}
					$address->address_lastname = $name;
				}
			}
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address','checkout&task=state'.$url_itemid);

		$this->assignRef('extraFields',$extraFields);
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['address'],$address,0);

		$this->assignRef('address',$address);
		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$requiredFields = array();
		$validMessages = array();
		$values = array('address'=>$address);
		$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
		$fieldsClass->addJS($requiredFields,$validMessages,array('address'));
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
	}

}
