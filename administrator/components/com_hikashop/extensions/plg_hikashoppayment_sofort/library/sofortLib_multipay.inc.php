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
class SofortLib_Multipay extends SofortLib_Abstract {

	protected $_parameters = array();

	protected $_response = array();

	protected $_xmlRootTag = 'multipay';

	private $_paymentMethods = array();

	private $_transactionId = '';

	private $_paymentUrl = '';


	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		$this->_parameters['project_id'] = $projectId;
	}


	public function setLanguageCode($arg) {
		$this->_parameters['language_code'] = $arg;
		return $this;
	}


	public function setTimeout($arg) {
		$this->_parameters['timeout'] = $arg;
		return $this;
	}


	public function setEmailCustomer($arg) {
		$this->_parameters['email_customer'] = $arg;
		return $this;
	}


	public function setPhoneNumberCustomer($arg) {
		$this->_parameters['phone_customer'] = $arg;
		return $this;
	}


	public function addUserVariable($arg) {
		$this->_parameters['user_variables']['user_variable'][] = $arg;
		return $this;
	}


	public function setSenderAccount($bankCode, $accountNumber, $holder) {
		$this->_parameters['sender'] = array(
			'bank_code' => $bankCode,
			'account_number' => $accountNumber,
			'holder' => $holder,
		);
		return $this;
	}


	public function setAmount($arg, $currency = 'EUR') {
		$this->_parameters['amount'] = $arg;
		$this->_parameters['currency_code'] = $currency;
		return $this;
	}


	public function setReason($arg, $arg2 = '') {
		$arg = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg);
		$arg = substr($arg, 0, 27);
		$arg2 = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg2);
		$arg2 = substr($arg2, 0, 27);
		$this->_parameters['reasons']['reason'][0] = $arg;
		$this->_parameters['reasons']['reason'][1] = $arg2;
		return $this;
	}


	public function setSuccessLinkRedirect($arg) {
		$this->_parameters['success_link_redirect'] = $arg;
	}


	public function setSuccessUrl($successUrl, $redirect = true) {
		$this->_parameters['success_url'] = $successUrl;
		$this->setSuccessLinkRedirect($redirect);
		return $this;
	}


	public function setAbortUrl($arg) {
		$this->_parameters['abort_url'] = $arg;
		return $this;
	}


	public function setTimeoutUrl($arg) {
		$this->_parameters['timeout_url'] = $arg;
		return $this;
	}


	public function setNotificationUrl($arg) {
		$this->_parameters['notification_urls']['notification_url'] = array($arg);
		return $this;
	}


	public function addNotificationUrl($arg) {
		$this->_parameters['notification_urls']['notification_url'][] = $arg;
		return $this;
	}


	public function setNotificationEmail($arg) {
		$this->_parameters['notification_emails']['notification_email'] = array($arg);
		return $this;
	}


	public function addNotificationEmail($arg) {
		$this->_parameters['notification_emails']['notification_email'][] = $arg;
		return $this;
	}


	public function setVersion($arg) {
		$this->_parameters['interface_version'] = $arg;
		return $this;
	}


	public function setSofortueberweisung($amount = '') {
		$this->_paymentMethods[] = 'su';

		if (!array_key_exists('su', $this->_parameters) || !is_array($this->_parameters['su'])) {
			$this->_parameters['su'] = array();
		}

		if (!empty($amount)) {
			$this->_parameters['su']['amount'] = $amount;
		}

		return $this;
	}


	public function setSofortueberweisungCustomerprotection($customerProtection = true) {
		$this->_paymentMethods[] = 'su';

		if (!array_key_exists('su', $this->_parameters) || !is_array($this->_parameters['su'])) {
			$this->_parameters['su'] = array();
		}

		$this->_parameters['su']['customer_protection'] = $customerProtection ? 1 : 0;
		return $this;
	}


	public function setSofortlastschrift($amount = '') {
		$this->_paymentMethods[] = 'sl';

		if (!array_key_exists('sl', $this->_parameters) || !is_array($this->_parameters['sl'])) {
			$this->_parameters['sl'] = array();
		}

		if (!empty($amount)) {
			$this->_parameters['sl']['amount'] = $amount;
		}

		return $this;
	}


	public function setSofortlastschriftAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE') {
		$this->_parameters['sl']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['sl']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['sl']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['sl']['invoice_address']['street'] = $street;
		$this->_parameters['sl']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['sl']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['sl']['invoice_address']['city'] = $city;
		$this->_parameters['sl']['invoice_address']['country_code'] = $country;
		return $this;
	}


	public function setLastschrift($amount = '') {
		$this->_paymentMethods[] = 'ls';

		if (!array_key_exists('ls', $this->_parameters) || !is_array($this->_parameters['ls'])) {
			$this->_parameters['ls'] = array();
		}

		if (!empty($amount)) {
			$this->_parameters['ls']['amount'] = $amount;
		}

		return $this;
	}


	public function setLastschriftBaseCheckDisabled() {
		$this->_parameters['ls']['base_check_disabled'] = 1;
		return $this;
	}


	public function setLastschriftExtendedCheckDisabled() {
		$this->_parameters['ls']['extended_check_disabled'] = 1;
		return $this;
	}


	public function setLastschriftMobileCheckDisabled() {
		$this->_parameters['ls']['mobile_check_disabled'] = 1;
		return $this;
	}


	public function setLastschriftAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE') {
		$this->_parameters['ls']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['ls']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['ls']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['ls']['invoice_address']['street'] = $street;
		$this->_parameters['ls']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['ls']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['ls']['invoice_address']['city'] = $city;
		$this->_parameters['ls']['invoice_address']['country_code'] = $country;
		return $this;
	}


	public function setSofortrechnung() {
		$this->_paymentMethods[] = 'sr';

		if (!array_key_exists('sr', $this->_parameters) || !is_array($this->_parameters['sr'])) {
			$this->_parameters['sr'] = array();
		}

		return $this;
	}


	public function setSofortvorkasse($amount = '') {
		$this->_paymentMethods[] = 'sv';

		if (!array_key_exists('sv', $this->_parameters) || !is_array($this->_parameters['sv'])) {
			$this->_parameters['sv'] = array();
		}

		if (!empty($amount)) {
			$this->_parameters['sv']['amount'] = $amount;
		}

		return $this;
	}


	public function setSofortvorkasseCustomerprotection($customerProtection = true) {
		$this->_paymentMethods[] = 'sv';

		if (!array_key_exists('sv', $this->_parameters) || !is_array($this->_parameters['sv'])) {
			$this->_parameters['sv'] = array();
		}

		$this->_parameters['sv']['customer_protection'] = $customerProtection ? 1 : 0;
		return $this;
	}


	public function setSofortrechnungCustomerId($arg) {
		$this->_parameters['sr']['customer_id'] = $arg;
		return $this;
	}


	public function setSofortrechnungOrderId($arg) {
		$this->_parameters['sr']['order_id'] = $arg;
		return $this;
	}


	public function setDebitorVatNumber($vatNumber) {
		$this->_parameters['sr']['debitor_vat_number'] = $vatNumber;
		return $this;
	}


	public function setSofortrechnungInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->_parameters['sr']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['sr']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['sr']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['sr']['invoice_address']['street'] = $street;
		$this->_parameters['sr']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['sr']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['sr']['invoice_address']['city'] = $city;
		$this->_parameters['sr']['invoice_address']['country_code'] = $country;
		$this->_parameters['sr']['invoice_address']['name_additive'] = $nameAdditive;
		$this->_parameters['sr']['invoice_address']['street_additive'] = $streetAdditive;
		$this->_parameters['sr']['invoice_address']['company'] = $companyName;
		return $this;
	}


	public function setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->_parameters['sr']['shipping_address']['salutation'] = $salutation;
		$this->_parameters['sr']['shipping_address']['firstname'] = $firstname;
		$this->_parameters['sr']['shipping_address']['lastname'] = $lastname;
		$this->_parameters['sr']['shipping_address']['street'] = $street;
		$this->_parameters['sr']['shipping_address']['street_number'] = $streetNumber;
		$this->_parameters['sr']['shipping_address']['zipcode'] = $zipcode;
		$this->_parameters['sr']['shipping_address']['city'] = $city;
		$this->_parameters['sr']['shipping_address']['country_code'] = $country;
		$this->_parameters['sr']['shipping_address']['name_additive'] = $nameAdditive;
		$this->_parameters['sr']['shipping_address']['street_additive'] = $streetAdditive;
		$this->_parameters['sr']['shipping_address']['company'] = $companyName;
		return $this;
	}


	public function addSofortrechnungItem($itemId, $productNumber, $title, $unitPrice, $productType = 0, $description = '', $quantity = 1, $tax = 19) {
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);

		if (empty($title)) {
			$this->setError('Title must not be empty. Title: '.$title.', Productnumber: '.$productNumber.', Unitprice: '.$unitPrice.', Quantity: '.$quantity.', Description: '.$description);
		}

		$this->_parameters['sr']['items']['item'][] = array(
			'item_id' => $itemId,
			'product_number' => $productNumber,
			'product_type' => $productType,
			'title' => $title,
			'description' => $description,
			'quantity' => $quantity,
			'unit_price' => $unitPrice,
			'tax' => $tax,
		);
	}


	public function setSofortrechungComment($comment) {
		$this->_parameters['sr']['items']['comment'] = $comment;
	}


	public function removeSofortrechnungItem($itemId) {
		$i = 0;

		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item['item_id']) && $item['item_id'] == $itemId) {
				unset($this->_parameters['sr']['items']['item'][$i]);
				return true;
			}

			$i++;
		}

		return false;
	}


	public function updateSofortrechnungItem($itemId, $quantity, $unitPrice) {
		$i = 0;

		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item[$i]['item_id']) && $item[$i]['item_id'] == $itemId) {
				$this->_parameters['sr']['items']['item'][$i]['quantity'] = $quantity;
				$this->_parameters['sr']['items']['item'][$i]['unit_price'] = $unitPrice;
				return true;
			}

			$i++;
		}

		return false;
	}


	public function getSofortrechnungItemAmount($itemId) {
		$i = 0;

		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item['item_id']) && $item['item_id'] == $itemId) {
				return $this->_parameters['sr']['items']['item'][$i]['quantity'] * $this->_parameters['sr']['items']['item'][$i]['unit_price'];
			}

			$i++;
		}
	}


	public function setSofortrechnungTimeForPayment($arg) {
		$this->_parameters['sr']['time_for_payment'] = $arg;
		return $this;
	}


	public function sendValidationRequest() {
		$this->_validateOnly = true;
		$this->sendRequest();
		return isset($this->_response['validation']['status']['@data']) ? true : false;
	}


	public function getSofortrechnungItem($itemId) {
		return $this->_parameters['sr']['items'][$itemId];
	}


	public function getSofortrechnungItems() {
		return $this->_parameters['sr']['items'];
	}


	public function getPaymentUrl() {
		$this->_paymentUrl = isset($this->_response['new_transaction']['payment_url']['@data'])
			? $this->_response['new_transaction']['payment_url']['@data']
			: false;
		return $this->_paymentUrl;
	}


	public function getPaymentMethod($i = 0) {
		if ($i < 0 || $i >= count($this->_paymentMethods)) {
			return false;
		}

		return $this->_paymentMethods[$i];
	}


	public function isSofortueberweisung() {
		return array_key_exists('su', $this->_parameters);
	}


	public function isSofortvorkasse() {
		return array_key_exists('sv', $this->_parameters);
	}


	public function isSofortlastschrift() {
		return array_key_exists('sl', $this->_parameters);
	}


	public function isLastschrift() {
		return array_key_exists('ls', $this->_parameters);
	}


	public function isSofortrechnung() {
		return array_key_exists('sr', $this->_parameters);
	}


	public function isConsumerProtection($product) {
		if (in_array($product, array('su', 'sv'))) {
			if(isset($this->_parameters[$product]['customer_protection'])) {
				return $this->_parameters[$product]['customer_protection'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function isDebitpayCheckDisabled($product, $check) {
		if (in_array($product, array('ls', 'sl')) && in_array($check, array('base_check_disabled', 'extended_check_disabled', 'mobile_check_disabled'))) {
			return $this->_parameters[$product][$check];
		} else {
			return false;
		}
	}


	public function getTransactionId() {
		return $this->_transactionId;
	}


	protected function _parseXml() {
		$this->_transactionId = isset($this->_response['new_transaction']['transaction']['@data'])
			? $this->_response['new_transaction']['transaction']['@data']
			: false;
	}
}
?>
