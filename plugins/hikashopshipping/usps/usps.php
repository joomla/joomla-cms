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
class plgHikashopshippingUSPS extends hikashopShippingPlugin
{
	var $multiple = true;
	var $name = 'usps';
	var $doc_form = 'usps';
	var $pluginConfig = array(
		'usps_user_id' => array('USPS WebTools User ID', 'input'),
		'post_code' => array('POST_CODE', 'input'),
		'services' => array('SHIPPING_SERVICES', 'checkbox',array(
			'PRIORITY' => 'Priority Mail',
			'MEDIA' => 'Media Mail',
			'EXPRESS' => 'Express Mail',
			'EXPRESSINT' => 'Priority Mail Express International (International)',
			'EXPRESSINTBOX' => 'Priority Mail Express International Flat Rate Boxes (International)',
			'FIRST CLASS' => 'First Class Mail',
			'FIRSTCLASSINT' => 'First Class Mail (International)',
			'INTERNATIONAL' => 'Priority Mail International (International)',
			'PRIORITYINTSMALL' => 'Priority Mail International Small Flat Rate Box (International)',
			'PRIORITYINTDVD' => 'Priority Mail International DVD Flat Rate priced box (International)',
			'PRIORITYINTLARGEVIDEO' => 'Priority Mail International Large Video Flat Rate priced box (International)',
			'PRIORITYINTMEDIUM' => 'Priority Mail International Medium Flat Rate Box (International)',
			'PRIORITYINTLARGE' => 'Priority Mail International Large Flat Rate Box (International)',
			'ENVELOPE' => 'USPS GXG Envelopes (International)',
		)),
		'weight_approximation' => array('Weight approximation (%)', 'input'),
		'dim_approximation' => array('Dimension approximation (%)', 'input'),
		'machinableCheck' => array(
			'Machinable Shipments', 'checkbox', array(
				'MACHINABLE' => 'Are most of your packages <a href="http://pe.usps.com/text/dmm300/101.htm">machinable</a>?',
			)
		),
		'sort_by_price' => array('Sort by shipping price', 'list',array(
			'NO' => 'No',
			'ASC' => 'Ascending',
			'DESC' => 'Descending',
		)),
		'firstclass_mail_type' => array('First class mail type', 'list',array(
			'PARCEL' => 'Parcel',
			'LETTER' => 'Letter',
			'FLAT' => 'Flat',
			'POSTCARD' => 'Postcard',
			'PACKAGE SERVICE' => 'Package service',
		)),
		'container' => array('Container', 'list',array(
			'VARIABLE' => 'Variable',
			'FLAT RATE ENVELOPE' => 'Flate rate envelope',
			'PADDED FLAT RATE ENVELOPE' => 'Padded flat rate envelope',
			'LEGAL FLAT RATE ENVELOPE' => 'Legal flate rate envelope',
			'SM FLAT RATE ENVELOPE' => 'Small flate rate envelope',
			'WINDOW FLAT RATE ENVELOPE' => 'Window flat rate envelope',
			'GIFT CARD FLAT RATE ENVELOPE' => 'Gift cart flat rate envelope',
			'FLAT RATE BOX' => 'Flate rate box',
			'SM FLAT RATE BOX' => 'Small flate rate box',
			'MD FLAT RATE BOX' => 'Medium flate rate box',
			'LG FLAT RATE BOX' => 'Large flate rate box',
			'RECTANGULAR' => 'Rectangular',
			'NONRECTANGULAR' => 'Non rectangular',
		))
	);
	var $methods = array(
		'PRIORITY' => 1,
		'PRIORITYINTSMALL' => 2,
		'PRIORITYINTDVD' => 3,
		'PRIORITYINTLARGEVIDEO' => 4,
		'PRIORITYINTMEDIUM' => 5,
		'PRIORITYINTLARGE' => 6,
		'EXPRESS' => 7,
		'EXPRESSINT' => 8,
		'EXPRESSINTBOX' => 9,
		'FIRST CLASS' => 11,
		'FIRSTCLASSINT' => 12,
		'INTERNATIONAL' => 13,
		'ENVELOPE' => 14,
		'MEDIA' => 15,
	);

