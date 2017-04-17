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
class hikashopAddressClass extends hikashopClass{
	var $tables = array('address');
	var $pkeys = array('address_id');

	function getByUser($user_id){
		$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_default DESC, a.address_id DESC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList('address_id');
	}

	function get($element, $default = null) {
		static $cachedElements = array();
		if($element == 'reset_cache') {
			$cachedElements = array();
			return true;
		}

		if((int)$element == 0)
			return true;

		if(!isset($cachedElements[$element]))
			$cachedElements[$element] = parent::get($element, $default);

		if(!is_object($cachedElements[$element]))
			return $cachedElements[$element];

		$copy = new stdClass();
		foreach(get_object_vars($cachedElements[$element]) as $key => $val) {
			$copy->$key = $val;
		}

		return $copy;
	}

	function loadZone(&$addresses, $type = 'name', $display = 'frontcomp') {
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData($display, 'address');
		$this->fields =& $fields;

		if(empty($fields))
			return;

		$namekeys = array();
		foreach($fields as $field) {
			if($field->field_type == 'zone') {
				$namekeys[$field->field_namekey] = $field->field_namekey;
			}
		}

		if(empty($namekeys))
			return;

		$zones = array();
		$quoted_zones = array();
		foreach($addresses as $address) {
			foreach($namekeys as $namekey) {
				if(!empty($address->$namekey)) {
					$zones[$address->$namekey] = $address->$namekey;
					$quoted_zones[$address->$namekey] = $this->database->Quote($address->$namekey);
				}
			}
		}

		if(empty($zones))
			return;

		if(!in_array($type, array('name', 'object'))) {
			$this->_getParents($zones,$addresses,$namekeys);

			return;
		}

		$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$quoted_zones).');';
		$this->database->setQuery($query);
		$zones = $this->database->loadObjectList('zone_namekey');
		if(empty($zones))
			return;

