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


class PnagInvoice extends PnagAbstractDocument {

	const PENDING_CONFIRM_INVOICE = 4195329;
	const LOSS_CANCELED = 4194824;
	const LOSS_CONFIRMATION_PERIOD_EXPIRED = 4196360;

	const PENDING_NOT_CREDITED_YET_PENDING = 32785;
	const PENDING_NOT_CREDITED_YET_REMINDER_1 = 65553;
	const PENDING_NOT_CREDITED_YET_REMINDER_2 = 131089;
	const PENDING_NOT_CREDITED_YET_REMINDER_3 = 262161;
	const PENDING_NOT_CREDITED_YET_DELCREDERE = 524305;

	const RECEIVED_CREDITED_PENDING = 33026;
	const RECEIVED_CREDITED_REMINDER_1 = 65794;
	const RECEIVED_CREDITED_REMINDER_2 = 131330;
	const RECEIVED_CREDITED_REMINDER_3 = 262402;
	const RECEIVED_CREDITED_DELCREDERE = 524546;


	const REFUNDED_REFUNDED_PENDING = 32836;
	const REFUNDED_REFUNDED_RECEIVED = 2097220;
	const REFUNDED_REFUNDED_REMINDER_1 = 65604;
	const REFUNDED_REFUNDED_REMINDER_2 = 131140;
	const REFUNDED_REFUNDED_REMINDER_3 = 262212;
	const REFUNDED_REFUNDED_DELCREDERE = 524356;


	const REFUNDED_REFUNDED_REFUNDED = 1048644;
	const PENDING_NOT_CREDITED_YET_RECEIVED = 2097169;
	const RECEIVED_CREDITED_RECEIVED = 2097410;


	public $SofortLib_Multipay = null;

	public $SofortLib_TransactionData = null;

	public $ConfirmSr = null;

	public $EditSr = null;

	public $CancelSr = null;

	protected $_items = array();

	private $_statusMask = array(
		'status'=>
			array(
				'pending' => 1,
				'received' => 2,
				'refunded' => 4,
				'loss' => 8,
			),
		'status_reason' =>
			array(
				'not_credited_yet' => 16,
				'not_credited' => 32,
				'refunded' => 64,
				'compensation' => 128,
				'credited' => 256,
				'canceled' => 512,
				'confirm_invoice' => 1024,
				'confirmation_period_expired' => 2048,
				'wait_for_money' => 4096,
				'reversed' => 8192,
				'rejected' => 16384,
			),
		'invoice_status' =>
			array(
				'pending' => 32768,
				'reminder_1' => 65536,
				'reminder_2' => 131072,
				'reminder_3' => 262144,
				'delcredere' => 524288,
				'refunded' => 1048576,
				'received' => 2097152,
				'empty' => 4194304,
		)
	);

	private $_status = '';

	private $_status_reason = '';

	private $_invoice_status = '';

	private $_invoice_objection = '';

	private $_language_code = '';

	private $_transactionId = '';

	private $_configKey = '';

	private $_apiUrl = '';

	private $_time = '';

	private $_payment_method = '';

	private $_invoiceUrl = '';


	public function __construct($configKey, $transactionId = '') {
		$this->_transactionId = $transactionId;
		$this->_configKey = $configKey;
		$this->_apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		$this->SofortLib_Multipay = new SofortLib_Multipay($this->_configKey, $this->_apiUrl);

		if ($transactionId != '') {
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			$this->ConfirmSr = $this->_setupConfirmSr();
		}

		return $this;
	}


	public function getConstantById($id) {
		$Object = new ReflectionClass(__CLASS__);
		$constants = array_flip($Object->getConstants());
		return (array_key_exists($id, $constants)) ? $constants[$id] : 0;
	}


	public function getConstantByName($name) {
		$Object = new ReflectionClass(__CLASS__);
		$constants = $Object->getConstants();
		return (array_key_exists($name, $constants)) ? $constants[$name] : 0;
	}


	public function setBitmask($status, $statusReason, $invoiceStatus) {
		$this->_status = $status;
		$this->_status_reason = $statusReason;
		$this->_invoice_status = $invoiceStatus;
		$string = $this->_status.' - '.$this->_status_reason.' - '.$this->_invoice_status;
		return $string.' -> '.$this->_calcInvoiceStatusCode()."\n";
	}