	var $use_cache = true;

	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->services)){
			foreach($main->shipping_params->services as $service){
				$selected = null;
				foreach($this->methods as $name => $key){
					if($name == $service) {
						$selected = array('name' => $this->pluginConfig['services'][2][$name], 'key' => $key);
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

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		if(!hikashop_loadUser())
			return false;

		if($this->loadShippingCache($order, $usable_rates, $messages))
			return true;

		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;

		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The USPS shipping plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$cache_rates = array();
		$cache_messages = array();
		$found = false;

		$currentShippingZone = null;
		$currentCurrencyId = null;
		foreach($local_usable_rates as $rate) {
			if($rate->shipping_type != 'usps')
				continue;
			if(empty($order->shipping_address))
				continue;

			$found = true;
			$zones = null;

			if(!empty($rate->shipping_zone_namekey)) {
				if(empty($zones)) {
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
				}

				if(!in_array($rate->shipping_zone_namekey,$zones)) {
					$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
					continue;
				}

				$db = JFactory::getDBO();
				if(is_array($order->shipping_address->address_country)) {
					$address_country = reset($order->shipping_address->address_country);
				} else {
					$address_country = $order->shipping_address->address_country;
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey='.$db->Quote($address_country));
				$zone = $db->loadObject();
				if($zone->zone_code_3 != 'USA') {
					$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
					continue;
				}
			}

			$check = false;
			if(empty($order->shipping_address->address_post_code)) {
				$check = true;
				$message = 'The USPS shipping plugin requires the user to enter a postal code when goods are shipped within the United States. Please go to "Display->Custom fields" and set the post code field to required.';
			} elseif(!preg_match('#^[0-9]{5}(-?[0-9]{4})?$#',$order->shipping_address->address_post_code)) {
				$check = true;
				$message = 'The post code entered is not valid';
			}

			if($check) {
				if(empty($zones)) {
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
				}

				$db = JFactory::getDBO();
				$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_code_3='.$db->Quote('USA'));
				$usa_zone = $db->loadResult();
				if(in_array($usa_zone, $zones)) {
					$messages['post_code_missing'] = $message;
					continue;
				}
			}

			foreach($order->products as $product) {
				if (!empty($rate->shipping_params->weight_approximation)) {
					if (isset($product->product_weight)) $product->product_weight = $product->product_weight + $product->product_weight * $rate->shipping_params->weight_approximation / 100;
					if (isset($product->product_weight_orig)) $product->product_weight_orig = $product->product_weight_orig + $product->product_weight_orig * $rate->shipping_params->weight_approximation / 100;
				}
				if (!empty($rate->shipping_params->dim_approximation)) {
					if (isset($product->product_height)) $product->product_height = $product->product_height + $product->product_height * $rate->shipping_params->dim_approximation / 100;
					if (isset($product->product_length)) $product->product_length = $product->product_length + $product->product_length * $rate->shipping_params->dim_approximation / 100;
					if (isset($product->product_width)) $product->product_width = $product->product_width + $product->product_width * $rate->shipping_params->dim_approximation / 100;
					if (isset($product->product_total_volume)) $product->product_total_volume = $product->product_total_volume + $product->product_total_volume * $rate->shipping_params->dim_approximation / 100;
					if (isset($product->product_total_volume_orig)) $product->product_total_volume_orig = $product->product_total_volume_orig + $product->product_total_volume_orig * $rate->shipping_params->dim_approximation / 100;
				}
				if (isset($product->variants)) {
					foreach($product->variants as $variant) {
						if (!empty($rate->shipping_params->weight_approximation)) {
							if (isset($variant->product_weight)) $variant->product_weight = $variant->product_weight + $variant->product_weight * $rate->shipping_params->weight_approximation / 100;
							if (isset($variant->product_weight_orig)) $variant->product_weight_orig = $variant->product_weight_orig + $variant->product_weight_orig * $rate->shipping_params->weight_approximation / 100;
						}
						if (!empty($rate->shipping_params->dim_approximation)) {
							if (isset($variant->product_height)) $variant->product_height = $variant->product_height + $variant->product_height * $rate->shipping_params->dim_approximation / 100;
							if (isset($variant->product_length)) $variant->product_length = $variant->product_length + $variant->product_length * $rate->shipping_params->dim_approximation / 100;
							if (isset($variant->product_width)) $variant->product_width = $variant->product_width + $variant->product_width * $rate->shipping_params->dim_approximation / 100;
							if (isset($variant->product_total_volume)) $variant->product_total_volume = $variant->product_total_volume + $variant->product_total_volume * $rate->shipping_params->dim_approximation / 100;
							if (isset($variant->product_total_volume_orig)) $variant->product_total_volume_orig = $variant->product_total_volume_orig + $variant->product_total_volume_orig * $rate->shipping_params->dim_approximation / 100;
						}
					}
				}
			}

			$package = $this->getOrderPackage($order, array('weight_unit' => 'oz', 'volume_unit' => 'in', 'required_dimensions' => array('w','x','y','z')));

			if(isset($package[0]))
				$package = $package[0];

			$max_weight = 1120;
			if($package['w'] > $max_weight) {
				$messages['items_weight_over_limit'] = JText::_('ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS');
				$cache_messages['items_weight_over_limit'] = JText::_('ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS');
				$this->setShippingCache($order, null, $cache_messages);
				return true;
			}

			if($package['w'] < 1)
				$package['w'] = 1; // Minimum 1oz

			if(empty($order->shipping_address_full)) {
				$cart = hikashop_get('class.cart');
				$app = JFactory::getApplication();
				$address = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
				$cart->loadAddress($order->shipping_address_full, $address, 'object', 'shipping');
			}

			$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code=\'USD\'';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$currency = $db->loadResult();

			$parcel = new stdClass();
			$parcel->Country = @$order->shipping_address_full->shipping_address->address_country->zone_code_2;
			if(empty($parcel->Country))
				$parcel->Country = 'US';
			$parcel->Pickup_Postcode = substr(preg_replace('#[^a-z0-9]#i', '', @$rate->shipping_params->post_code), 0, 5);
			$parcel->Destination_Postcode = substr(preg_replace('#[^a-z0-9]#i', '', $order->shipping_address->address_post_code), 0, 5);

			$girth = array($package['y'], $package['x'], $package['z']);
			sort($girth);

			$parcel->Length = $girth[2];
			$parcel->Width = $girth[1];
			$parcel->Height = $girth[0];
			$parcel->Girth = (($parcel->Height + $parcel->Width) * 2) + $parcel->Length;

			$parcel->Quantity = 1;
			$parcel->Weight = $package['w'];
			$rates = array();
			if($parcel->Country == 'US') {
				$modes = array('PRIORITY', 'MEDIA', 'EXPRESS', 'FIRST CLASS');
				foreach($modes as $mode) {
					if(!empty($rate->shipping_params->$mode))
						$this->addRate($rates, $mode, $parcel, $rate, $currency, false);
				}
			} else {
				$modes = array('INTERNATIONAL', 'PRIORITYINTSMALL', 'PRIORITYINTDVD', 'PRIORITYINTLARGEVIDEO', 'PRIORITYINTMEDIUM', 'PRIORITYINTLARGE', 'EXPRESSINTBOX', 'EXPRESSINT', 'FIRSTCLASSINT', 'ENVELOPE');
				foreach($modes as $mode) {
					if(!empty($rate->shipping_params->$mode))
						$this->addRate($rates, $mode, $parcel, $rate, $currency, true);
				}
			}

			if($rate->shipping_params->sort_by_price == 'DESC')
				$rates = $this->array_sort($rates, 'shipping_price', 'usps', SORT_DESC);
			else if($rate->shipping_params->sort_by_price == 'ASC')
				$rates = $this->array_sort($rates, 'shipping_price', 'usps', SORT_ASC);

			$i = 0;

			if(empty($rates)) {
				$messages['no_shipping_quotes'] = 'Failed to obtain shipping quotes.';
				$cache_messages['no_shipping_quotes'] = 'Failed to obtain shipping quotes.';
				$this->setShippingCache($order, null, $cache_messages);

				return true;
			}
			foreach($rates as $finalRate) {
				$finalRate->shipping_ordering .= '_' . $i++;
				$usable_rates[$finalRate->shipping_id] = $finalRate;
				$cache_rates[$finalRate->shipping_id] = $finalRate;
			}
		}

		if(!$found) {
			$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			$cache_messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
		}

		$this->setShippingCache($order, $cache_rates, $cache_messages);

		return true;
	}

	function addRate(&$rates, $type, $parcel, &$rate, $currency, $isInternational) {
		$parcel->Service_Type = $type;
		$app = JFactory::getApplication();

		$usps_user_id = $rate->shipping_params->usps_user_id;
		$package_weight_arr = $this->getUSPSweightDimensions($parcel->Weight);
		$package_weight_lb = $package_weight_arr['Pounds'];
		$package_weight_oz = $package_weight_arr['Ounces'];

		$service = $parcel->Service_Type;
		$origin_zip = $parcel->Pickup_Postcode;
		$destination_zip = $parcel->Destination_Postcode;
		$this->countries = $this->USPS_country_list();
		$destination_country = $this->countries[$parcel->Country];
		$machinable = 'false';
		$package_id = 1; //will change this when setting up for multiple packages.

		if (isset($rate->shipping_params->MACHINABLE)) {
			$machinable = "true";
		}
		$package_weight_oz = round($package_weight_oz,2);

		if($parcel->Weight > 13 && $type =='FIRST CLASS')
			return;

		if($isInternational) {
			$request = '<'.'?xml version="1.0"?'.'>'.
				'<IntlRateV2Request USERID="' . $usps_user_id . '">';

			if($parcel->Country == 'CA')
				$request .= '<Revision>2</Revision>';
			else
				$request .= '<Revision />';

			$request .=	'<Package ID="' . $package_id . '">'.
				'<Pounds>' . $package_weight_lb . '</Pounds>'.
				'<Ounces>' . $package_weight_oz . '</Ounces>'.
				'<Machinable>' . $machinable . '</Machinable>'.
				'<MailType>Package</MailType>'.
				'<ValueOfContents>100</ValueOfContents>'.
				'<Country>' . $destination_country . '</Country>'.
				'<Container>' . $rate->shipping_params->container . '</Container>'.
				'<Size>Regular</Size>'.
				'<Width>'.$parcel->Width.'</Width>'.
				'<Length>'.$parcel->Length.'</Length>'.
				'<Height>'.$parcel->Height.'</Height>'.
				'<Girth>'.$parcel->Girth.'</Girth>';

			if($parcel->Country == 'CA')
				$request .= '<OriginZip>' . $origin_zip . '</OriginZip>';

				$request .= '</Package>'.
				'</IntlRateV2Request>';
		}
		else {
			$request = '<'.'?xml version="1.0"?'.'>'.
				'<RateV4Request USERID="' . $usps_user_id . '">'.
				'<Revision />'.
				'<Package ID="' . $package_id . '">'.
				'<Service>' . $service . '</Service>';

			if($type =='FIRST CLASS'){
				if(!empty($rate->shipping_params->firstclass_mail_type)){
					$request .= '<FirstClassMailType>' . $rate->shipping_params->firstclass_mail_type . '</FirstClassMailType>';
				}
				else{
					$request .= '<FirstClassMailType>Parcel</FirstClassMailType>';
				}
			}
			$request .= '<ZipOrigination>' . $origin_zip . '</ZipOrigination>'.
				'<ZipDestination>' . $destination_zip . '</ZipDestination>'.
				'<Pounds>' . $package_weight_lb . '</Pounds>'.
				'<Ounces>' . $package_weight_oz . '</Ounces>'.
				'<Container>' . $rate->shipping_params->container . '</Container>'.
				'<Size>Regular</Size>'.
				'<Width>'.$parcel->Width.'</Width>'.
				'<Length>'.$parcel->Length.'</Length>'.
				'<Height>'.$parcel->Height.'</Height>'.
				'<Girth>'.$parcel->Girth.'</Girth>'.
				'<Machinable>' . $machinable . '</Machinable>'.
				'</Package>'.
				'</RateV4Request>';
		}


		$responseError = false;
		$response_xml = $this->doUSPS($request, !$isInternational);
		if(!$response_xml)
			return false;

		if($response_xml->Number) {
			 $app->enqueueMessage( 'USPS error: ' . $response_xml->Number . ' ' . $response_xml->Description);
			 $responseError = true;
		}

		if($response_xml->Package->Error) {
			 $app->enqueueMessage( 'USPS error: ' . $response_xml->Package->Error->Number . ' ' . $response_xml->Package->Error->Description);
			 $responseError = true;
		}

		if($isInternational) {
			$rateResult = $response_xml->xpath('Package/Service');
			$usps_rate_arr = $this->xml2array($rateResult);

			$decoding = array(
				2 => 'INTERNATIONAL',
				16 => 'PRIORITYINTSMALL',
				24 => 'PRIORITYINTDVD',
				25 => 'PRIORITYINTLARGEVIDEO',
				9 => 'PRIORITYINTMEDIUM',
				11 => 'PRIORITYINTLARGE',
				15 => 'FIRSTCLASSINT',
				16 => 'PRIORITYINTSMALL',
				24 => 'PRIORITYINTDVD',
				25 => 'PRIORITYINTLARGEVIDEO',
				9 => 'PRIORITYINTMEDIUM',
				11 => 'PRIORITYINTLARGE',
				26 => 'EXPRESSINTBOX',
				1 => 'EXPRESSINT',
				12 => 'ENVELOPE'
			);

			foreach($usps_rate_arr as $k) {
				$id = (int)$k['@attributes']['ID'];
				if(isset($decoding[$id]) && strcmp($parcel->Service_Type, $decoding[$id]) == 0) {
					if(isset($k['ServiceErrors']) || $k['Postage'] == 0)
						continue;

					$usps_rates = array(
						'Service' => html_entity_decode($k['SvcDescription']),
						'Rate' => $k['Postage']
					);
				}
			}
		} else {
			$rateResult = $response_xml->xpath('Package/Postage');
			$usps_rate_arr = $this->xml2array($rateResult);
			foreach($usps_rate_arr as $k => $v) {
				$usps_rates = array(
					'Service' => html_entity_decode($v['MailService']),
					'Rate' => $v['Rate']
				);
			}

			if(empty($usps_rates)) {
				$responseError = true;
			}
		}

		if($responseError == false && !empty($usps_rates)) {
			if(empty($rates[$type])) {
				$info = new stdClass();
				$info = (!HIKASHOP_PHP5) ? $rate : clone($rate);
				$info->shipping_name = preg_replace('#sup.*?sup#', '', $info->shipping_name.' : '. $this->pluginConfig['services'][2][$type]);
				$shipping_description = JText::_($type.'_DESCRIPTION');
				if($shipping_description != $type.'_DESCRIPTION') {
					$info->shipping_description .= $shipping_description;
				}
				$info->shipping_id .= '-'.$this->methods[$type];
				$rates[$type] = $info;
			}
			$rates[$type]->shipping_price += $usps_rates['Rate'];
		}
	}

	function getShippingDefaultValues(&$element){
		$element->shipping_name='United States Postal Service';
		$element->shipping_description='';
		$element->shipping_images='usps';
		$element->shipping_params->PRIORITY='Priority Mail';
		$element->shipping_params->MEDIA='Media Mail';
		$element->shipping_params->EXPRESS='Express Mail';
		$FIRSTCLASS = 'FIRST CLASS';
		$element->shipping_params->$FIRSTCLASS='First Class Mail';
		$element->shipping_params->INTERNATIONAL='Priority Mail International (International)';
		$element->shipping_params->PRIORITYINTSMALL='Priority Mail International Small Flat Rate Box (International)';
		$element->shipping_params->PRIORITYINTDVD='Priority Mail International DVD Flat Rate priced box (International)';
		$element->shipping_params->PRIORITYINTLARGEVIDEO='Priority Mail International Large Video Flat Rate priced box (International)';
		$element->shipping_params->PRIORITYINTMEDIUM='Priority Mail International Medium Flat Rate Box (International)';
		$element->shipping_params->PRIORITYINTLARGE='Priority Mail International Large Flat Rate Box (International)';
		$element->shipping_params->EXPRESSINTBOX='Express Mail International Flat Rate Boxes (International)';
		$element->shipping_params->EXPRESSINT='Express Mail International (International)';
		$element->shipping_params->FIRSTCLASSINT='First Class Mail (International)';
		$element->shipping_params->ENVELOPE='USPS GXG Envelopes (International)';
		$element->shipping_params->post_code='';
		$element->shipping_params->firstclass_mail_type='PARCEL';
		$element->shipping_params->container='RECTANGULAR';
		$element->shipping_params->MACHINABLE=true;
	}

	function onShippingConfiguration(&$element){
		$config = &hikashop_config();
		$this->usps = JRequest::getCmd('name','usps');
		$this->main_currency = $config->get('main_currency', 1);

		$currencyClass = hikashop_get('class.currency');
		$currency = $currencyClass->get($this->main_currency);
		$this->currencyCode = $currency->currency_code;
		$this->currencySymbol = $currency->currency_symbol;

		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';

		parent::onShippingConfiguration($element);
		$elements = array($element);
	}

	function onShippingConfigurationSave(&$element) {
		$app = JFactory::getApplication();
		if(empty($element->shipping_params->usps_user_id)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'USPS', ' WebTools User ID'));
		}
		if(empty($element->shipping_params->post_code)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'USPS', JText::_('POST_CODE')));
		}
		if(isset($element->shipping_params->services)){
			$element->shipping_params->PRIORITY=in_array('PRIORITY',$element->shipping_params->services);
			$element->shipping_params->MEDIA=in_array('MEDIA',$element->shipping_params->services);
			$element->shipping_params->EXPRESS=in_array('EXPRESS',$element->shipping_params->services);
			$FIRSTCLASS = 'FIRST CLASS';
			$element->shipping_params->$FIRSTCLASS=in_array('FIRST CLASS',$element->shipping_params->services);
			$element->shipping_params->INTERNATIONAL=in_array('INTERNATIONAL',$element->shipping_params->services);
			$element->shipping_params->PRIORITYINTSMALL=in_array('PRIORITYINTSMALL',$element->shipping_params->services);
			$element->shipping_params->PRIORITYINTDVD=in_array('PRIORITYINTDVD',$element->shipping_params->services);
			$element->shipping_params->PRIORITYINTLARGEVIDEO=in_array('PRIORITYINTLARGEVIDEO',$element->shipping_params->services);
			$element->shipping_params->PRIORITYINTMEDIUM=in_array('PRIORITYINTMEDIUM',$element->shipping_params->services);
			$element->shipping_params->PRIORITYINTLARGE=in_array('PRIORITYINTLARGE',$element->shipping_params->services);
			$element->shipping_params->EXPRESSINTBOX=in_array('EXPRESSINTBOX',$element->shipping_params->services);
			$element->shipping_params->EXPRESSINT=in_array('EXPRESSINT',$element->shipping_params->services);
			$element->shipping_params->FIRSTCLASSINT=in_array('FIRSTCLASSINT',$element->shipping_params->services);
			$element->shipping_params->ENVELOPE=in_array('ENVELOPE',$element->shipping_params->services);
		}else {
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}
		if(!empty($element->shipping_params->machinableCheck))
			$element->shipping_params->MACHINABLE = in_array('MACHINABLE',$element->shipping_params->machinableCheck);
		parent::onShippingConfigurationSave($element);
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		return true;
	}

	function doUSPS($XMLRequest, $domesticShipment) {
		$apiName = 'RateV4';

		if($domesticShipment == false)
			$apiName = 'IntlRateV2';

		$url = 'http://production.shippingapis.com/ShippingAPI.dll?API=' . $apiName . '&XML=' . urlencode($XMLRequest);

		$session = curl_init();
		curl_setopt($session, CURLOPT_FRESH_CONNECT,  true);
		curl_setopt($session, CURLOPT_POST,           false);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_COOKIEFILE,     '');
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_ENCODING,       'UTF-8');
		curl_setopt($session, CURLOPT_URL,            $url);
		$result = curl_exec($session);
		$error = curl_error($session);

		if(empty($result)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( 'Cannot connect to USPS web service. You hosting company might be blocking outbound connections.<br/>'.$error);
			return false;
		}

		$responseDoc = simplexml_load_string($result);
		if($responseDoc === false) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('Cannot dialog with USPS web service.<br/>'.$result);
			return false;
		}
		return $responseDoc;
	}


	function getResponseXML($httpResponse) {
		$lines = preg_split('/(\r\n|\r|\n)/', $httpResponse);
		$responseBody = '';
		$lineCount = count($lines);
		for($i = 0; $i < $lineCount; $i++) {
			if($lines[$i] == '')
				break;
		}

		for($j = $i + 1; $j < $lineCount; $j++) {
			$responseBody .= $lines[$j] . "\n";
		}
		return $responseBody;
	}

	function getUSPSweightDimensions($ounces) {
		if($ounces < 16)
			return array('Pounds' => 0, 'Ounces' => $ounces);
		$pounds = floor($ounces / 16);
		$ounces =  fmod($ounces, $pounds * 16);
		return array('Pounds' => $pounds, 'Ounces' => $ounces);
	}

	function USPS_Country_List() {
		return array(
'AF' => 'Afghanistan', 'AL' => 'Albania', 'AX' => 'Aland Island (Finland)', 'DZ' => 'Algeria', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AG' => 'Antigua and Barbuda',
'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BC' => 'Canada', 'BS' => 'Bahamas', 'BH' => 'Bahrain',
'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia',
'BA' => 'Bosnia-Herzegovina', 'BW' => 'Botswana', 'BR' => 'Brazil', 'VG' => 'British Virgin Islands', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso',
'MM' => 'Burma', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic',
'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island (Australia)', 'CC' => 'Cocos Island (Australia)', 'CO' => 'Colombia', 'KM' => 'Comoros',
'CG' => 'Congo, Republic of the', 'CD' => 'Congo, Democratic Republic of the', 'CK' => 'Cook Islands (New Zealand)', 'CR' => 'Costa Rica', 'CI' => 'Cote d Ivoire (Ivory Coast)',
'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic',
'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands',
'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'FX' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'GA' => 'Gabon', 'GM' => 'Gambia',
'GE' => 'Georgia, Republic of', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GB' => 'Great Britain and Northern Ireland', 'GR' => 'Greece', 'GL' => 'Greenland',
'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HN' => 'Honduras', 'HK' => 'Hong Kong',
'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica',
'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia',
'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao',
'MK' => 'Macedonia, Republic of', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MQ' => 'Martinique',
'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte (France)', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States of', 'MD' => 'Moldova', 'MC' => 'Monaco (France)',
'MN' => 'Mongolia', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands',
'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria',
'KP' => 'North Korea (Korea, Democratic People\'s Republic of)', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay',
'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn Island', 'PL' => 'Poland', 'PT' => 'Portugal', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russia',
'RW' => 'Rwanda', 'SH' => 'Saint Helena',  'KN' => 'Saint Kitts (St. Christopher and Nevis)', 'LC' => 'Saint Lucia', 'PM' => 'Saint Pierre and Miquelon',
'VC' => 'Saint Vincent and the Grenadines', 'SM' => 'San Marino', 'ST' => 'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles',
'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovak Republic', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa',
'GS' => 'South Georgia (Falkland Islands)', 'KR' => 'South Korea (Korea, Republic of)', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SZ' => 'Swaziland',
'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand',
'TL' => 'East Timor (Indonesia)', 'TG' => 'Togo', 'TK' => 'Tokelau (Union) Group (Western Samoa)', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey',
'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'US' => 'United States', 'AE' => 'United Arab Emirates',
'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican City', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'WF' => 'Wallis and Futuna Islands',
'WS' => 'Western Samoa', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'
		);
	}

	function xml2array( $xmlObject, $out = array())
	{
		foreach( (array) $xmlObject as $index => $node)
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		return $out;
	}

	function array_sort($array, $on, $type, $order = SORT_ASC)
	{
		if(empty($array))
			return array();

		$new_array = array();
		$sortable_array = array();
		foreach ($array as $key => $value) {
			if($value->shipping_type == $type)
				$sortable_array[$key] = $value->shipping_price;
		}

		switch ($order) {
			case SORT_ASC:
				asort($sortable_array);
			break;
			case SORT_DESC:
				arsort($sortable_array);
			break;
		}

		foreach ($sortable_array as $k => $v) {
			$new_array[$k] = $array[$k];
		}
		return $new_array;
	}
}
