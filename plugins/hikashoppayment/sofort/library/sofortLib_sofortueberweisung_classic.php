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
require_once 'sofortLib_classic_notification.inc.php';
class SofortLib_SofortueberweisungClassic {

	public $params = array();

	protected $_password;

	protected $_userId;

	protected $_projectId;

	protected $_hashFunction;

	protected $_paymentUrl = 'https://www.sofort.com/payment/start';

	protected $_hashFields = array(
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_code',
		'sender_country_id',
		'amount','currency_id',
		'reason_1','reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
	);


	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1', $paymentUrl = null) {
		$this->_password = $password;
		$this->_userId = $this->params['user_id'] = $userId;
		$this->_projectId = $this->params['project_id'] = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->params['encoding'] = 'UTF-8';
		if ($paymentUrl) $this->_paymentUrl = $paymentUrl;
		$this->_paymentUrl = $this->_getPaymentDomain();
	}


	public function setAmount($arg, $currency = 'EUR') {
		$this->params['amount'] = $arg;
		$this->params['currency_id'] = $currency;
	}


	public function setSenderHolder($senderHolder) {
		$this->params['sender_holder'] = $senderHolder;
	}


	public function setSenderAccountNumber($senderAccountNumber) {
		$this->params['sender_account_number'] = $senderAccountNumber;
	}


	public function setReason($arg, $arg2 = '') {
		$this->params['reason_1'] = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg);
		$this->params['reason_2'] = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg2);
		return $this;
	}


	public function addUserVariable($arg) {
		$i = 0;

		while ($i < 6) {
			if (array_key_exists('user_variable_'.$i, $this->params)) {
				$i++;
			} else {
				break;
			}
		}

		$this->params['user_variable_'.$i] = $arg;
		return $this;
	}


	public function setSuccessUrl($arg) {
		$this->params['user_variable_3'] = $arg;
		return $this;
	}


	public function setAbortUrl($arg) {
		$this->params['user_variable_4'] = $arg;
		return $this;
	}


	public function setNotificationUrl($arg) {
		$this->params['user_variable_5'] = $arg;
		return $this;
	}


	public function setVersion($arg) {
		$this->params['interface_version'] = $arg;
		return $this;
	}


	public function getPaymentUrl() {
		$hashFields = $this->_hashFields;
		$hashString = '';

		foreach ($hashFields as $value) {
			if (array_key_exists($value, $this->params)) {
				$hashString .= $this->params[$value];
			}

			$hashString .= '|';
		}

		$hashString .= $this->_password;
		$hash = $this->getHashHexValue($hashString, $this->_hashFunction);
		$this->params['hash'] = $hash;
		$paramString = '';

		foreach ($this->params as $key => $value) {
			$paramString .= $key.'='.urlencode($value).'&';
		}

		$paramString = substr($paramString, 0, -1); //remove last &
		return $this->_paymentUrl.'?'.$paramString;
	}


	public function isError() {
		return false;
	}


	public function getError() {
		return false;
	}


	public function getHashHexValue($data, $hashFunction = 'sha1') {
		if($hashFunction == 'sha1') {
			return sha1($data);
		}

		if($hashFunction == 'md5') {
			return md5($data);
		}

		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}

		return false;
	}


	public static function generatePassword($length = 24) {
		$password = '';

		do {
			$randomBytes = '';
			$strong = false;

			if (function_exists('openssl_random_pseudo_bytes')) { //php >= 5.3
				$randomBytes = openssl_random_pseudo_bytes(32, $strong);//get 256bit
			}

			if (!$strong) { //fallback
				$randomBytes = pack('I*', mt_rand()); //get 32bit (pseudo-random)
			}

			$password .= preg_replace('#[^A-Za-z0-9]#', '', base64_encode($randomBytes));
		} while (strlen($password) < $length);

		return substr($password, 0, $length);
	}


	public function getSupportedHashAlgorithm() {
		$algorithms = $this->getSupportedHashAlgorithms();

		if(is_array($algorithms)) {
			return $algorithms[0];
		} else {
			return ''; //no hash function found
		}
	}


	public function getSupportedHashAlgorithms() {
		$algorithms = array();

		if (function_exists('hash') && in_array('sha512', hash_algos())) {
			$algorithms[] = 'sha512';
		}

		if(function_exists('hash') && in_array('sha256', hash_algos())) {
			$algorithms[] = 'sha256';
		}

		if(function_exists('sha1'))	{ //deprecated
			$algorithms[] = 'sha1';
		}

		if(function_exists('md5')) { //deprecated
			$algorithms[] = 'md5';
		}

		return $algorithms;
	}


	protected function _getPaymentDomain() {
		return (getenv('sofortPaymentUrl') != '') ? getenv('sofortPaymentUrl') : $this->_paymentUrl;
	}
}
?>