	public function setState($state, $callback = '') {
		$this->_state = $state;

		if ($callback != '') {
			call_user_func($callback);
		}

		return $this;
	}


	public function getState() {
		return $this->_state;
	}


	public function setTransactionId($transactionId) {
		$this->_transactionId = $transactionId;
		$this->SofortLib_TransactionData = $this->_setupTransactionData();
		$this->ConfirmSr = $this->_setupConfirmSr();
		return $this;
	}


	private function _setupTransactionData() {
		$SofortLib_TransactionData = new SofortLib_TransactionData($this->_configKey, $this->_apiUrl);
		$SofortLib_TransactionData->setTransaction($this->_transactionId);
		$SofortLib_TransactionData->sendRequest();

		if (!$SofortLib_TransactionData->getCount()) {
			return false;
		}

		$this->setStatus($SofortLib_TransactionData->getStatus());
		$this->setStatusReason($SofortLib_TransactionData->getStatusReason());
		$this->setStatusOfInvoice($SofortLib_TransactionData->getInvoiceStatus());
		$this->setInvoiceObjection($SofortLib_TransactionData->getInvoiceObjection());
		$this->setLanguageCode($SofortLib_TransactionData->getLanguageCode());
		$this->setTransaction($this->getTransactionId());
		$this->setTime($SofortLib_TransactionData->getTime());
		$this->setPaymentMethod($SofortLib_TransactionData->getPaymentMethod());
		$this->setInvoiceUrl($SofortLib_TransactionData->getInvoiceUrl());
		$this->setAmount($SofortLib_TransactionData->getAmount());
		$this->setAmountRefunded($SofortLib_TransactionData->getAmountRefunded());
		$itemArray = $SofortLib_TransactionData->getItems();

		$this->_items = array();

		if (is_array($itemArray) && !empty($itemArray)) {
			foreach ($itemArray as $item) {
				$this->setItem($item['item_id'], $item['product_number'], $item['product_type'], $item['title'], $item['description'], $item['quantity'], $item['unit_price'], $item['tax']);
				$this->_amount += ($item['unit_price'] * $item['quantity']);
			}
		}
		$this->setState($this->_calcInvoiceStatusCode());
		return $SofortLib_TransactionData;
	}


	public function setSofortLibMultipay($SofortLib_Multipay) {
		$this->SofortLib_Multipay = $SofortLib_Multipay;
	}


	public function setSofortLibTransactionData($SofortLib_TransactionData) {
		$this->SofortLib_TransactionData = $SofortLib_TransactionData;
	}


	public function setSofortLibEditSr($SofortLib_EditSr) {
		$this->EditSr = $SofortLib_EditSr;
	}


	public function setSofortLibCancelSr($SofortLib_CancelSr) {
		$this->CancelSr = $SofortLib_CancelSr;
	}


	private function _setupConfirmSr() {
		$SofortLib_ConfirmSr = new SofortLib_ConfirmSr($this->_configKey);
		$SofortLib_ConfirmSr->setTransaction($this->_transactionId);
		return $SofortLib_ConfirmSr;
	}


	private function _setupEditSr() {
		$SofortLib_EditSr = new SofortLib_EditSr($this->_configKey);
		$SofortLib_EditSr->setTransaction($this->_transactionId);
		return $SofortLib_EditSr;
	}


	private function _setupCancelSr() {
		$SofortLib_CancelSr = new SofortLib_CancelSr($this->_configKey);
		$SofortLib_CancelSr->setTransaction($this->_transactionId);
		return $SofortLib_CancelSr;
	}


	public function refreshTransactionData() {
		$this->SofortLib_TransactionData = $this->_setupTransactionData();
		return true;
	}


	public function cancelInvoice($transactionId = '', $creditNoteNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->CancelSr = $this->_setupCancelSr();
		}

		if ($this->CancelSr != null) {
			unset($this->_items);
			$this->CancelSr->cancelInvoice();
			$this->CancelSr->setComment('Vollstorno');
			$creditNoteNumber && $this->CancelSr->setCreditNoteNumber($creditNoteNumber);
			$this->CancelSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}

