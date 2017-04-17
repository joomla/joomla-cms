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
class SofortLib_TransactionData extends SofortLib_Abstract {

	protected $_parameters = array();

	protected $_response = array();

	protected $_xmlRootTag = 'transaction_request';

	private $_count = 0;


	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		return $this;
	}


	public function setTransaction($arg) {
		$this->_parameters['transaction'] = $arg;
		return $this;
	}


	public function addTransaction($arg) {
		if (is_array($arg)) {
			foreach($arg as $element) {
				$this->_parameters['transaction'][] = $element;
			}
		} else {
			$this->_parameters['transaction'][] = $arg;
		}

		return $this;
	}


	public function setTime($from, $to) {
		$this->_parameters['from_time'] = $from;
		$this->_parameters['to_time'] = $to;
		return $this;
	}


	public function setNumber($number, $page = '1') {
		$this->_parameters['number'] = $number;
		$this->_parameters['page'] = $page;
		return $this;
	}

	public function getConsumerProtection($i = 0) {
		$paymentMethod = $this->getPaymentMethod($i);

		if (in_array($paymentMethod, array('su', 'sv'))) {
			if(isset($this->_response[$i][$paymentMethod]['consumer_protection']['@data'])) {
				return $this->_response[$i][$paymentMethod]['consumer_protection']['@data'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function getInvoiceAddress($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		$invoiceAddress = array(
			'firstname' => $this->_response[$i]['sr']['invoice_address']['firstname']['@data'],
			'lastname' => $this->_response[$i]['sr']['invoice_address']['lastname']['@data'],
			'name_additive' => $this->_response[$i]['sr']['invoice_address']['name_additive']['@data'],
			'street' => $this->_response[$i]['sr']['invoice_address']['street']['@data'],
			'street_number' => $this->_response[$i]['sr']['invoice_address']['street_number']['@data'],
			'street_additive' => $this->_response[$i]['sr']['invoice_address']['street_additive']['@data'],
			'zipcode' => $this->_response[$i]['sr']['invoice_address']['zipcode']['@data'],
			'city' => $this->_response[$i]['sr']['invoice_address']['city']['@data'],
			'country_code' => $this->_response[$i]['sr']['invoice_address']['country_code']['@data'],
			'salutation' => !empty($this->_response[$i]['sr']['invoice_address']['salutation']['@data']) ? $this->_response[$i]['sr']['invoice_address']['salutation']['@data'] : '',
			'company' => $this->_response[$i]['sr']['invoice_address']['company']['@data'],
		);

		return $invoiceAddress;
	}


	public function getShippingAddress($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		$shippingAddress = array(
			'firstname' => $this->_response[$i]['sr']['shipping_address']['firstname']['@data'],
			'lastname' => $this->_response[$i]['sr']['shipping_address']['lastname']['@data'],
			'name_additive' => $this->_response[$i]['sr']['shipping_address']['name_additive']['@data'],
			'street' => $this->_response[$i]['sr']['shipping_address']['street']['@data'],
			'street_number' => $this->_response[$i]['sr']['shipping_address']['street_number']['@data'],
			'street_additive' => $this->_response[$i]['sr']['shipping_address']['street_additive']['@data'],
			'zipcode' => $this->_response[$i]['sr']['shipping_address']['zipcode']['@data'],
			'city' => $this->_response[$i]['sr']['shipping_address']['city']['@data'],
			'country_code' => $this->_response[$i]['sr']['shipping_address']['country_code']['@data'],
			'salutation' => !empty($this->_response[$i]['sr']['shipping_address']['salutation']['@data']) ? $this->_response[$i]['sr']['shipping_address']['salutation']['@data'] : '',
			'company' => $this->_response[$i]['sr']['shipping_address']['company']['@data'],
		);

		return $shippingAddress;
	}


	public function getStatus($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status']['@data'];
	}


	public function getStatusReason($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status_reason']['@data'];
	}


	public function getStatusModifiedTime($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status_modified']['@data'];
	}


	public function getLanguageCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['language_code']['@data'];
	}


	public function getAmount($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['amount']['@data'];
	}


	public function getOrderNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['shop_order_number']['@data'];
	}


	public function getAmountRefunded($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['amount_refunded']['@data'];
	}


	public function getAmountReceived($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sv']['received_amount']['@data'];
	}


	public function getCurrency($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['currency_code']['@data'];
	}


	public function getPaymentMethod($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'];
	}


	public function getTransaction($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['transaction']['@data'];
	}


	public function getItems($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		$items = array();

		if (isset($this->_response[$i]['sr']['items']['item'][0])) {
			foreach ($this->_response[$i]['sr']['items']['item'] as $key => $item) {
				$items[$key]['item_id'] = $item['item_id']['@data'];
				$items[$key]['product_number'] = $item['product_number']['@data'];
				$items[$key]['product_type'] = $item['product_type']['@data'];
				$items[$key]['number_type'] = $item['number_type']['@data'];
				$items[$key]['title'] = $item['title']['@data'];
				$items[$key]['description'] = $item['description']['@data'];
				$items[$key]['quantity'] = $item['quantity']['@data'];
				$items[$key]['unit_price'] = $item['unit_price']['@data'];
				$items[$key]['tax'] = $item['tax']['@data'];
			}
		}

		return $items;
	}


	public function getReason($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		$reasons[] = $this->_response[$i]['reasons']['reason'][0]['@data'];
		$reasons[] = $this->_response[$i]['reasons']['reason'][1]['@data'];
		return $reasons;
	}


	public function getUserVariable($n, $i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		if($n == 0 && !array_key_exists($n, $this->_response[$i]['user_variables']['user_variable'])) {
			return $this->_response[$i]['user_variables']['user_variable']['@data'];
		}

		return $this->_response[$i]['user_variables']['user_variable'][$n]['@data'];
	}


	public function getTime($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['time']['@data'];
	}


	public function getProjectId($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['project_id']['@data'];
	}


	public function getInvoiceUrl($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_url']['@data'];
	}


	public function getInvoiceStatus($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_status']['@data'];
	}


	public function getInvoiceObjection($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_objection']['@data'];
	}


	public function isTest($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['test']['@data'];
	}


	public function isSofortueberweisung($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'] == 'su';
	}


	public function isSofortvorkasse($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'] == 'sv';
	}


	public function isSofortlastschrift($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'] == 'sl';
	}


	public function isLastschrift($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'] == 'ls';
	}


	public function isSofortrechnung($i = 0) {
		if($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['payment_method']['@data'] == 'sr';
	}


	public function isReceived($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status']['@data'] == 'received';
	}

	public function isLoss($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status']['@data'] == 'loss';
	}


	public function isPending($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status']['@data'] == 'pending';
	}


	public function isRefunded($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['status']['@data'] == 'refunded';
	}


	public function getRecipientHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['holder']['@data'];
	}


	public function getRecipientAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['account_number']['@data'];
	}


	public function getRecipientBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['bank_code']['@data'];
	}


	public function getRecipientCountryCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['country_code']['@data'];
	}


	public function getRecipientBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['bank_name']['@data'];
	}


	public function getRecipientBic($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['bic']['@data'];
	}


	public function getRecipientIban($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['recipient']['iban']['@data'];
	}


	public function getSenderHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['holder']['@data'];
	}


	public function getSenderAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['account_number']['@data'];
	}


	public function getSenderBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['bank_code']['@data'];
	}


	public function getSenderCountryCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['country_code']['@data'];
	}


	public function getSenderBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['bank_name']['@data'];
	}


	public function getSenderBic($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['bic']['@data'];
	}


	public function getSenderIban($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sender']['iban']['@data'];
	}


	public function getInvoiceReason($n = 0, $i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		if ($n == 1) {
			return $this->_response[$i]['sr']['reason_1']['@data'];
		}

		if ($n == 2) {
			return $this->_response[$i]['sr']['reason_2']['@data'];
		}

		return array($this->_response[$i]['sr']['reason_1']['@data'], $this->_response[$i]['sr']['reason_2']['@data']);
	}


	public function getInvoiceDebitorText($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['debitor_text']['@data'];
	}


	public function getInvoiceDate($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_date']['@data'];
	}


	public function getInvoiceDueDate($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['due_date']['@data'];
	}


	public function getInvoiceNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_number']['@data'];
	}


	public function getInvoiceBankHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['recipient_bank_account']['holder']['@data'];
	}


	public function getInvoiceBankAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['recipient_bank_account']['account_number']['@data'];
	}


	public function getInvoiceBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['recipient_bank_account']['bank_code']['@data'];
	}


	public function getInvoiceBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['recipient_bank_account']['bank_name']['@data'];
	}


	public function getInvoiceType($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}

		return $this->_response[$i]['sr']['invoice_type']['@data'];
	}


	public function getCount() {
		return $this->_count;
	}


	protected function _parseXml() {
		if (isset($this->_response['transactions']['transaction_details'])) {
			$transactionFromXml = (isset($this->_response['transactions']['transaction_details'][0]))
				? $this->_response['transactions']['transaction_details']
				: $this->_response['transactions'];
			$this->_count = count($transactionFromXml);
			$transactions = array();

			foreach ($transactionFromXml as $transaction) {
				if (!empty($transaction)) {
					if (isset($transaction['sr']['items']['item']) && !isset($transaction['sr']['items']['item'][0])) {
						$tmp = $transaction['sr']['items']['item'];
						unset($transaction['sr']['items']['item']);
						$transaction['sr']['items']['item'][] = $tmp;
						unset($tmp);
					}

					$transactions[] = $transaction;
				}
			}

			$this->_response = $transactions;
			$this->_count = count($transactions);
		} else {
			$this->_count = 0;
		}
	}
}
?>
