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
class SofortLib_Debit extends SofortLib_Abstract {

	protected $_response = array();

	protected $_parameters = array();

	protected $_xmlRootTag = 'debitpay';


	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('debitApiUrl') != '') ? getenv('debitApiUrl') : 'https://www.sofort.com/payment/debitpay/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		$this->setProjectId($projectId);
		$this->setDate(); //set date to today
	}


	public function sendRequest() {
		parent::sendRequest();
		return $this->isError() === false;
	}


	public function setProjectId($id) {
		$this->_parameters['project_id'] = $id;
		return $this;
	}


	public function setDate($date = '') {
		if (empty($date)) {
			$date = date('Y-m-d');
		}

		$this->_parameters['date'] = $date;
		return $this;
	}


	public function setSenderAccount($bankCode, $accountNumber, $holder) {
		$this->_parameters['sl']['sender'] = array(
			'holder' => $holder,
			'account_number' => $accountNumber,
			'bank_code' => $bankCode,
		);
		return $this;
	}


	public function setSenderAccountNumber($accountNumber) {
		$this->_parameters['sl']['sender']['account_number'] = $accountNumber;
		return $this;
	}


	public function setSenderBankCode($bankCode) {
		$this->_parameters['sl']['sender']['bank_code'] = $bankCode;
		return $this;
	}


	public function setSenderHolder($name) {
		$this->_parameters['sl']['sender']['holder'] = $name;
		return $this;
	}


	public function setAmount($amount) {
		$this->_parameters['sl']['amount'] = $amount;
		return $this;
	}


	public function addUserVariable($userVariable) {
		$this->_parameters['sl']['user_variables']['user_variable'][] = $userVariable;
		return $this;
	}


	public function addReason($reason) {
		$this->_parameters['sl']['reasons']['reason'][] = $reason;
		return $this;
	}


	public function setReason($reason1, $reason2 = '') {
		$this->_parameters['sl']['reasons']['reason'][0] = $reason1;
		$this->_parameters['sl']['reasons']['reason'][1] = $reason2;
		return $this;
	}


	public function getTransactionId() {
		return $this->_response['transaction'];
	}


	public function getReason($i = 0) {
		return $this->_response['reasons'][$i];
	}


	public function getAmount() {
		return $this->_response['amount'];
	}


	public function getUserVariable($i = 0) {
		return $this->_response['user_variables'][$i];
	}


	public function getDate() {
		return $this->_response['date'];
	}


	public function isError($paymentMethod = 'all', $message = ''){
		return parent::isError($paymentMethod, $message);
	}


	public function getError($paymentMethod = 'all', $message = '') {
		return parent::getError($paymentMethod, $message);
	}


	public function getResponse() {
		return $this->_response;
	}


	protected function _parseXml() {}
}
?>