		return false;
	}


	public function confirmInvoice($transactionId = '', $invoiceNumber = '', $customerNumber = '', $orderNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->ConfirmSr = $this->_setupConfirmSr();
		}

		if ($this->ConfirmSr != null) {
			$this->ConfirmSr->confirmInvoice();
			$invoiceNumber && $this->ConfirmSr->setInvoiceNumber($invoiceNumber);
			$customerNumber && $this->ConfirmSr->setCustomerNumber($customerNumber);
			$orderNumber && $this->ConfirmSr->setOrderNumber($orderNumber);
			$this->ConfirmSr->setApiVersion('2.0');
			$this->ConfirmSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}

		return false;
	}


	public function updateInvoice($transactionId, $items, $comment, $invoiceNumber = '', $customerNumber = '', $orderNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->EditSr = $this->_setupEditSr();
		}

		if ($this->EditSr != null) {
			$this->EditSr->setComment($comment);
			$invoiceNumber && $this->EditSr->setInvoiceNumber($invoiceNumber);
			$customerNumber && $this->EditSr->setCustomerNumber($customerNumber);
			$orderNumber && $this->EditSr->setOrderNumber($orderNumber);
			$this->EditSr->updateCart($items);
			$this->EditSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}

		return false;
	}

	public function updateOrderNumber($transactionId, $orderNumber) {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->EditSr = $this->_setupEditSr();
		}

		if ($this->EditSr != null) {
			$orderNumber && $this->EditSr->setOrderNumber($orderNumber);
			$this->EditSr->sendRequest();
		}
	}



	public function addItemToInvoice($itemId, $productNumber, $title, $unitPrice, $productType = 0, $description = '', $quantity = 1, $tax = 19) {
		$unitPrice = round($unitPrice, 2);	// round all prices to two decimals
		$this->SofortLib_Multipay->addSofortrechnungItem($itemId, $productNumber, $title, $unitPrice, $productType, $description, $quantity, $tax);
		$this->setItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax);
		$this->_amount += ($quantity * $unitPrice);
		$this->setAmount($this->_amount, $this->_currency);
	}


	public function removeItemfromInvoice($itemId) {
		$return = false;
		$i = 0;

		foreach ($this->_items as $item) {
			if ($item->itemId == $itemId) {
				$this->setAmount($this->getAmount() - $this->getItemAmount($itemId));
				$return = $this->SofortLib_Multipay->removeSofortrechnungItem($itemId);
			}

			$i++;
		}

		return $return;
	}


	public function updateInvoiceItem($itemId, $quantity, $unitPrice) {
		$return = false;
		foreach ($this->_items as $item) {
			if ($item->itemId == $itemId) {
				$oldPrice = $item->unitPrice * $item->quantity;
				$item->uniPrice = $unitPrice;
				$item->quantity = $quantity;
				$newPrice = $unitPrice * $quantity;
				$this->setAmount($this->getAmount() - $oldPrice + $newPrice);
				$return = $this->SofortLib_Multipay->updateSofortrechnungItem($itemId, $quantity, $unitPrice);
			}
		}

		return $return;
	}


	public function getItemAmount($itemId) {
		return $this->SofortLib_Multipay->getSofortrechnungItemAmount($itemId);
	}


	public function addShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->SofortLib_Multipay->setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive, $companyName);
	}


	public function addShippingAddresss($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '') {
		$this->SofortLib_Multipay->setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive);
	}


	public function addInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->SofortLib_Multipay->setSofortrechnungInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive, $companyName);
	}


	public function setOrderId($arg) {
		$this->SofortLib_Multipay->setSofortrechnungOrderId($arg);
	}


	public function setCustomerId($arg) {
		$this->SofortLib_Multipay->setSofortrechnungCustomerId($arg);
	}


	public function setPhoneNumberCustomer($arg) {
		$this->SofortLib_Multipay->setPhoneNumberCustomer($arg);
	}


	public function setEmailCustomer($arg) {
		$this->SofortLib_Multipay->setEmailCustomer($arg);
	}


	public function addUserVariable($arg) {
		$this->SofortLib_Multipay->addUserVariable($arg);
	}


	public function setNotificationUrl($arg) {
		$this->SofortLib_Multipay->setNotificationUrl($arg);
	}


	public function setAbortUrl($arg) {
		$this->SofortLib_Multipay->setAbortUrl($arg);
	}


	public function setSuccessUrl($arg) {
		$this->SofortLib_Multipay->setSuccessUrl($arg);
	}


	public function setTimeoutUrl($arg) {
		$this->SofortLib_Multipay->setTimeoutUrl($arg);
	}


	public function setTimeout($arg) {
		$this->SofortLib_Multipay->setTimeout($arg);
	}


	public function setReason($reason1, $reason2 = '') {
		$this->SofortLib_Multipay->setReason($reason1, $reason2);
	}


	public function setAmount($arg, $currency = 'EUR') {
		$this->SofortLib_Multipay->setAmount($arg, $currency);
	}


	public function getAmount() {
		if (isset($this->SofortLib_TransactionData) && $this->SofortLib_TransactionData instanceof  SofortLib_TransactionData) {
			$amount = $this->SofortLib_TransactionData->getAmount();
		} else {
			$amount = $this->_amount;
		}

		if ($amount != 0.00) {
			return $amount;
		} elseif (isset($this->_amount) && $this->_amount != 0.00) {
			return $this->_amount;	// TODO: check
		}

		return 0.0;
	}


	public function setAmountRefunded($arg) {
		$this->_amountRefunded = $arg;
	}


	public function getAmountRefunded() {
		return $this->_amountRefunded;
	}


	public function setSofortrechnung() {
		$this->SofortLib_Multipay->setSofortrechnung();
	}


	public function setDebitorVatNumber($vatNumber) {
		$this->SofortLib_Multipay->setDebitorVatNumber($vatNumber);
	}


	public function getPaymentUrl() {
		return $this->SofortLib_Multipay->getPaymentUrl();
	}


	public function getInvoiceNumber() {
		return $this->SofortLib_TransactionData->getInvoiceNumber();
	}


	public function getCustomerNumber() {
		return $this->SofortLib_TransactionData->getCustomerNumber();
	}


	public function getOrderNumber() {
		if ($this->SofortLib_TransactionData instanceof SofortLib_TransactionData) {
			return $this->SofortLib_TransactionData->getOrderNumber();
		}
		return false;
	}


	public function getInvoiceTye() {
		return $this->SofortLib_TransactionData->getInvoiceType();
	}


	public function getTransactionId() {
		if ($this->SofortLib_Multipay instanceof SofortLib_Multipay && $transactionId = $this->SofortLib_Multipay->getTransactionId()) {
			return $transactionId;
		} elseif ($this->SofortLib_TransactionData instanceof SofortLib_TransactionData && $transactionId = $this->SofortLib_TransactionData->getTransaction()) {
			return $transactionId;
		} else {
			return $this->_transactionId;
		}
	}




	public function checkout() {
		$this->SofortLib_Multipay->sendRequest();
		$this->_transactionId = $this->SofortLib_Multipay->getTransactionId();	// set the resulting transaction id
		$this->SofortLib_TransactionData = $this->_setupTransactionData();

		$errors = array();

		if ($this->isError()) {
			$errors = $this->getErrors();
		}

		$warnings = array();

		if ($this->isWarning()) {
			$warnings = $this->getWarnings();
		}

		if (!empty($errors) && !empty($warnings)) {
			return array(); //no errors or warnings found
		} else {
			$returnArray = array();
			$returnArray['errors'] = $errors;
			$returnArray['warnings'] = $warnings;
			return $returnArray;
		}
	}


	public function getTransactionInfo() {
		if (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->setTransaction($this->transactionId);
			$this->sendRequest();
			return $this->SofortLib_TransactionData;
		} else {
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
		}

		return array();
	}



	public function getInvoice() {
		$errorCode = $this->getHttpResponseCode($this->_invoiceUrl);

		if (!in_array($errorCode, array('200', '301', '302'))) {
			return false;
		}

		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="invoice.pdf"');
		echo $this->handleDownload($this->getInvoiceDownloadMethod());
	}


	public function handleDownload($method = 'socket') {
		switch ($method) {
			case 'file_get_contents':
				return file_get_contents($this->_invoiceUrl);
				break;
			case 'curl':
				return $this->handleCurlDownload();
				break;
			default:
				return $this->handleSocketDownload();
				break;
		}
	}


	public function getInvoiceDownloadMethod() {
		if (ini_get('allow_url_fopen')) {
			$method = 'file_get_contents';
		} elseif (function_exists('curl_init')) {
			$method = 'curl';
		} else {
			$method = 'socket';
		}
		return $method;
	}


	private function handleCurlDownload() {
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $this->_invoiceUrl);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $buffer;
	}


	private function handleSocketDownload() {
		$uri = parse_url($this->_invoiceUrl);
		$host = $uri['host'];
		$path = $uri['path'];
		$handle = $this->openSocket($host);
		$header = $this->makeHeader('GET', $path, $host);
		fwrite($handle, $header);
		$buffer = null;

		while (!feof($handle)) {
			$buffer .= fgets($handle, 8192);
		}

		fclose($handle);
		return $buffer;
	}


	public function getHttpResponseCode() {
		$uri = parse_url($this->_invoiceUrl);
		$host = $uri['host'];
		$path = $uri['path'];
		$handle = $this->openSocket($host);
		$header = $this->makeHeader('HEAD', $path, $host);
		fwrite($handle, $header);
		$buffer = null;

		while(!feof($handle)) {
			$buffer .= fgets($handle, 16);
		}

		fclose($handle);
		$httpCode = substr($buffer, 9, 3);
		return (int)$httpCode;
	}


	private function openSocket($host) {
		if (!$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr, 15)) {
			return false;
		}

		return $fp;
	}


	private function makeHeader($action, $path, $host) {
		$header = $action." ".$path." HTTP/1.1\r\n";
		$header .= 'Host: '.$host."\r\n";
		$header .= "User-Agent: SOFORTLib \r\n";
		return $header .= "Connection: Close\r\n\r\n";
	}


	public function setInvoiceUrl($invoiceUrl) {
		$this->_invoiceUrl = $invoiceUrl;
		return $this;
	}


	public function getInvoiceUrl() {
		return $this->_invoiceUrl;
	}


	public function setStatus($status) {
		$this->_status = $status;
		return $this;
	}


	public function setStatusReason($statusReason) {
		$this->_status_reason = $statusReason;
		return $this;
	}


	public function setStatusOfInvoice($invoiceStatus = '') {
		$this->_invoice_status = !empty($invoiceStatus) ? $invoiceStatus : 'empty';
		return $this;
	}


	public function setLanguageCode($languageCode = '') {
		$this->_language_code = !empty($languageCode) ? $languageCode : 'en';
		return $this;
	}


	public function setTransaction($transaction) {
		$this->_transaction = $transaction;
		return $this;
	}


	public function setTime($time) {
		$this->_time = $time;
		return $this;
	}


	public function setVersion($arg) {
		$this->SofortLib_Multipay->setVersion($arg);
	}


	public function setPaymentMethod($paymentMethod) {
		$this->_payment_method = $paymentMethod;
		return $this;
	}


	public function setInvoiceObjection($invoiceObjection) {
		$this->_invoice_objection = $invoiceObjection;
		return $this;
	}


	public function setInvoiceStatus($invoiceStatus) {
		$this->_invoice_status = $invoiceStatus;
		return $this;
	}


	public function getInvoiceObjection() {
		return $this->_invoice_objection;
	}


	public function getStatusOfInvoice() {
		return $this->_invoice_status;
	}


	public function getInvoiceStatus() {
		return $this->_calcInvoiceStatusCode();
	}


	private function _calcInvoiceStatusCode() {
		return $this->_statusMask['status'][$this->_status]
			| $this->_statusMask['status_reason'][$this->_status_reason]
			| $this->_statusMask['invoice_status'][$this->_invoice_status];
	}


	public function getPaymentMethod() {
		return $this->_payment_method;
	}


	public function getStatusReason() {
		return $this->_status_reason;
	}


	public function getStatus() {
		return $this->_status;
	}


	public function getLanguageCode() {
		return $this->_language_code;
	}


	public function getItems() {
		return $this->_items;
	}


	public function setItems($items) {
		$this->_items = $items;
	}


	public function getTransactionData() {
		if ($this->SofortLib_TransactionData) {
			return $this->SofortLib_TransactionData;
		} else {
			return false;
		}
	}


	public function isError() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				return true;
			}
		}

		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				return true;
			}
		}

		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				return true;
			}
		}

		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				return true;
			}
		}


		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				return true;
			}
		}

		return false;
	}


	public function isWarning() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isWarning('sr')) {
				return true;
			}
		}

		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isWarning('sr')) {
				return true;
			}
		}

		if ($this->EditSr) {
			if ($this->EditSr->isWarning('sr')) {
				return true;
			}
		}

		if ($this->CancelSr) {
			if ($this->CancelSr->isWarning('sr')) {
				return true;
			}
		}

		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isWarning('sr')) {
				return true;
			}
		}

		return false;
	}


	public function getError() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				return $this->SofortLib_Multipay->getError('sr');
			}
		}

		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				return $this->ConfirmSr->getError('sr');
			}
		}

		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				return $this->EditSr->getError('sr');
			}
		}

		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				return $this->CancelSr->getError('sr');
			}
		}

		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				return $this->SofortLib_TransactionData->getError('sr');
			}
		}

		return '';
	}


	public function getErrors() {
		$allErrors = array();

		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				$allErrors = array_merge($this->SofortLib_Multipay->getErrors('sr'), $allErrors);
			}
		}

		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				$allErrors = array_merge($this->ConfirmSr->getErrors('sr'), $allErrors);
			}
		}

		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				$allErrors = array_merge($this->EditSr->getErrors('sr'), $allErrors);
			}
		}

		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				$allErrors = array_merge($this->CancelSr->getErrors('sr'), $allErrors);
			}
		}

		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				$allErrors = array_merge($this->SofortLib_TransactionData->getErrors('sr'), $allErrors);
			}
		}

		return $allErrors;
	}


	public function getErrorCodes($detailLevel = 0) {
		$errors = $this->getErrors();

		if (empty($errors)) return array();

		$errorCodes = array();

		foreach($errors as $error) {

			if ($detailLevel === 0) {
				array_push($errorCodes, $error['code']);
			} elseif ($detailLevel === 1) {
				array_push($errorCodes, array(
										'code' => $error['code'],
										'message' => $error['message'],
										'field' => $error['field'],
				));
			}

		}

		return $errorCodes;
	}


	public function getWarnings() {
		$allWarnings = array();

		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isWarning('sr')) {
				$allWarnings = array_merge($this->SofortLib_Multipay->getWarnings('sr'), $allWarnings);
			}
		}

		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isWarning('sr')) {
				$allWarnings = array_merge($this->ConfirmSr->getWarnings('sr'), $allWarnings);
			}
		}

		if ($this->EditSr) {
			if ($this->EditSr->isWarning('sr')) {
				$allErrors = array_merge($this->EditSr->getWarnings('sr'), $allErrors);
			}
		}

		if ($this->CancelSr) {
			if ($this->CancelSr->isWarning('sr')) {
				$allErrors = array_merge($this->CancelSr->getWarnings('sr'), $allErrors);
			}
		}

		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isWarning('sr')) {
				$allWarnings = array_merge($this->SofortLib_TransactionData->getWarnings('sr'), $allWarnings);
			}
		}

		return $allWarnings;
	}


	public function enableLog() {
		(is_a($this->SofortLib_Multipay, 'SofortLib')) ? $this->SofortLib_Multipay->setLogEnabled() : '';
		(is_a($this->SofortLib_TransactionData, 'SofortLib')) ? $this->SofortLib_TransactionData->setLogEnabled() : '';
		(is_a($this->ConfirmSr, 'SofortLib')) ? $this->ConfirmSr->setLogEnabled() : '';
		return true;
	}


	public function disableLog() {
		(is_a($this->SofortLib_Multipay, 'SofortLib')) ? $this->SofortLib_Multipay->setLogDisabled() : '';
		(is_a($this->SofortLib_TransactionData, 'SofortLib')) ? $this->SofortLib_TransactionData->setLogDisabled() : '';
		(is_a($this->ConfirmSr, 'SofortLib')) ? $this->ConfirmSr->setLogDisabled() : '';
		return true;
	}


	public function log($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->log($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->log($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->log($message);
			return true;
		}

		return false;
	}


	public function logError($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->logError($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->logError($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->logError($message);
			return true;
		}

		return false;
	}


	public function logWarning($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->logWarning($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->logWarning($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->logWarning($message);
			return true;
		}

		return false;
	}


	public function __toString() {
		$string = '<pre>';
		$string .= print_r($this, 1);
		$string .= '</pre>';
		return $string;
	}
}
?>
