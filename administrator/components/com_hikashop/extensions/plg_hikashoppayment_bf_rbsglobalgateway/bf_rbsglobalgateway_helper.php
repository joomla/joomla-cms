<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for Worldpay Global Gateway using XML Redirect.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derived from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * See: http://www.worldpay.com/support/kb/gg/submittingtransactionsredirect/rxml.html
 *
 * In order to configure and use this plug-in you must have a Worldpay Global Gateway account.
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class rbsglobalgateway_helper {
	static function getSiteLogo(&$params) {
		if (!empty($params->instid)) return null;
		$src = $params->siteLogo;
		if (empty($src)) return null;
		if (!preg_match('@^http[s]*:@i', $src)) $src = JURI::root() . '/' . $src;
		return str_replace('http://', 'https://', $src);
	}
	static function roundPrice(&$params, $value) {
		return round($value,$params->_exponent);
	}
	static function formatPrice(&$params, $value) {
		return $params->_currency_symbol . sprintf(' %0.0' . $params->_exponent . 'f', $value);
	}
	static function product_price(&$params, &$product, &$tax_cart) {
		$amount_item = self::roundPrice($params, $product->order_product_total_price_no_vat);
		$tax_item    = self::roundPrice($params, $product->order_product_tax);
		if (@$params->show_tax_amount) $tax_cart += ($tax_item*$product->order_product_quantity);
		else $amount_item += $tax_item;
		return self::formatPrice($params, $amount_item);
	}
	static function shipping_price(&$params, &$order, &$tax_cart) {
		if(!empty($order->order_shipping_price)){
			if (@$params->show_tax_amount && !empty($order->cart->shipping->shipping_price)) {
				$amount_item = self::roundPrice($params, $order->cart->shipping->shipping_price);
				$tax_item = self::roundPrice($params, $order->cart->shipping->shipping_price_with_tax)-$amount_item;
				$tax_cart+=$tax_item;
				return self::formatPrice($params, $amount_item);
			}
			return self::formatPrice($params, self::roundPrice($params, $order->order_shipping_price));
		}
		return self::formatPrice($params, 0);
	}
	static function shipping_name(&$params, &$order) {
		if (empty($order->cart->shipping->shipping_name)) return $order->order_shipping_method;
		return ucwords($order->cart->shipping->shipping_name);
	}
	static function loadAddress(&$order, $type) {
		$app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
		$address = $app->getUserState( HIKASHOP_COMPONENT. '.' . $type . '_address');
		if(!empty($address)) $cart->loadAddress($order->cart, $address, 'object', $type);
	}
	static function contentEmailAddress(&$user, &$order, $colspan=1, $inline=false) {
		echo '<tr class="rbs-email-address-header"><td colspan="' . $colspan . '" class="rbs-email-address-header">Your Email Address:';
		if ($inline) echo ' <span class="rbs-email-address-detail">' . $user->user_email . '</span>';
		else echo '</td></tr><tr class="rbs-email-address-detail"><td colspan="' . $colspan . '">' . $user->user_email;
		echo '</td></tr>';
	}
	static function contentAddress(&$params, &$user, &$order, $address_type, $colspan=1) {
		$app =& JFactory::getApplication();
		$address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
		if(empty($address)) return null;
		$address = null;
		$address .= '<tr class="rbs-row-spacer"><td colspan="' . $colspan . '" class="rbs-row-spacer">&nbsp;</td></tr>';
		$address .= '<tr class="rbs-address-header"><td colspan="' . $colspan . '" class="rbs-address-header">';
		switch ($address_type) {
			case 'shipping_address':
				$address .= 'Your Shipping Address:';
				break;
			default:
				$address .= 'Your Billing Address:';
				break;
		}
		$address .= '</td></tr>';
		$address .= '<tr class="rbs-address-detail"><td colspan="' . $colspan . '" class="rbs-address-detail">';
		$address .= htmlspecialchars($order->cart->$address_type->address_title) . ' ' .
								htmlspecialchars($order->cart->$address_type->address_firstname) . ' ' .
								htmlspecialchars($order->cart->$address_type->address_lastname) . ',';
		$address .= '<br/>';
		$field = $params->houseNameField;
		if (!empty($field)) {
			$house_name = trim(@$order->cart->$address_type->$field);
			if (!empty($house_name)) $address .= htmlspecialchars($house_name) . ',<br />';
		}
		$field = $params->houseNoField;
		if (!empty($field)) {
			$house_no = trim(@$order->cart->$address_type->$field);
			if (!empty($house_no)) $address .= htmlspecialchars($house_no) . ' ';
		}
		if (!empty($order->cart->$address_type->address_street)) {
			$address .= htmlspecialchars($order->cart->$address_type->address_street) . ',';
		}
		if (!empty($order->cart->$address_type->address_city)) {
			$address .= '<br/>';
			$address .= htmlspecialchars($order->cart->$address_type->address_city) . ',';
		}
		if (!empty($order->cart->$address_type->post_code)) {
			$address .= '<br/>';
			$address .= htmlspecialchars($order->cart->$address_type->address_post_code) . ',';
		}
		$country_name = @$order->cart->$address_type->address_country->zone_name_english;
		if (empty($country_name)) $country_name = 'United Kingdom';
		$address .= '<br/>';
		$address .= htmlspecialchars($country_name);
		return $address;
	}
	static function raiseError($showVars, $message) {
		if (!empty($showVars)) echo $message . '<br/>';
 	else{
 		$app =&JFactory::getApplication();
 		$app->enqueueMessage($message,'error');
 	}
	}
	static function saveRBSReference(&$order_id, $paymentRefField, $reference) {
		if (empty($paymentRefField)) return;
		$db = JFactory::getDBO();
		$query = 'UPDATE #__hikashop_order ' .
								'SET  ' . $paymentRefField . ' = ' . $db->Quote($reference) . ' ' .
							'WHERE order_id = ' . $order_id;
		$db->setQuery($query);
		$db->query();
	}
	static function xmlAddress(&$params, &$user, &$order, $address_type, $element) {
		$address = $order->cart->$address_type;
		$xml = null;
		$xml .= '<address>';
		$xml .= '<firstName>' . htmlspecialchars($address->address_firstname) . '</firstName>';
		$xml .= '<lastName>' . htmlspecialchars($address->address_lastname) . '</lastName>';
		$xml .= '<street>' . htmlspecialchars($address->address_street) . '</street>';
		$field = $params->houseNoField;
		if (empty($field)) $house_no = null;
		else $house_no = trim(@$address->$field);
		$field = $params->houseNameField;
		if (empty($field)) $house_name = null;
		else $house_name = trim(@$address->$field);
		if (!empty($house_no)) {
			if (preg_match('/[^0-9]/', $house_no)) $house_name = trim($house_no . ' ' . $house_name);
			else $xml .= '<houseNumber>' . htmlspecialchars($house_no) . '</houseNumber>';
		}
		if (!empty($house_name)) $xml .= '<houseName>' . htmlspecialchars($house_name) . '</houseName>';
		$xml .= '<postalCode>' . htmlspecialchars($address->address_post_code) . '</postalCode>';
		$xml .= '<city>' . htmlspecialchars($address->address_city) . '</city>';
		$country_code = @$address->zone_code_2;
		if (empty($country_code)) $country_code = 'GB';
		$xml .= '<countryCode>' . $country_code . '</countryCode>';
		$xml .= '<telephoneNumber>' . htmlspecialchars(@$address->address_telephone) . '</telephoneNumber>';
		$xml .= '</address>';
		return '<' . $element . '>' . $xml . '</' . $element . '>';
	}
	static function xml2phpArray($xml){
		$arr = array();
		$iter = 0;
		foreach($xml->attributes() as $a => $b) $arr[$a] = (string)$b;
		foreach ($xml->children() as $b) {
			$a = $b->getName();
			$arr[$a][$iter] = self::xml2phpArray($b);
			$value = trim((string)$b);
			if (!empty($value)) $arr[$a][$iter][] = $value;
			$iter++;
		}
		return $arr;
	}
	static function encodeAttribute($attr) {
		$attr = str_replace('"', '%22', $attr);
		return str_replace('=', '%3D', $attr);
	}
	static function notificationURL(&$params, $locale=null) {
		global $Itemid;
	$url_itemid='';
	if(!empty($Itemid)){
		$url_itemid='&Itemid='.$Itemid;
	}
		return str_replace('http://', 'https://', HIKASHOP_LIVE) .
							 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=' . $params->payment_type . '&tmpl=component' .
							 ((empty($locale)) ? null : '&lang='.$locale).$url_itemid;
	}
	static function cancelButton(&$params, &$order, $label='Cancel Order') {
		$orderKey = @$params->adminCode . '^' . @$params->merchantCode . '^' . $order->order_number;
		$lang =& JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));
	echo '<div class="rbs-cancel">';
		echo '<a href="' . self::notificationURL($params, $locale) .
								'&orderKey=' . htmlspecialchars($orderKey) .
								'&paymentStatus=CANCELLED';
		if (!empty($params->macSecret)) echo '&mac=' . htmlspecialchars(self::calculateMAC($orderKey, null, null, 'CANCELLED', $params->macSecret));
		echo '">' . $label . '</a>';
		echo '</div>';
	}
	static function calculateMAC($orderKey, $paymentAmount, $paymentCurrency, $paymentStatus, $macSecret) {
		return md5($orderKey . $paymentAmount . $paymentCurrency . $paymentStatus . $macSecret);
	}
	static function parseCSS($source) {
		$source = str_replace('%SITEBASE%', HIKASHOP_LIVE, $source);
		return str_replace('%', '&#37;', $source);
	}
	static function worldpayHeader() {
		echo '<div id="rbs-worldpay-header">';
		echo '<div id="rbs-worldpay-about">';
		echo '<a title="About Worldpay - Opens in a new window"  target="_blank" href="http://www.worldpay.com"><img alt="RBS WorldPay Logo" style="border: 0px none;" src="/images/rbswp/logo.gif"></a>';
		echo '</div>';
		echo '<div id="rbs-worldpay-menu">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="Help - Opens in a new window" target="_blank" style="text-decoration:none;color:#002469;" href="/global3/brands/rbsworldpay/payment/default/help_en.html">Help</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="FAQs - Opens in a new window" target="_blank" style="text-decoration:none;color:#002469;" href="/global3/brands/rbsworldpay/payment/default/help_faqs_en.html">FAQs</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="Security - Opens in a new window" target="_blank" style="text-decoration:none;color:#002469;" href="/global3/brands/rbsworldpay/payment/default/help_security_en.html">Security</a>';
		echo '</div>';
		echo '</div>';
	}

	static function sendXML($payment_params, $xml) {
		$xml = '<?xml version="1.0"?>' .
					 '<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.wp3.worldpay.com/paymentService_v1.dtd">' .
					 '<paymentService version="' . self::getVersion() . '" merchantCode="' . $payment_params->merchantCode . '">' .
					 $xml .
					 '</paymentService>';
		if (!empty($payment_params->showVars)) {
			echo '<div>';
			echo str_replace("\n", '<br/>',
														htmlspecialchars(str_replace(']]>', "]]>\n",
														str_replace('<![CDATA[', "<![CDATA[\n",
														preg_replace('/(<[^\/>]*>)/', "\n$1", $xml)))));
			echo '</div>';
			echo '<hr/>';
			echo 'Sending to: ' . $payment_params->xmlurl;
			echo '<hr/>';
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $payment_params->xmlurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$xmlResult = curl_exec($ch);
		if ( curl_errno($ch) ) {
			self::raiseError($payment_params->showVars, 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch));
			return null;
		}
		$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		 switch($returnCode){
			case 200:
				break;
			case 401:
				self::raiseError($payment_params->showVars, 'ERROR -> Merchant code/password error.');
				return null;
			default:
				self::raiseError($payment_params->showVars, 'HTTP ERROR -> ' . $returnCode);
				return null;
		}
		return $xmlResult;
	}

	static function getOrderPaymentResponse($payment_params, $order_number) {
		$xml = '<inquiry><orderInquiry orderCode="' . $order_number . '"/></inquiry>';
		$xmlResult = self::sendXML($payment_params, $xml);
		if (!empty($xmlResult)) {
			$xmlElement = new SimpleXMLElement($xmlResult);
			$xmlArray = self::xml2phpArray($xmlElement);
			if (!empty($payment_params->showVars)) self::showXMLReply($xmlArray);
			if (!rbsglobalgateway_helper::validService($xmlArray, $payment_params)) return null;
			$orderStatus = $xmlArray['reply'][0]['orderStatus'][0];
			if ($orderStatus['orderCode'] != $order_number) {
				rbsglobalgateway_helper::raiseError($payment_params->showVars, 'ERROR -> Order ID mismatch.');
				return NULL;
			}
		}
		return $xmlResult;
	}

	static function getVersion() {
		return '1.4';
	}

	static function showXMLReply($xmlArray) {
		echo 'Replied with: ';
		echo '<hr/>';
		echo '<pre>';
		print_r($xmlArray);
		echo '</pre>';
		echo '<hr/>';
	}

	static function validService($xmlArray, $payment_params) {
		if ($xmlArray['version'] != self::getVersion()) {
			self::raiseError($payment_params->showVars, 'ERROR -> Version mismatch.');
			return false;
		}
		if ($xmlArray['merchantCode'] != $payment_params->merchantCode) {
			self::raiseError($payment_params->showVars, 'ERROR -> Merchant code mismatch.');
			return false;
		}
		return true;
	}
}