		foreach($addresses as $k => $address) {
			foreach($namekeys as $namekey) {
				if(empty($address->$namekey) || empty($zones[$address->$namekey]))
					continue;

				$addresses[$k]->{$namekey.'_orig'} = $addresses[$k]->$namekey;
				if($type == 'name') {
					$addresses[$k]->{$namekey.'_code_2'} = $zones[$address->$namekey]->zone_code_2;
					$addresses[$k]->{$namekey.'_code_3'} = $zones[$address->$namekey]->zone_code_3;
					$addresses[$k]->{$namekey.'_name'} = $zones[$address->$namekey]->zone_name;

					if(is_numeric($zones[$address->$namekey]->zone_name_english)) {
						$addresses[$k]->$namekey = $zones[$address->$namekey]->zone_name;
					} else {
						$addresses[$k]->$namekey = $zones[$address->$namekey]->zone_name_english;
					}

					$addresses[$k]->{$namekey.'_name_english'} = $addresses[$k]->$namekey;
				} else {
					$addresses[$k]->$namekey = $zones[$address->$namekey];
				}
			}
		}
	}

	function displayAddress(&$fields,&$address,$view='address',$text=false){
		$params = new HikaParameter('');
		if(HIKASHOP_J25) $params->set('address',$address);
		$js = '';
		$fieldsClass = hikashop_get('class.field');
		$html = ''.hikashop_getLayout($view,'address_template',$params,$js);
		if(!empty($fields)){
			foreach($fields as $field){
				$fieldname = $field->field_namekey;
				if(!empty($address->$fieldname)) $html = str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$address->$fieldname),$html);
			}
		}
		$html = str_replace("\n\n","\n",trim(str_replace("\r\n","\n", trim(preg_replace('#{(?:(?!}).)*}#i','',$html))),"\n"));
		if(!$text){
			$html = str_replace("\n","<br/>\n",$html);
		}
		return $html;
	}

	function loadUserAddresses($user_id){
		static $addresses = array();
		if(!isset($addresses[$user_id])){
			$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_default DESC, a.address_id DESC';
			$this->database->setQuery($query);
			$addresses[$user_id] = $this->database->loadObjectList('address_id');
		}
		return $addresses[$user_id];
	}

	function _getParents(&$zones,&$addresses,&$fields){
		$namekeys = array();
		foreach($zones as $zone){
			$namekeys[]=$this->database->Quote($zone);
		}
		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$namekeys).');';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList();
		if(!empty($parents)){
			$childs = array();
			foreach($parents as $parent){
				foreach($addresses as $k => $address){
					foreach($fields as $field){
						if(!is_array($addresses[$k]->$field)){
							$addresses[$k]->$field = array($addresses[$k]->$field);
						}
						foreach($addresses[$k]->$field as $value){
							if($value == $parent->zone_child_namekey && !in_array($parent->zone_parent_namekey,$addresses[$k]->$field)){
								$values =& $addresses[$k]->$field;
								$values[]=$parent->zone_parent_namekey;
								$childs[$parent->zone_parent_namekey]=$parent->zone_parent_namekey;
							}
						}
					}
				}
			}
			if(!empty($childs)){
				$this->_getParents($childs,$addresses,$fields);
			}
		}

	}

	function save(&$addressData,$order_id=0,$type='shipping'){
		$new = true;
		if(!empty($addressData->address_id)){
			$new = false;
			$oldData = $this->get($addressData->address_id);

			if(!empty($addressData->address_vat) && $oldData->address_vat != $addressData->address_vat){
				if(!$this->_checkVat($addressData)){
					return false;
				}
			}

			$app = JFactory::getApplication();
			if(!$app->isAdmin()){
				$user_id = hikashop_loadUser();
				if($user_id!=$oldData->address_user_id || !$oldData->address_published){
					unset($addressData->address_id);
					$new = true;
				}
			}

			$orderClass = hikashop_get('class.order');

			if(!empty($addressData->address_id) && ($oldData->address_published!=0||$order_id) && $orderClass->addressUsed($addressData->address_id,$order_id,$type)){
				unset($addressData->address_id);
				$new = true;
				$oldData->address_published=0;
				parent::save($oldData);
				$this->get('reset_cache');
			}
		}elseif(!empty($addressData->address_vat)){
			if(!$this->_checkVat($addressData)){
				return false;
			}
		}

		if(empty($addressData->address_id) && empty($addressData->address_user_id) && empty($order_id))
			return false;

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if($new){
			if(!empty($addressData->address_user_id)) {
				$query = 'SELECT count(*) as cpt FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$addressData->address_user_id.' AND address_published = 1 AND address_default = 1';
				$this->database->setQuery($query);
				$ret = $this->database->loadObject();
				if($ret->cpt == 0) {
					$addressData->address_default = 1;
				}
			}

			$dispatcher->trigger( 'onBeforeAddressCreate', array( & $addressData, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeAddressUpdate', array( & $addressData, & $do) );
		}
		if(!$do){
			return false;
		}
		$status = parent::save($addressData);
		if(!$status){
			return false;
		}
		$this->get('reset_cache');
		if(!empty($addressData->address_default) && !empty($oldData)) {
			$query = 'UPDATE '.hikashop_table('address').' SET address_default=0 WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_id != '.(int)$status;
			$this->database->setQuery($query);
			$this->database->query();
		}
		if($new){
			$dispatcher->trigger( 'onAfterAddressCreate', array( & $addressData ) );
		}else{
			$dispatcher->trigger( 'onAfterAddressUpdate', array( & $addressData ) );
		}
		return $status;
	}

	function frontSaveForm($task = '') {
		$fieldsClass = hikashop_get('class.field');
		$data = JRequest::getVar('data', array(), '', 'array');
		$ret = array();

		$user_id = hikashop_loadUser(false);

		$currentTask = 'billing_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask])) {
			$oldAddress = null;
			$billing_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($billing_address)) {
				$billing_address->address_user_id = $user_id;
				$id = (int)@$billing_address->address_id;

				$result = $this->save($billing_address, 0, 'billing');
				if($result) {
					$r = new stdClass();
					$r->id = $result;
					$r->previous_id = $id;
					$ret[$currentTask] = $r;
				}else{
					return false;
				}
			}
		}

		$same_address = JRequest::getString('same_address');
		$currentTask = 'shipping_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && $same_address != 'yes') {
			$oldAddress = null;
			$shipping_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($shipping_address)) {
				$shipping_address->address_user_id = $user_id;
				$id = (int)@$shipping_address->address_id;

				$result = $this->save($shipping_address, 0, 'shipping');
				if($result) {
					$r = new stdClass();
					$r->id = $result;
					$r->previous_id = $id;
					$ret[$currentTask] = $r;
				}else{
					return false;
				}
			}
		}

		return $ret;
	}

	function delete(&$elements,$order=false){
		$elements = (int)$elements;

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do=true;
		$dispatcher->trigger( 'onBeforeAddressDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}
		$orderClass = hikashop_get('class.order');
		$status = true;
		if($orderClass->addressUsed($elements)){
			if(!$order){
				$address=new stdClass();
				$address->address_id = $elements;
				$address->address_published=0;
				$status = parent::save($address);
				$app = JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage(JText::_('ADDRESS_UNPUBLISHED_CAUSE_USED_IN_ORDER'));
				}
			}
		}else{
			$data = $this->get($elements);
			if(!$order || (isset($data->address_published) && !$data->address_published)){
				$status = parent::delete($elements);
			}
		}
		if($status){
			if(empty($data))
				$data = $this->get($elements);
			if(!empty($data->address_default)) {
				$query = 'SELECT MIN(address_id) as address_id, MAX(address_default) as address_default FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$data->address_user_id.' AND address_published = 1';
				$this->database->setQuery($query);
				$ret = $this->database->loadObject();
				if(!empty($ret) && (int)$ret->address_default == 0) {
					$address=new stdClass();
					$address->address_id = (int)$ret->address_id;
					$address->address_default = 1;
					parent::save($address);
				}
			}

			$dispatcher->trigger( 'onAfterAddressDelete', array( & $elements ) );
		}
		return $status;
	}

	function _checkVat(&$vatData){
		$vat = hikashop_get('helper.vat');
		if(!$vat->isValid($vatData)){
			$this->message = @$vat->message;
			return false;
		}
		return true;
	}

	public function miniFormat($address, $fields = null, $format = '') {
		$config = hikashop_config();
		$ret = $config->get('mini_address_format', '');
		if(empty($ret))
			$ret = '{address_lastname} {address_firstname} - {address_street}, {address_state} ({address_country})';
		if(!empty($format))
			$ret = $format;
		if(!empty($fields)) {
			if(empty($this->fieldsClass))
				$this->fieldsClass = hikashop_get('class.field');

			foreach($fields as $field) {
				$fieldname = $field->field_namekey;
				$ret = str_replace('{'.$fieldname.'}', $this->fieldsClass->show($field, @$address->$fieldname), $ret);
			}
		} else {
			foreach($address as $k => $v) {
				if(is_string($v))
					$ret = str_replace('{' . $k . '}', $v, $ret);
			}
		}
		$ret = preg_replace('#{[-_a-zA-Z0-9]+}#iU', '', $ret);
		return $ret;
	}
}
