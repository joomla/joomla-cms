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
class SofortLib_Refund extends SofortLib_Abstract {

	protected $_parameters = array();

	protected $_response = array();

	protected $_xmlRootTag = 'refunds';


	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('refundApiUrl') != '') ? getenv('refundApiUrl') : 'https://www.sofort.com/payment/refunds';
		parent::__construct($userId, $apiKey, $apiUrl);
	}


	public function sendRequest() {
		parent::sendRequest();
		return $this->getStatusArray();
	}


	public function addRefund($transaction, $amount, $comment = '') {
		$this->_parameters['refund'][] = array(
			'transaction' => $transaction,
			'amount' => $amount,
			'comment' => $comment,
		);
		return $this;
	}


	public function setSenderAccount($bankCode, $accountNumber, $holder = '') {
		$this->_parameters['sender'] = array(
			'holder' => $holder,
			'account_number' => $accountNumber,
			'bank_code' => $bankCode,
		);
		return $this;
	}


	public function setTitle($arg) {
		$this->_parameters['title'] = $arg;
		return $this;
	}


	public function getTransactionId($i = 0) {
		return $this->_response['refunds']['refund'][$i]['transaction']['@data'];
	}


	public function getAmount($i = 0) {
		return $this->_response['refunds']['refund'][$i]['amount']['@data'];
	}


	public function getStatus($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'];
	}


	public function getComment($i = 0) {
		return $this->_response['refunds']['refund'][$i]['comment']['@data'];
	}


	public function getTitle() {
		return $this->_response['refunds']['title']['@data'];
	}


	public function getRefundError($i = 0) {
		return parent::getError('all', $this->_response[$i]);
	}


	public function isRefundError($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'] == 'error';
	}


	public function getDta() {
		return $this->_response['refunds']['dta']['@data'];
	}


	public function getAsArray() {
		return $this->_response;
	}


	public function getStatusArray() {
		$ret = array();

		foreach ($this->_response['refunds']['refund'] as $transaction) {
			$ret[$transaction['transaction']['@data']] = $transaction['status']['@data'];
		}

		return $ret;
	}


	protected function _parseXml() {}


	protected function _handleErrors() {
		if (!isset($this->_response['refunds']['refund'][0])) {
			$tmp = $this->_response['refunds']['refund'];
			unset($this->_response['refunds']['refund']);
			$this->_response['refunds']['refund'][] = $tmp;
		}

		foreach ($this->_response['refunds']['refund'] as $response) {
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}

				foreach ($response['errors']['error'] as $error) {
					$this->errors['global'][] = $this->_getErrorBlock($error);
				}
			}

			if (isset($response['warnings']['warning'])) {
				if (!isset($response['warnings']['warning'][0])) {
					$tmp = $response['warnings']['warning'];
					unset($response['warnings']['warning']);
					$response['warnings']['warning'][0] = $tmp;
				}

				foreach ($response['warnings']['warning'] as $error) {
					$this->warnings['global'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>
