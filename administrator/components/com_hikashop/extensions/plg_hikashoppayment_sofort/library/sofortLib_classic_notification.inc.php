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
class SofortLib_ClassicNotification {

	public $params = array();

	private $_password;

	private $_userId;

	private $_projectId;

	private $_hashFunction;

	private $_hashCheck = false;

	private $_statusReason;


	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1') {
		$this->_password = $password;
		$this->_userId = $userId;
		$this->_projectId = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->_statusReason = false;
	}


	public function getNotification($request) {
		if (array_key_exists('status_reason', $request) && !empty($request['status_reason'])) {
			$this->_statusReason = $request['status_reason'];
		}

		if (array_key_exists('international_transaction', $request)) {
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_code', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic', 'recipient_iban', 'recipient_country_id',
				'international_transaction', 'amount', 'currency_id', 'reason_1', 'reason_2', 'security_criteria',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		} else {
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic',	'recipient_iban', 'recipient_country_id',
				'amount', 'currency_id', 'reason_1', 'reason_2',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		}

		if (array_key_exists('status', $request) && !empty($request['status'])) {
			array_push($fields, 'status', 'status_modified');
		}

		$this->params = array();

		foreach ($fields as $key) {
			$this->params[$key] = $request[$key];
		}

		$this->params['project_password'] = $this->_password;
		$validationHash = $this->_getHashHexValue(implode('|', $this->params), $this->_hashFunction);
		$messageHash = $request['hash'];
		$this->_hashCheck = ($validationHash === $messageHash);
		return $this;
	}


	public function isError() {
		if (!$this->_hashCheck) {
			return true;
		}

		return false;
	}


	public function getError() {
		if (!$this->_hashCheck) {
			return 'hash-check failed';
		}

		return false;
	}


	public function getTransaction() {
		return $this->params['transaction'];
	}


	public function getAmount() {
		return $this->params['amount'];
	}


	public function getUserVariable($i = 0) {
		return $this->params['user_variable_'.$i];
	}


	public function getCurrency() {
		return $this->params['currency_id'];
	}


	public function getTime() {
		return $this->params['created'];
	}


	public function getStatus() {
		return $this->params['status'];
	}


	public function getStatusReason() {
		return $this->_statusReason;
	}


	protected function _getHashHexValue($data, $hashFunction = 'sha1') {
		if ($hashFunction == 'sha1') {
			return sha1($data);
		}

		if ($hashFunction == 'md5') {
			return md5($data);
		}

		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}

		return false;
	}
}
?>
