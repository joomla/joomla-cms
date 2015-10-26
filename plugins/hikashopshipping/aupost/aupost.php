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
class plgHikashopshippingAupost extends hikashopShippingPlugin {
	var $multiple = true;
	var $name = 'aupost';
	var $doc_form = 'aupost';
	var $use_cache = true;
	var $pluginConfig = array(
		'post_code' => array('POST_CODE', 'input'),
		'services' => array('SHIPPING_SERVICES', 'checkbox',array(
			'EXPRESS' => 'EXPRESS',
			'STANDARD' => 'STANDARD',
			'AIR' => 'AIR',
			'SEA' => 'SEA',
		)),
		'reverse_order' => array('Reverse order of services', 'boolean','0'),
		'shipping_group' => array('Group products together', 'boolean','0'),
	);

	function processPackageLimit($limit_key, $limit_value, $product, $qty, $package, $units) {
		switch ($limit_key) {
			case 'volume':
				$divide = (float)($product['x'] * $product['y'] * $product['z']);
				if(empty($divide) || $divide > $limit_value)
					return false;
				return (int)floor($limit_value / $divide);
				break;
			case 'girth':
				$divide = (float)(($product['x'] + $product['y']) * 2);
				if(empty($divide) || $divide > $limit_value)
					return false;
				return (int)floor($limit_value / $divide);
				break;
		}
		return parent::processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units);
	}

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		if(!hikashop_loadUser())
			return false;

		if($this->loadShippingCache($order, $usable_rates, $messages))
			return true;

		$local_usable_rates = array();
		$local_messages = array();
		$currencyClass = hikashop_get('class.currency');
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;

		$cache_usable_rates = array();
		$cache_messages = array();

		$currentCurrencyId = null;

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$iAmSuperAdmin = false;
		if(!HIKASHOP_J16) {
			$iAmSuperAdmin = ($user->get('gid') == 25);
		} else {
			$iAmSuperAdmin = $user->authorise('core.admin');
		}
		if ($iAmSuperAdmin)
			$app->enqueueMessage('That Australia Post shipping version is deprecated and is using the old Australia post API, Please start using the new Australia Post v2 shipping method');
		foreach($local_usable_rates as $rate) {
			if(!empty($rate->shipping_zone_namekey)){
				if(empty($rate->shipping_params->SEA) && empty($rate->shipping_params->AIR) && !empty($order->shipping_address->address_country)){

					$db = JFactory::getDBO();
					if(is_array($order->shipping_address->address_country)){
						$address_country = reset($order->shipping_address->address_country);
					}else{
						$address_country = $order->shipping_address->address_country;
					}
					$db->setQuery('SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey='.$db->Quote($address_country));
					$currentShippingZone = $db->loadObject();

					if($currentShippingZone->zone_code_3 != 'AUS') {
						$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
						continue;
					}
				}
			}

			$check = false;

			if(empty($order->shipping_address->address_post_code)){
				$check = true;
				$message = 'The Australia Post shipping plugin requires the user to enter a postal code when goods are shipped within Australia. Please go to "Display->Custom fields" and set the post code field to required.';
			}elseif(!preg_match('#[0-9]{4}#',$order->shipping_address->address_post_code)){
				$check = true;
				$message = 'The post code entered is not valid';
				$order->shipping_address->address_post_code = preg_replace('#[^0-9A-Z]#','',$order->shipping_address->address_post_code);
			}
			if($check){
				$zoneClass=hikashop_get('class.zone');
				$zones = $zoneClass->getOrderZones($order);
				$db = JFactory::getDBO();
				$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_code_3='.$db->Quote('AUS'));
				$australia_zone = $db->loadResult();
				if(in_array($australia_zone,$zones)){
					$cache_messages['post_code_missing'] = $message;
					continue;
				}
			}
			if(empty($order->shipping_address_full)){
				$cart = hikashop_get('class.cart');
				$address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
				$cart->loadAddress($order->shipping_address_full,$address,'object','shipping');
			}
			$rates = array();

			$this->getRates($rate, $order, $rates);

			if(!empty($rate->shipping_params->reverse_order)){
				$rates=array_reverse($rates,true);
			}

			foreach($rates as $finalRate){
				if(hikashop_getCurrency() != 6)
					$finalRate->shipping_price = $currencyClass->convertUniquePrice($finalRate->shipping_price, 6, hikashop_getCurrency());
				$usable_rates[$finalRate->shipping_id]=$finalRate;
				$cache_usable_rates[$finalRate->shipping_id] = $finalRate;
			}
		}
		$this->setShippingCache($order, $cache_usable_rates, $cache_messages);

		if(!empty($cache_messages)) {
			foreach($cache_messages as $k => $msg) {
				$messages[$k] = $msg;
			}
		}
		return true;
	}
	function getRates($rate, $order, &$rates){
		$weightClass=hikashop_get('helper.weight');
		$volumeClass=hikashop_get('helper.volume');
		$limit = array();
		if(@$order->shipping_address_full->shipping_address->address_country->zone_code_2 == 'AU'){
			$limit['w'] = 22000;
			$limit['volume'] = 250000000;
			$limit['x'] = 1050;
		}else{
			$limit['w'] = 20000;
			$limit['girth'] = 1400;
			$limit['x'] = 1050;
		}
		if(empty($currentCurrencyId)) {
			$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code=\'AUD\'';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$currentCurrencyId = $db->loadResult();
		}
		$parcel=new stdClass();
		$parcel->Weight = 0;
		$parcel->global_volume = 0;
		$parcel->Width = 0;
		$parcel->Height = 0;
		$parcel->Length = 0;
		$parcels = array($parcel);
		$i=0;

		if(isset($rate->shipping_params->shipping_group) && $rate->shipping_params->shipping_group){
			$packages = $this->getOrderPackage($order, array('weight_unit' => 'g', 'volume_unit' => 'mm', 'limit' => $limit, 'required_dimensions' => array('w','x','y','z')));

			if(empty($packages))
				return true;
			if(isset($packages['w']) || isset($packages['x']) || isset($packages['y']) || isset($packages['z'])){
				if(empty($parcels[$i]))
					$parcels[$i] = new stdClass();
				$parcels[$i]->Weight = $packages['w'];
				$parcels[$i]->Width = $packages['z'];
				$parcels[$i]->Height = $packages['y'];
				$parcels[$i]->Length = $packages['x'];

				if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
				if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
				if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
				if($parcels[$i]->Height<1)$parcels[$i]->Height=1;

				$i++;
			}else{
				foreach($packages as $package){
					if(empty($parcels[$i]))
						$parcels[$i] = new stdClass();
					$parcels[$i]->Weight = $package['w'];
					$parcels[$i]->Width = $package['z'];
					$parcels[$i]->Height = $package['y'];
					$parcels[$i]->Length = $package['x'];

					if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
					if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
					if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
					if($parcels[$i]->Height<1)$parcels[$i]->Height=1;

					$i++;
				}
			}
			foreach($parcels as $parcel){
				$parcel->Country = @$order->shipping_address_full->shipping_address->address_country->zone_code_2;
				if(empty($parcel->Country)) $parcel->Country='AU';
				$parcel->Pickup_Postcode = substr(trim(@$rate->shipping_params->post_code),0,4);
				$parcel->Destination_Postcode = substr(trim($order->shipping_address->address_post_code),0,4);
				$parcel->Quantity=1;
				if($parcel->Country=='AU'){
					if(!empty($rate->shipping_params->EXPRESS)){
						$this->addRate($rates,'EXPRESS',$parcel,$rate,$currentCurrencyId, $i);
					}
					if(!empty($rate->shipping_params->STANDARD)){
						$this->addRate($rates,'STANDARD',$parcel,$rate,$currentCurrencyId, $i);
					}
				}else{
					if(!empty($rate->shipping_params->SEA)){
						$this->addRate($rates,'SEA',$parcel,$rate,$currentCurrencyId, $i);
					}
					if(!empty($rate->shipping_params->AIR)){
						$this->addRate($rates,'AIR',$parcel,$rate,$currentCurrencyId, $i);
					}
				}
			}
		}else{
			$limit['unit'] = 1;
			$packages = $this->getOrderPackage($order, array('weight_unit' => 'g', 'volume_unit' => 'mm', 'limit' => $limit, 'required_dimensions' => array('w','x','y','z')));

			if(empty($packages))
				return true;

			if(isset($packages['w']) || isset($packages['x']) || isset($packages['y']) || isset($packages['z'])){
				if(empty($parcels[$i]))
					$parcels[$i] = new stdClass();
				$parcels[$i]->Weight = $packages['w'];
				$parcels[$i]->Width = $packages['z'];
				$parcels[$i]->Height = $packages['y'];
				$parcels[$i]->Length = $packages['x'];

				if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
				if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
				if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
				if($parcels[$i]->Height<1)$parcels[$i]->Height=1;

				$i++;
			}else{
				foreach($packages as $package){
					if(empty($parcels[$i]))
						$parcels[$i] = new stdClass();
					$parcels[$i]->Weight = $package['w'];
					$parcels[$i]->Width = $package['z'];
					$parcels[$i]->Height = $package['y'];
					$parcels[$i]->Length = $package['x'];

					if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
					if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
					if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
					if($parcels[$i]->Height<1)$parcels[$i]->Height=1;

					$i++;
				}
			}

			foreach($parcels as $parcel){
				$parcel->Country = @$order->shipping_address_full->shipping_address->address_country->zone_code_2;
				if(empty($parcel->Country)) $parcel->Country='AU';
				$parcel->Pickup_Postcode = substr(trim(@$rate->shipping_params->post_code),0,4);
				$parcel->Destination_Postcode = substr(trim($order->shipping_address->address_post_code),0,4);
				$parcel->Quantity=1;
				if($parcel->Country=='AU'){
					if(!empty($rate->shipping_params->EXPRESS)){
						$this->addRate($rates,'EXPRESS',$parcel,$rate,$currentCurrencyId, 1);
					}
					if(!empty($rate->shipping_params->STANDARD)){
						$this->addRate($rates,'STANDARD',$parcel,$rate,$currentCurrencyId, 1);
					}
				}else{
					if(!empty($rate->shipping_params->SEA)){
						$this->addRate($rates,'SEA',$parcel,$rate,$currentCurrencyId, 1);
					}
					if(!empty($rate->shipping_params->AIR)){
						$this->addRate($rates,'AIR',$parcel,$rate,$currentCurrencyId, 1);
					}
				}
			}
		}
	}
	function onShippingConfigurationSave(&$element) {

		$app = JFactory::getApplication();

		if(empty($element->shipping_params->post_code)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Australia POST', JText::_('POST_CODE')));
		}
		if (!isset($element->shipping_params->services)) {
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}
		$element->shipping_params->AIR=isset($element->shipping_params->services) && in_array('AIR',$element->shipping_params->services);
		$element->shipping_params->SEA=isset($element->shipping_params->services) && in_array('SEA',$element->shipping_params->services);
		$element->shipping_params->STANDARD=isset($element->shipping_params->services) && in_array('STANDARD',$element->shipping_params->services);
		$element->shipping_params->EXPRESS=isset($element->shipping_params->services) && in_array('EXPRESS',$element->shipping_params->services);
		parent::onShippingConfigurationSave($element);
	}
	function getShippingDefaultValues(&$element){
		$element->shipping_name='Australia Post';
		$element->shipping_description='';
		$element->shipping_images='aupost';
		$element->shipping_params->AIR='AIR';
		$element->shipping_params->SEA='SEA';
		$element->shipping_params->STANDARD='STANDARD';
		$element->shipping_params->EXPRESS='EXPRESS';
		$element->shipping_params->post_code='';
		$elements = array($element);
	}
	function onShippingConfiguration(&$element){

		$app = JFactory::getApplication();
		$app->enqueueMessage('That Australia Post shipping version is deprecated and is using the old Australia post API, Please start using the new Australia Post v2 shipping method');

		$this->aupost = JRequest::getCmd('name','aupost');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';

		parent::onShippingConfiguration($element);
	}
	function addRate(&$rates,$type,$parcel,&$rate,$currency, $nb_package){
		if(empty($nb_package))
			$nb_package = 1;
		$parcel->Service_Type=$type;
		$url='http://drc.edeliver.com.au/ratecalc.asp?';
		foreach(get_object_vars($parcel) as $key => $val){
			$url.=$key.'='.$val.'&';
		}
		$url = rtrim($url,'&');
		$url = parse_url($url);
		if(!isset($url['query'])){
		$url['query'] = '';
		}

		if(!isset($url['port'])){
			if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
				$url['port'] = 443;
			}else{
			$url['port'] = 80;
			}
		}
		if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
			$url['host_socket'] = 'ssl://'.$url['host'];
		}else{
			$url['host_socket'] = $url['host'];
		}
		$fp = fsockopen ( $url['host_socket'], $url['port'], $errno, $errstr, 30);
		if (!$fp) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( 'Cannot connect to australia post web service. You hosting company might be blocking outbond connections');
			return false;
		}
		$uri = $url['path'].($url['query']!='' ? '?' . $url['query'] : '');
		$header = "GET $uri HTTP/1.0\r\n".
			"User-Agent: PHP/".phpversion()."\r\n".
			"Referer: ".hikashop_currentURL()."\r\n".
			"Server: ".$_SERVER['SERVER_SOFTWARE']."\r\n".
			"Host: ".$url['host'].":".$url['port']."\r\n".
			"Accept: */"."*\r\n\r\n";

			fwrite($fp, $header);
		$response = '';
		while (!feof($fp)) {
			$response .= fgets ($fp, 1024);
		}
		fclose ($fp);
		$pos = strpos($response, "\r\n\r\n");
		$header = substr($response, 0, $pos);
		$body = substr($response, $pos + 2 * strlen("\r\n\r\n"));
		if(preg_match_all('#([a-z_]+)=([a-z_\.0-9 ]+?)#Ui',$response,$matches)){
			$data = array();
			foreach($matches[1] as $key=>$val){
				$data[$val]=$matches[2][$key];
			}
			if(!empty($data['err_msg'])){
				if($data['err_msg']=='OK'){
					if(empty($rates[$type])){
						$info = new stdClass();
						$info = (!HIKASHOP_PHP5) ? $rate : clone($rate);
						$info->shipping_name .=' '.JText::_($type);
						if (!empty($rate->shipping_description))
							$info->shipping_description = $rate->shipping_description . ' ';
						else{
							$shipping_description = JText::_($type.'_DESCRIPTION');
							if($shipping_description == $type.'_DESCRIPTION'){
								$info->shipping_description .= $shipping_description;
							}
							$info->shipping_description=$shipping_description;
						}
						$types = array('SEA' => 1, 'AIR' => 2, 'EXPRESS' => 3, 'STANDARD' => 4);
						$info->shipping_id .= '-' . $types[$type];
						$rates[$type]=$info;
					} else {
						$shipping_description = JText::_($type.'_DESCRIPTION');
						if($shipping_description ==$type.'_DESCRIPTION'){ $shipping_description = ''; }
						if(empty($shipping_description)){ $shipping_description = $rate->shipping_description; }
						if(!empty($shipping_description)){ $shipping_description .= '<br/>'; }
						if($nb_package > 1 && (isset($rate->shipping_params->shipping_group) && $rate->shipping_params->shipping_group)) $rates[$type]->shipping_description = $shipping_description . JText::sprintf('X_PACKAGES', $nb_package);
						else $rates[$type]->shipping_description = $shipping_description;
					}
					if(@$rates[$type]->shipping_tax_id){
						$currencyClass = hikashop_get('class.currency');
						$data['charge'] = $currencyClass->getUntaxedPrice($data['charge'],hikashop_getZone(),$rates[$type]->shipping_tax_id);
					}
					$rates[$type]->shipping_price += $data['charge'];
				}elseif(!empty($data['err_msg'])){
					if(preg_match('#Selected Destination not reached by .*#i',$data['err_msg'])){
						return true;
					}
					$app = JFactory::getApplication();
					$app->enqueueMessage('The request to the Australia Post server failed with the message: '.$data['err_msg']);
				}else{
					$app = JFactory::getApplication();
					$app->enqueueMessage('The request to the Australia Post server failed');
				}
			}
		}
	}
	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->SEA)){
			$methods[$main->shipping_id.'-1'] = $main->shipping_name.' '.JText::_('SEA');
		}
		if(!empty($main->shipping_params->AIR)){
			$methods[$main->shipping_id.'-2'] = $main->shipping_name.' '.JText::_('AIR');
		}
		if(!empty($main->shipping_params->EXPRESS)){
			$methods[$main->shipping_id.'-3'] = $main->shipping_name.' '.JText::_('EXPRESS');
		}
		if(!empty($main->shipping_params->STANDARD)){
			$methods[$main->shipping_id.'-4'] = $main->shipping_name.' '.JText::_('STANDARD');
		}
		return $methods;
	}
}
