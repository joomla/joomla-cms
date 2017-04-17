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
class plgHikashopshippingUPS extends hikashopShippingPlugin
{
	var $ups_methods = array(
		array('key' => 1, 'code' => '01', 'name' => 'UPS Next Day Air', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172') , 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key' => 2, 'code' => '02', 'name' => 'UPS Second Day Air', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key' => 3, 'code' => '03', 'name' => 'UPS Ground', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key' => 4, 'code' => '07', 'name' => 'UPS Worldwide Express', 'countries' => 'USA, PUERTO RICO, CANADA', 'zones' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'international')),
		array('key' => 5, 'code' => '08', 'name' => 'UPS Worldwide Expedited', 'countries' => 'USA, PUERTO RICO, CANADA' , 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172', 'country_Canada_38', 'international')),
		array('key' => 6, 'code' => '11', 'name' => 'UPS Standard', 'countries' => 'USA, CANADA, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_United_States_of_America_223', 'country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other'), 'destinations' => array('country_United_States_of_America_223', 'country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other')),
		array('key' => 7, 'code' => '12', 'name' => 'UPS Three-Day Select', 'countries' => 'USA, CANADA', 'zones' => array('country_United_States_of_America_223', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223', 'country_Canada_38')),
		array('key' => 8, 'code' => '13', 'name' => 'UPS Next Day Air Saver', 'countries' => 'USA', 'zones' => array('country_United_States_of_America_223'), 'destinations' => array('country_United_States_of_America_223')),
		array('key' => 9, 'code' => '14', 'name' => 'UPS Next Day Air Early A.M.', 'countries' => 'USA, PUERTO RICO' , 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key' => 10, 'code' => '54', 'name' => 'UPS Worldwide Express Plus', 'countries' => 'USA, CANADA, POLAND, EUROPEAN UNION, OTHER, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other', 'country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other', 'country_Puerto_Rico_172', 'international')),
		array('key' => 11, 'code' => '59', 'name' => 'UPS Second Day Air A.M.', 'countries' => 'USA', 'zones' => array('country_United_States_of_America_223'), 'destinations' => array('country_United_States_of_America_223')),
		array('key' => 12, 'code' => '65', 'name' => 'UPS Saver', 'countries' => 'USA, PUERTO RICO, CANADA, MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'country_Mexico_138', 'country_Poland_170', 'tax_europe_9728', 'other'), 'destinations' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'country_Mexico_138', 'country_Poland_170', 'tax_europe_9728', 'other')),

		array('key' => 13, 'code' => '01', 'double' => true, 'name' => 'UPS Express CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('key' => 14, 'code' => '02', 'double' => true, 'name' => 'UPS Expedited CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('key' => 15, 'code' => '13', 'double' => true, 'name' => 'UPS Saver CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('key' => 16, 'code' => '14', 'double' => true, 'name' => 'UPS Express Early A.M', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),

		array('key' => 17, 'code' => '07', 'name' => 'UPS Express', 'countries' => 'MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other'), 'destinations' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other')),
		array('key' => 18, 'code' => '08', 'name' => 'UPS Expedited', 'countries' => 'MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other'), 'destinations' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other')),
		array('key' => 19, 'code' => '54', 'name' => 'UPS Express Plus', 'countries' => 'MEXICO', 'zones' => array('country_Mexico_138'), 'destinations' => array('country_Mexico_138')),

		array('key' => 20, 'code' => '82', 'name' => 'UPS Today Standard', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('key' => 21, 'code' => '83', 'name' => 'UPS Today Dedicated Courrier', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('key' => 22, 'code' => '84', 'name' => 'UPS Today Intercity', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('key' => 23, 'code' => '85', 'name' => 'UPS Today Express', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('key' => 24, 'code' => '86', 'name' => 'UPS Today Express Saver', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),

		array('key' => 25, 'code' => 'TDCB', 'name' => 'Trade Direct Cross Border', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('key' => 26, 'code' => 'TDA', 'name' => 'Trade Direct Air', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('key' => 27, 'code' => 'TDO', 'name' => 'Trade Direct Ocean', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('key' => 28, 'code' => '308', 'name' => 'UPS Freight LTL', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('key' => 29, 'code' => '309', 'name' => 'UPS Freight LTL Guaranteed', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('key' => 30, 'code' => '310', 'name' => 'UPS Freight LTL Urgent', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
	);

	var $convertUnit=array(
		'kg' => 'KGS',
		'lb' => 'LBS',
		'cm' => 'CM',
		'in' => 'IN',
		'kg2' => 'kg',
		'lb2' => 'lb',
		'cm2' => 'cm',
		'in2' => 'in',
	);

	var $multiple = true;
	var $name = 'ups';
	var $doc_form = 'ups';
	var $use_cache = true;

	public $nbpackage = 0;
	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->methodsList)){
			$main->shipping_params->methods=unserialize($main->shipping_params->methodsList);
		}
		if(!empty($main->shipping_params->methods)){
			foreach($main->shipping_params->methods as $method){
				$selected = null;
				foreach($this->ups_methods as $ups){
					if($ups['code']==$method) {
						$selected = $ups;
						break;
					}
				}
				if($selected){
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
				}
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
		if(!hikashop_loadUser())
			return false;

		if(empty($order->shipping_address))
			return true;

		if($this->loadShippingCache($order, $usable_rates, $messages))
			return true;

		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;

		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The UPS shipping plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$cache_usable_rates = array();
		$cache_messages = array();

		$currentShippingZone = null;
		$currentCurrencyId = null;

		$found = true;
		$usableWarehouses = array();
		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass->getOrderZones($order);

		$this->error_messages = array();

		foreach($local_usable_rates as $k => $rate){
			if(empty($rate->shipping_params->warehousesList)) {
				$cache_messages['no_warehouse_configured'] = 'No warehouse configured in the UPS shipping plugin options';
				continue;
			}

			$rate->shipping_params->warehouses = unserialize($rate->shipping_params->warehousesList);
			foreach($rate->shipping_params->warehouses as $warehouse) {
				if(!empty($warehouse->country) && empty($warehouse->zone) || $warehouse->zone == '-' || in_array($warehouse->zone, $zones))
					$usableWarehouses[] = $warehouse;
			}

			if(empty($usableWarehouses)) {
				$cache_messages['no_warehouse_configured'] = 'No available warehouse found for your location';
				continue;
			}

			if(empty($rate->shipping_params->methodsList)) {
				$cache_messages['no_shipping_methods_configured'] = 'No shipping methods configured in the UPS shipping plugin options';
				continue;
			}

			if($order->weight <= 0 || ($order->volume <= 0 && @$rate->shipping_params->exclude_dimensions != 1)) {
				continue;
			}

			$rate->shipping_params->methods = unserialize($rate->shipping_params->methodsList);

			$this->freight = false;
			$this->classicMethod = false;
			$heavyProduct = false;
			$weightTotal = 0;
			if(!empty($rate->shipping_params->methods)) {
				foreach($rate->shipping_params->methods as $method) {
					if(in_array($method, array('TDCB', 'TDA', 'TDO', '308', '309', '310')))
						$this->freight = true;
					else
						$this->classicMethod = true;
				}
			}
			$null = null;
			if(empty($this->shipping_currency_id)) {
				$this->shipping_currency_id = hikashop_getCurrency();
			}
			$currencyClass = hikashop_get('class.currency');
			$currencies = $currencyClass->getCurrencies(array($this->shipping_currency_id),$null);
			$this->shipping_currency_code = $currencies[$this->shipping_currency_id]->currency_code;

			$cart = hikashop_get('class.cart');
			$cart->loadAddress($null, $order->shipping_address->address_id, 'object', 'shipping');


			$receivedMethods = $this->_getBestMethods($rate, $order, $usableWarehouses, $heavyProduct, $null);

			if(empty($receivedMethods)) {
				continue;
			}

			$i = 0;
			$new_usable_rates = array();
			foreach($receivedMethods as $method) {
				$new_usable_rates[$i] = (!HIKASHOP_PHP5) ? $rate : clone($rate);
				$new_usable_rates[$i]->shipping_price += round($method['value'], 2);
				$selected_method = '';
				$name = '';

				foreach($this->ups_methods as $ups_method) {
					if($ups_method['code'] == $method['code'] && ($method['old_currency_code'] == 'CAD' || !isset($ups_method['double']))) {
						$selected_method = $ups_method['key'];
						$name = $ups_method['name'];
						break;
					}
				}

				$new_usable_rates[$i]->shipping_name = $name;

				if(!empty($selected_method))
					$new_usable_rates[$i]->shipping_id .= '-' . $selected_method;

				if(isset($rate->shipping_params->show_eta) && $rate->shipping_params->show_eta){
					if($method['delivery_day'] != -1)
						$new_usable_rates[$i]->shipping_description .= ' '.JText::sprintf('ESTIMATED_TIME_AFTER_SEND', $method['delivery_day']);
					else
						$new_usable_rates[$i]->shipping_description .= ' '.JText::_('NO_ESTIMATED_TIME_AFTER_SEND');

					if($method['delivery_time'] != -1)
						$new_usable_rates[$i]->shipping_description .= '<br/>'.JText::sprintf('DELIVERY_HOUR', $method['delivery_time']);
					else
						$new_usable_rates[$i]->shipping_description .= '<br/>'.JText::_('NO_DELIVERY_HOUR');
				}

				if($rate->shipping_params->group_package && $this->nbpackage > 1)
					$new_usable_rates[$i]->shipping_description .= '<br/>'.JText::sprintf('X_PACKAGES', $this->nbpackage);
				$i++;
			}

			foreach($new_usable_rates as $i => $rate) {
				if(isset($rate->shipping_price_orig) || isset($rate->shipping_currency_id_orig)){
					if($rate->shipping_currency_id_orig == $rate->shipping_currency_id)
						$rate->shipping_price_orig = $rate->shipping_price;
					else
						$rate->shipping_price_orig = $currencyClass->convertUniquePrice($rate->shipping_price, $rate->shipping_currency_id, $rate->shipping_currency_id_orig);
				}
				$usable_rates[$rate->shipping_id] = $rate;
				$cache_usable_rates[$rate->shipping_id] = $rate;
			}
		}

		if(!empty($this->error_messages)){
			foreach($this->error_messages as $key => $value){
				$cache_messages[$key] = $value;
			}
		}

		$this->setShippingCache($order, $cache_usable_rates, $cache_messages);

		if(!empty($cache_messages)) {
			foreach($cache_messages as $k => $msg) {
				$messages[$k] = $msg;
			}
		}
	}

	function getShippingDefaultValues(&$element){
		$element->shipping_name = 'UPS';
		$element->shipping_description = '';
		$element->group_package = 0;
		$element->shipping_images = 'ups';
		$element->shipping_type = $this->ups;
		$element->shipping_params->post_code = '';
		$element->shipping_currency_id = $this->main_currency;
		$element->shipping_params->pickup_type = '01';
		$element->shipping_params->destination_type = 'auto';
	}

	function onShippingConfiguration(&$element){
		$config =& hikashop_config();
		$app = JFactory::getApplication();
		$this->main_currency = $config->get('main_currency', 1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_get('class.currency');
		$this->currencyCode = $currency->get($this->main_currency)->currency_code;
		$this->currencySymbol = $currency->get($this->main_currency)->currency_symbol;

		$this->ups = JRequest::getCmd('name','ups');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';

		parent::onShippingConfiguration($element);

		$elements = array($element);
		$key = key($elements);
		if(!empty($elements[$key]->shipping_params->warehousesList)){
			$elements[$key]->shipping_params->warehouse = unserialize($elements[$key]->shipping_params->warehousesList);
		}
		if(!empty($elements[$key]->shipping_params->methodsList)){
			$elements[$key]->shipping_params->methods = unserialize($elements[$key]->shipping_params->methodsList);
		}
		$js = '
function deleteRow(divName,inputName,rowName){
	var d = document.getElementById(divName);
	var olddiv = document.getElementById(inputName);
	if(d && olddiv){
		d.removeChild(olddiv);
		document.getElementById(rowName).style.display=\'none\';
	}
	return false;
}

function deleteZone(zoneName){
	var d = document.getElementById(zoneName);
	if(d){
		d.innerHTML="";
	}
	return false;
}
		';

	 	$js.='
function checkAllBox(id, type){
	var toCheck = document.getElementById(id).getElementsByTagName("input");
	for (i = 0 ; i < toCheck.length ; i++) {
		if (toCheck[i].type == "checkbox") {
			toCheck[i].checked = (type == "check");
		}
	}
}
';
		if(empty($elements[$key]->shipping_params->access_code)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('UPS_ACCESS_CODE')),'notice');
		}
		if(empty($elements[$key]->shipping_params->user_id)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('UPS_USER_ID')),'notice');
		}
		if(empty($elements[$key]->shipping_params->password)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('HIKA_PASSWORD')),'notice');
		}
		if(empty($elements[$key]->shipping_params->shipper_number)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('SHIPPER_NUMBER')),'notice');
		}

		if(empty($elements[$key]->shipping_params->warehouse[0]->zip)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('POST_CODE')),'notice');
		}
		if(empty($elements[$key]->shipping_params->warehouse[0]->city)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',JText::_('CITY')),'notice');
		}

		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$elements){
		parent::onShippingConfiguration($elements);
		$warehouses = JRequest::getVar( 'warehouse', array(), '', 'array' );
		$cats = array();
		$methods=array();
		$db = JFactory::getDBO();
		$zone_keys='';

		if(isset($_REQUEST['data']['shipping_methods'])){
			foreach($_REQUEST['data']['shipping_methods'] as $method){
				foreach($this->ups_methods as $upsMethod){
					$name=strtolower($upsMethod['name']);
					$name=str_replace(' ','_', $name);
					if($name==$method['name']){
						$obj = new stdClass();
						$methods[strip_tags($method['name'])]=strip_tags($upsMethod['code']);
					}
				}
			}
		}
		$elements->shipping_params->methodsList = serialize($methods);

		if(!empty($warehouses)){
			foreach($warehouses as $id => $warehouse){
				if(!empty($warehouse['zone']))
					$zone_keys.='zone_namekey='.$db->Quote($warehouse['zone']).' OR ';
			}
			$zone_keys=substr($zone_keys,0,-4);
			if(!empty($zone_keys)){
				$query=' SELECT zone_namekey, zone_id, zone_name_english FROM '.hikashop_table('zone').' WHERE '.$zone_keys;
				$db->setQuery($query);
				$zones = $db->loadObjectList();

			}
			foreach($warehouses as $id => $warehouse){
				$warehouse['zone_name']='';
				if(!empty($zones)){
					foreach($zones as $zone){
						if($zone->zone_namekey==$warehouse['zone'])
							$warehouse['zone_name']=$zone->zone_id.' '.$zone->zone_name_english;
					}
				}

				if(!empty($_REQUEST['warehouse'][$id]['zip'])){
					$obj = new stdClass();
					$obj->name = strip_tags($_REQUEST['warehouse'][$id]['name']);
					$obj->zip = strip_tags($_REQUEST['warehouse'][$id]['zip']);
					$obj->statecode = strip_tags($_REQUEST['warehouse'][$id]['statecode']);
					$obj->city = strip_tags($_REQUEST['warehouse'][$id]['city']);
					$obj->country = strip_tags($_REQUEST['warehouse'][$id]['country']);
					$obj->zone = @strip_tags($_REQUEST['warehouse'][$id]['zone']);
					$obj->zone_name = $warehouse['zone_name'];
					$obj->units = strip_tags($_REQUEST['warehouse'][$id]['units']);
					$obj->currency = strip_tags($_REQUEST['warehouse'][$id]['currency']);
					$cats[]=$obj;
				}
			}
			$elements->shipping_params->warehousesList = serialize($cats);
		}

		if(empty($cats)){
			$obj = new stdClass();
			$obj->name = '';
			$obj->zip = '';
			$obj->statecode = '';
			$obj->city = '';
			$obj->country = '';
			$obj->zone = '';
			$void[]=$obj;
			$elements->shipping_params->warehousesList = serialize($void);
		}
		return true;
	}

	function _getBestMethods(&$rate, &$order, &$usableWarehouses, $heavyProduct, $null) {
		$db = JFactory::getDBO();
		$usableMethods=array();
		$zone_code='';

		$freight=false;
		$classicMethod=false;
		foreach($rate->shipping_params->methods as $method){
			if($method=='TDCB' || $method=='TDA' || $method=='TDO' || $method=='308' || $method=='309' || $method=='310'){
				$this->freight = true;
			} else {
				$this->classicMethod = true;
			}
		}

		$currencies = array();
		foreach($usableWarehouses as $warehouse) {
			$zone_code .= $db->Quote($warehouse->country).',';
			if(!empty($warehouse->currency))
				$currencies[$warehouse->currency] = (int)$warehouse->currency;
			else {
				$config = hikashop_config();
				$c = (int)$config->get('main_currency', 1);
				$currencies[$c] = $c;
			}
		}
		$zone_code = substr($zone_code,0,-1);

		$query='SELECT zone_id, zone_code_2 FROM '.hikashop_table('zone').' WHERE zone_id IN ('.$zone_code.')';
		$db->setQuery($query);
		$warehouses_namekey = $db->loadObjectList();
		if(!empty($warehouses_namekey)){
			foreach($usableWarehouses as $warehouse) {
				foreach($warehouses_namekey as $zone) {
					if($zone->zone_id == $warehouse->country) {
						$warehouse->country_ID=$zone->zone_code_2;
					}
				}
			}
		}

		$query='SELECT currency_code, currency_id FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$currencies).')';
		$db->setQuery($query);
		$warehouses_currency_code = $db->loadObjectList();
		if(!empty($warehouses_currency_code)){
			foreach($usableWarehouses as $k => $warehouse) {
				foreach($warehouses_currency_code as $currency_code) {
					if(!empty($warehouse->currency) && $warehouse->currency == $currency_code->currency_id) {
						$usableWarehouses[$k]->currency_code = $currency_code->currency_code;
					}
				}
			}
		}
		foreach($usableWarehouses as $k => $warehouse){
			$usableWarehouses[$k]->methods = $this->_getShippingMethods($rate, $order, $warehouse, $heavyProduct, $null);
		}
		if(empty($usableWarehouses)){
			return false;
		}

		foreach($usableWarehouses as $k => $warehouse){
			if(!empty($warehouse->methods)){
				foreach($warehouse->methods as $i => $method){
					if(!in_array($method['code'], $rate->shipping_params->methods)){
						unset($usableWarehouses[$k]->methods[$i]);
					}
				}
			}
		}
		$bestPrice=99999999;
		foreach($usableWarehouses as $id => $warehouse){
			if(!empty($warehouse->methods)){
				foreach($warehouse->methods as $method){
					if($method['value']<$bestPrice){
						$bestPrice=$method['value'];
						$bestWarehouse=$id;
					}
				}
			}
		}
		if(isset($bestWarehouse)){
			return $usableWarehouses[$bestWarehouse]->methods;
		}
		return false;
	}

	function _getShippingMethods(&$rate, &$order, &$warehouse, $heavyProduct, $null){
		$data['userId'] = $rate->shipping_params->user_id;
		$data['accessLicenseNumber'] = $rate->shipping_params->access_code;
		$data['password'] = $rate->shipping_params->password;
		$data['destCity'] = $null->shipping_address->address_city;
		$data['destZip'] = $null->shipping_address->address_post_code;
		if(empty($null->shipping_address->address_country->zone_code_2)) $null->shipping_address->address_country->zone_code_2 = 'US';
		$data['destCountry'] = $null->shipping_address->address_country->zone_code_2;
		$data['destStatecode'] = $null->shipping_address->address_state->zone_code_3;
		$data['city'] = $warehouse->city;
		$data['zip'] = $warehouse->zip;
		$data['stateCode'] = @$warehouse->statecode;
		$data['country'] = $warehouse->country_ID;
		$data['units'] = $warehouse->units;
		$data['currency'] = $warehouse->currency;
		$data['currency_code'] = $warehouse->currency_code;
		$data['old_currency'] = $warehouse->currency;
		$data['old_currency_code'] = $warehouse->currency_code;
		$data['shipperNumber'] = $rate->shipping_params->shipper_number;
		$data['XMLpackage'] = '';
		$data['destType'] = '';
		$data['negotiated_rate'] = '';
		$limitations = array();
		if($rate->shipping_params->destination_type=='res') {
			$data['destType']='<ResidentialAddressIndicator/>';
		}
		if($rate->shipping_params->destination_type=='auto' && empty($order->shipping_address->address_company)) {
			$data['destType']='<ResidentialAddressIndicator/>';
		}
		$data['pickup_type']=$rate->shipping_params->pickup_type;

		$totalPrice = 0;
		if(($this->freight == true && $this->classicMethod == false) || ($heavyProduct == true && $this->freight == true)) {
			$data['weight'] = 0;
			$data['height'] = 0;
			$data['length'] = 0;
			$data['width'] = 0;
			$data['price'] = 0;
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							$caracs=parent::_convertCharacteristics($variant, $data);
							$data['weight_unit']=$caracs['weight_unit'];
							$data['dimension_unit']=$caracs['dimension_unit'];
							$data['weight']+=round($caracs['weight'],2)*$variant->cart_product_quantity;
							$data['height']+=round($caracs['height'],2)*$variant->cart_product_quantity;
							$data['length']+=round($caracs['length'],2)*$variant->cart_product_quantity;
							$data['width']+=round($caracs['width'],2)*$variant->cart_product_quantity;
							$data['price']+=$variant->prices[0]->unit_price->price_value_with_tax*$variant->cart_product_quantity;
						}
					}
					else{
						$caracs=parent::_convertCharacteristics($product,$data);
						$data['weight_unit']=$caracs['weight_unit'];
						$data['dimension_unit']=$caracs['dimension_unit'];
						$data['weight']+=round($caracs['weight'],2)*$product->cart_product_quantity;
						$data['height']+=round($caracs['height'],2)*$product->cart_product_quantity;
						$data['length']+=round($caracs['length'],2)*$product->cart_product_quantity;
						$data['width']+=round($caracs['width'],2)*$product->cart_product_quantity;
						$data['price']+=$product->prices[0]->unit_price->price_value_with_tax*$product->cart_product_quantity;
					}
				}
			}

			$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order );
			if(!empty($rate->shipping_params->negotiated_rate)) {
				$data['negotiated_rate'] = '<RateInformation><NegotiatedRatesIndicator/></RateInformation>';
			}
			$usableMethods=$this->_UPSrequestMethods($data);
			return $usableMethods;
		}

