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
class SofortLib_EditSr extends SofortLib_Abstract {

	protected $_apiVersion = '1.0';

	protected $_parameters = array();

	protected $_response = array();

	protected $_xmlRootTag = 'edit_sr';

	private $_file;

	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
	}


	public function setTransaction($transaction, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
		return $this;
	}


	public function setInvoiceNumber($invoiceNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['invoice_number'] = $invoiceNumber;
		return $this;
	}


	public function setCustomerNumber($customerNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['customer_id'] = $customerNumber;
		return $this;
	}


	public function setOrderNumber($orderNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['order_id'] = $orderNumber;
		return $this;
	}


	public function setComment($comment, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['comment'] = $comment;
		return $this;
	}


	public function addItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax, $invoice = 0) {
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);
		$this->_parameters['invoice'][$invoice]['items']['item'][] = array(
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


	function updateCart($cartItems = array(), $invoice = 0) {
		$i = 0;

		foreach ($cartItems as $cartItem) {
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['item_id'] = $cartItem['itemId'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['product_number'] = $cartItem['productNumber'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['title'] = $cartItem['title'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['description'] = $cartItem['description'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['quantity'] = $cartItem['quantity'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['unit_price'] = number_format($cartItem['unitPrice'], 2, '.', '') ;
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['tax'] = $cartItem['tax'];
			$i++;
		}
	}


	public function getInvoiceUrl() {
		return $this->_file;
	}


	protected function _parseXml() {
		$this->_file = isset($this->_response['invoice']['download_url']['@data']) ? $this->_response['invoice']['download_url']['@data'] : '';
	}


	protected function _handleErrors() {
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
