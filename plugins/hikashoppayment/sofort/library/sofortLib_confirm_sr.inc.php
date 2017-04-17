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
class SofortLib_ConfirmSr extends SofortLib_Abstract {

	protected $_parameters = array();

	protected $_invoices = array();

	protected $_response = array();

	protected $_xmlRootTag = 'confirm_sr';

	protected $_apiVersion = '2.0';

	private $_file;


	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
	}


	public function setTransaction($transaction, $invoice = 0) {
		if ($this->_apiVersion == 1) {
			$this->_parameters['transaction'] = $transaction;
		} else {
			$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
		}

		return $this;
	}


	public function setInvoiceNumber($invoiceNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['invoice_number'] = $invoiceNumber;
		return $this;
	}


	public function setCustomerNumber($customerNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['customer_id'] = $customerNumber;
		return $this;
	}


	public function setOrderNumber($orderNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['order_id'] = $orderNumber;
		return $this;
	}


	public function setComment($comment) {
		$this->setApiVersion('1.0');
		$this->_parameters['comment'] = $comment;
		return $this;
	}


	public function addItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax) {
		$this->setApiVersion('1.0');
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);
		$this->_parameters['items']['item'][] = array(
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


	public function removeItem($productId, $quantity = 0) {
		$this->setApiVersion('1.0');

		if (!isset($this->_parameters['items']['item'][$productId])) {
			return false;
		} elseif ($quantity = -1) {
			unset($this->_parameters['items']['item'][$productId]);
			return true;
		}

		return true;
	}

	function updateCart($cartItems = array()) {
		$this->setApiVersion('1.0');

		if (empty($cartItems)) {
			$this->_parameters['items'] = array();
			return $this;
		}

		$i = 0;

		foreach ($cartItems as $cartItem) {
			$this->_parameters['items']['item'][$i]['item_id'] = $cartItem['itemId'];
			$this->_parameters['items']['item'][$i]['product_number'] = $cartItem['productNumber'];
			$this->_parameters['items']['item'][$i]['title'] = $cartItem['title'];
			$this->_parameters['items']['item'][$i]['description'] = $cartItem['description'];
			$this->_parameters['items']['item'][$i]['quantity'] = $cartItem['quantity'];
			$this->_parameters['items']['item'][$i]['unit_price'] = number_format($cartItem['unitPrice'], 2, '.', '') ;
			$this->_parameters['items']['item'][$i]['tax'] = $cartItem['tax'];
			$i++;
		}

		return $this;
	}


	public function cancelInvoice($transaction = '') {
		$this->setApiVersion('1.0');

		if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
			$transaction = $this->_parameters['transaction'];
		}

		if (!empty($transaction)) {
			$this->_parameters = NULL;
			$this->_parameters['transaction'] = $transaction;
			$this->_parameters['items'] = array();
		}

		return $this;
	}


	public function confirmInvoice($transaction = '') {
		if ($this->_apiVersion == 1) {
			if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
				$transaction = $this->_parameters['transaction'];
			}

			if (!empty($transaction)) {
				$this->_parameters = NULL;
				$this->_parameters['transaction'] = $transaction;
			}
		} else {
			if (!empty($transaction)) {
				$this->_parameters['invoice'][0]['transaction'] = $transaction;
			}
		}

		return $this;
	}


	public function getInvoiceUrl($i = 0) {
		return isset($this->_response['invoice'][$i]['download_url']['@data']) ? $this->_response['invoice'][$i]['download_url']['@data'] : '';
	}

	protected function _parseXml() {}


	protected function _handleErrors() {
		if ($this->_apiVersion == 1) {
			return parent::_handleErrors();
		}

		if (!isset($this->_response['invoices']['invoice'][0])) {
			$tmp = $this->_response['invoices']['invoice'];
			unset($this->_response['invoices']['invoice']);
			$this->_response['invoices']['invoice'][0] = $tmp;
		}

		foreach ($this->_response['invoices']['invoice'] as $response) {
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}

				foreach ($response['errors']['error'] as $error) {
					$this->errors['sr'][] = $this->_getErrorBlock($error);
				}
			}

			if (isset($response['warnings']['warning'])) {
				if (!isset($response['warnings']['warning'][0])) {
					$tmp = $response['warnings']['warning'];
					unset($response['warnings']['warning']);
					$response['warnings']['warning'][0] = $tmp;
				}

				foreach ($response['warnings']['warning'] as $error) {
					$this->warnings['sr'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>