		if($rate->shipping_params->group_package){
			$data['weight']=0;
			$data['height']=0;
			$data['length']=0;
			$data['width']=0;
			$data['price']=0;
			$current_package = array();
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							for($i=0;$i<$variant->cart_product_quantity;$i++){
								$caracs= parent::_convertCharacteristics($variant, $data);
								$current_package = parent::groupPackages($data, $caracs);
								if($data['weight_unit'] == 'KGS'){
									$limitations['weight'] = 70;
								} else{
									$limitations['weight'] = 150;
								}
								if($data['dimension_unit'] == 'CM') {
									$limitations['dimension'] = 419;
								} else {
									$limitations['dimension'] = 165;
								}

								if($data['weight']+round($caracs['weight'],2)>$limitations['weight'] || $current_package['dim']>$limitations['dimension']){
									$this->nbpackage++;
									$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
									$data['weight']=round($caracs['weight'],2);
									$data['height']=$current_package['y'];
									$data['length']=$current_package['z'];
									$data['width']=$current_package['x'];
									$data['price']=$variant->prices[0]->unit_price->price_value_with_tax;
								}
								else{
									$data['weight']+=round($caracs['weight'],2);
									$data['height']=max($data['height'],$current_package['y']);
									$data['length']=max($data['length'],$current_package['z']);
									$data['width']+=$current_package['x'];
									$data['price']+=$variant->prices[0]->unit_price->price_value_with_tax;
								}
							}
						}
					}
					else{
						for($i=0;$i<$product->cart_product_quantity;$i++){
							$caracs = parent::_convertCharacteristics($product,$data);
							$current_package = parent::groupPackages($data,$caracs);
							if($data['weight_unit'] == 'KGS'){
								$limitations['weight'] = 70;
							}
							else{
								$limitations['weight'] = 150;
							}
							if($data['dimension_unit'] == 'CM'){
								$limitations['dimension'] = 419;
							}
							else{
								$limitations['dimension'] = 165;
							}
							if($data['weight']+round($caracs['weight'],2)>$limitations['weight'] || $current_package['dim']>$limitations['dimension']){
								$this->nbpackage++;
								$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
								$data['weight']=round($caracs['weight'],2);
								$data['height']=$current_package['y'];
								$data['length']=$current_package['z'];
								$data['width']=$current_package['x'];
								$data['price']=$product->prices[0]->unit_price->price_value_with_tax;
							}
							else{
								$data['weight']+=round($caracs['weight'],2);
								$data['height']=max($data['height'],$current_package['y']);
								$data['length']=max($data['length'],$current_package['z']);
								$data['width']+=$current_package['x'];
								$data['price']+=$product->prices[0]->unit_price->price_value_with_tax;
							}
						}
					}
				}
			}
			if (($data['weight']+$data['height']+$data['length']+$data['width'])>0){
				$this->nbpackage++;
				$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true);
			}
			if(!empty($rate->shipping_params->negotiated_rate)) {
				$data['negotiated_rate'] = '<RateInformation><NegotiatedRatesIndicator/></RateInformation>';
			}
			$usableMethods=$this->_UPSrequestMethods($data);
		}
		else{
			foreach($order->products as $product){
				$data['weight']=0;
				$data['height']=0;
				$data['length']=0;
				$data['width']=0;
				if(isset($product->prices[0])){
					if(!isset($product->prices[0]->unit_price->price_value_with_tax))
						$data['price']=$product->prices[0]->price_value_with_tax;
					else
						$data['price']=$product->prices[0]->unit_price->price_value_with_tax;
				}
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							$data['price']=$variant->prices[0]->unit_price->price_value_with_tax;
							for($i=0;$i<$variant->cart_product_quantity;$i++){
								$data['XMLpackage'].=$this->_createPackage($data, $variant, $rate, $order, true);
							}
						}
					}
					else{
						if(isset($product->prices[0])){
							if(!isset($product->prices[0]->unit_price->price_value_with_tax))
								$data['price']=$product->prices[0]->price_value_with_tax;
							else
								$data['price']=$product->prices[0]->unit_price->price_value_with_tax;
						}
						for($i=0;$i<$product->cart_product_quantity;$i++){
							$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
						}
					}
				}
			}
			if(!empty($rate->shipping_params->negotiated_rate)) {
				$data['negotiated_rate'] = '<RateInformation><NegotiatedRatesIndicator/></RateInformation>';
			}
			$usableMethods=$this->_UPSrequestMethods($data);
		}
		if(empty($usableMethods)){
			return false;
		}
		$currencies=array();
		foreach($usableMethods as $method){
			$currencies[$method['currency_code']]='"'.$method['currency_code'].'"';
		}
		$db = JFactory::getDBO();
		$query='SELECT currency_code, currency_id FROM '.hikashop_table('currency').' WHERE currency_code IN ('.implode(',',$currencies).')';
		$db->setQuery($query);
		$currencyList = $db->loadObjectList();
		$currencyList=reset($currencyList);
		foreach($usableMethods as $i => $method){
			$usableMethods[$i]['currency_id']=$currencyList->currency_id;
		}

		$usableMethods = parent::_currencyConversion($usableMethods, $order);
		return $usableMethods;
	}

	function _createPackage(&$data, &$product, &$rate, &$order, $includeDimension=false){
		if(@$rate->shipping_params->exclude_dimensions==1){
			$includeDimension=false;
		}

		if(empty($data['weight'])){
			$caracs = parent::_convertCharacteristics($product, $data);

			$data['weight_unit']=$caracs['weight_unit'];
			$data['dimension_unit']=$caracs['dimension_unit'];
			$data['weight']=round($caracs['weight'],2);
			$data['height']=round($caracs['height'],2);
			$data['length']=round($caracs['length'],2);
			$data['width']=round($caracs['width'],2);
		}
		$currencyClass=hikashop_get('class.currency');
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_getCurrency();

		if(isset($data['price'])){
			$price=$data['price'];
		}
		else{
			$price=$product->prices[0]->unit_price->price_value;
		}

		if($this->shipping_currency_id!=$data['currency']){
			$price=$currencyClass->convertUniquePrice($price, $this->shipping_currency_id,$data['currency']);
		}

		if(!empty($rate->shipping_params->weight_approximation)){
			$data['weight']=$data['weight']+$data['weight']*$rate->shipping_params->weight_approximation/100;
		}

		if($data['weight']<0.1){
			$data['weight']=0.1;
		}

		if(!empty($rate->shipping_params->dim_approximation)){
			$data['height']=$data['height']+$data['height']*$rate->shipping_params->dim_approximation/100;
			$data['length']=$data['length']+$data['length']*$rate->shipping_params->dim_approximation/100;
			$data['width']=$data['width']+$data['width']*$rate->shipping_params->dim_approximation/100;
		}

		$options='';
		$dimension='';
		if($rate->shipping_params->include_price){
			$options = '
	<PackageServiceOptions>
		<InsuredValue>
			<CurrencyCode>'.$data['currency_code'].'</CurrencyCode>
			<MonetaryValue>'.$price.'</MonetaryValue>
		</InsuredValue>
	</PackageServiceOptions>';
		}

		if($includeDimension){
			$dimension = '
	<Dimensions>
		<UnitOfMeasurement>
			<Code>'.$data['dimension_unit'].'</Code>
		</UnitOfMeasurement>
		<Length>'.round($data['length'],2).'</Length>
		<Width>'.round($data['width'],2).'</Width>
		<Height>'.round($data['height'],2).'</Height>
	</Dimensions>';
		}

		$xml = '
<Package>
	<PackagingType>
		<Code>02</Code>
	</PackagingType>
	<Description>Shop</Description>
'.$dimension.'
	<PackageWeight>
		<UnitOfMeasurement>
			<Code>'.$data['weight_unit'].'</Code>
		</UnitOfMeasurement>
		<Weight>'.$data['weight'].'</Weight>
	</PackageWeight>
'.$options.'
</Package>';

		return $xml;
	}

	function _UPSrequestMethods($data){
		$fromStateCode = '';
		$destStateCode = '';
		$negotiated_rate = '';
		if($data['stateCode'] != ''){
			$fromStateCode = '<StateProvinceCode>'.$data['stateCode'].'</StateProvinceCode>';
			$destStateCode = '<StateProvinceCode>'.$data['destStatecode'].'</StateProvinceCode>';
			$negotiated_rate = $data['negotiated_rate'];
		}
		$xml='<?xml version="1.0" ?>
<AccessRequest xml:lang=\'en-US\'>
	<AccessLicenseNumber>'.$data['accessLicenseNumber'].'</AccessLicenseNumber>
	<UserId>'.$data['userId'].'</UserId>
	<Password>'.str_replace('&','&amp;',$data['password']).'</Password>
</AccessRequest>
<?xml version="1.0" ?>
<RatingServiceSelectionRequest>
	<Request>
		<TransactionReference>
			<CustomerContext>Rating and Service</CustomerContext>
			<XpciVersion>1.0</XpciVersion>
		</TransactionReference>
		<RequestAction>Rate</RequestAction>
		<RequestOption>shop</RequestOption>
	</Request>
	<PickupType>
		<Code>'.$data['pickup_type'].'</Code>
		<Description>Daily Pickup</Description>
	</PickupType>
	<Shipment>
		<Description>Rate Shopping - Domestic</Description>
		<Shipper>
			<ShipperNumber>'.$data['shipperNumber'].'</ShipperNumber>
			<Address>
				<City>'.$data['city'].'</City>
				<PostalCode>'.$data['zip'].'</PostalCode>
				<CountryCode>'.$data['country'].'</CountryCode>
			</Address>
		</Shipper>
		<ShipTo>
			<Address>
				<City>'.$data['destCity'].'</City>
				'.$destStateCode.'
				<PostalCode>'.$data['destZip'].'</PostalCode>
				<CountryCode>'.$data['destCountry'].'</CountryCode>
				'.$data['destType'].'
			</Address>
		</ShipTo>
		<ShipFrom>
			<Address>
				<City>'.$data['city'].'</City>
				'.$fromStateCode.'
				<PostalCode>'.$data['zip'].'</PostalCode>
				<CountryCode>'.$data['country'].'</CountryCode>
			</Address>
		</ShipFrom>
		' . $negotiated_rate . $data['XMLpackage']. '
		<ShipmentServiceOptions />
	</Shipment>
</RatingServiceSelectionRequest>';

		if(@$rate->shipping_params->debug){
			echo '<!-- '. $xml. ' -->'."\r\n"; // THIS LINE IS FOR DEBUG PURPOSES ONLY-IT WILL SHOW IN HTML COMMENTS
		}
		$session = curl_init("https://www.ups.com/ups.app/xml/Rate");
		curl_setopt($session, CURLOPT_HEADER, 1);
		curl_setopt($session,CURLOPT_POST,1);
		curl_setopt($session,CURLOPT_TIMEOUT, 30);
		curl_setopt($session,CURLOPT_RETURNTRANSFER,1);
		curl_setopt ($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session,CURLOPT_POSTFIELDS,$xml);
		$result=curl_exec($session);
		$error = curl_errno($session);

		if( !$error && !empty($result)) {
			if(@$rate->shipping_params->debug){
				echo '<!-- '. $result. ' -->'; // THIS LINE IS FOR DEBUG PURPOSES ONLY-IT WILL SHOW IN HTML COMMENTS
			}
			$xml_data = strstr($result, '<?');
			$xml = simplexml_load_string($xml_data);

			$shipment= array();
			$i = 1;
			foreach($xml->RatedShipment as $rate){
				$shipment[$i]['value'] = (string) $rate->TotalCharges->MonetaryValue;
				$shipment[$i]['currency_code'] = (string)$rate->TotalCharges->CurrencyCode;
				$shipment[$i]['old_currency_code'] = (string)$rate->TotalCharges->CurrencyCode;
				$shipment[$i]['code'] = (string)$rate->Service->Code;
				$shipment[$i]['delivery_day'] = (string)$rate->GuaranteedDaysToDelivery;
				$shipment[$i]['delivery_time'] = (string)$rate->ScheduledDeliveryTime;
				$i++;
			}
			$ok = false;
			$error_volume = false;
			$error_locations = false;
			foreach($xml->Response->Error as $ups_error){
				$error=true;
				$shipment[$i]['return'] = (string)$xml->Response->ResponseStatusCode;
				if($shipment[$i]['return']=="-1"){
					$app = JFactory::getApplication();
					$shipment[$i]['err_message'] = (string)$xml->Response->Error->ErrorDescription;
					$shipment[$i]['err_code'] = (string)$xml->Response->Error->ErrorCode;
					if($shipment[$i]['err_code']==111210){
						$error_locations = $shipment[$i]['err_message'];
					}elseif($shipment[$i]['err_code']<=111056 && $shipment[$i]['err_code']>=111050){
						$error_volume = true;
					}else{
						$app->enqueueMessage( 'Error while sending XML to UPS. Error code: '.$shipment[$i]['err_code'].'. Message: '.$shipment[$i]['err_message'].'', 'error');
					}
				}
			}
			if($error){
				if($error_volume){
					$this->error_messages['ups_volume_too_big']=JText::_('ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS');
				}
				if($error_locations){
					$this->error_messages['ups_no_locations']='No UPS shipping methods available: '.$error_locations;
				}
				return false;
			}
			return $shipment;
		} else {
			$app = JFactory::getApplication();
			$error = curl_error($session);
			if(!empty($error)) $error = ' : '.$error;
			$app->enqueueMessage('An error occurred. The connection to the UPS server could not be established'.$error);
		}
		curl_close($session);
	}

}
