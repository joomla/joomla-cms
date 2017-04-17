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

if(!defined('SOFORTLIB_VERSION')) {
	define('SOFORTLIB_VERSION','1.5.4');
}

require_once dirname(__FILE__).'/sofortLib_abstract.inc.php';
require_once dirname(__FILE__).'/sofortLib_confirm_sr.inc.php';
require_once dirname(__FILE__).'/sofortLib_edit_sr.inc.php';
require_once dirname(__FILE__).'/sofortLib_cancel_sr.inc.php';
require_once dirname(__FILE__).'/sofortLib_ideal_banks.inc.php';
require_once dirname(__FILE__).'/sofortLib_debit.inc.php';
require_once dirname(__FILE__).'/sofortLib_http.inc.php';
require_once dirname(__FILE__).'/sofortLib_multipay.inc.php';
require_once dirname(__FILE__).'/sofortLib_notification.inc.php';
require_once dirname(__FILE__).'/sofortLib_refund.inc.php';
require_once dirname(__FILE__).'/sofortLib_transaction_data.inc.php';
require_once dirname(__FILE__).'/sofortLib_Logger.inc.php';


require_once dirname(__FILE__).'/helper/class.abstract_document.inc.php';
require_once dirname(__FILE__).'/helper/class.invoice.inc.php';
require_once dirname(__FILE__).'/helper/elements/sofort_element.php';
require_once dirname(__FILE__).'/helper/elements/sofort_tag.php';
require_once dirname(__FILE__).'/helper/elements/sofort_html_tag.php';
require_once dirname(__FILE__).'/helper/elements/sofort_text.php';
require_once dirname(__FILE__).'/helper/array_to_xml.php';
require_once dirname(__FILE__).'/helper/xml_to_array.php';


class SofortLib {

	public $errorPos = 'global'; //or su, sr, sv...

	public $errors = array();

	public $warnings = array();

	public $enableLogging = false;

	public $errorCountTemp = 0;

	public $SofortLibHttp = null;

	public $SofortLibLogger = null;

	protected $_apiKey;

	protected $_userId;

	protected $_response;

	protected $_products = array('global', 'sr', 'su', 'sv', 'ls', 'sl', 'sf');

	private $_logfilePath = false;


	public function __construct($userId = '', $apiKey = '', $apiUrl = '') {
		$this->_userId = $userId;
		$this->_apiKey = $apiKey;
		$this->SofortLibHttp = new SofortLib_Http($apiUrl, $this->_getHeaders());
		$this->SofortLibLogger = new SofortLibLogger();
		$this->enableLogging = (getenv('sofortDebug') == 'true') ? true : false;
	}


	public function getWarnings($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->warnings;
		} else {
			$message = $this->_parseErrorresponse($message);
		}

		$supportedPaymentMethods = $this->_products;

		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}

		$returnArray = array();

		foreach ($supportedPaymentMethods as $pm) {
			if (($paymentMethod == 'all' || $pm == 'global' || $paymentMethod == $pm) && array_key_exists($pm, $message)) {
				$returnArray = array_merge($returnArray, $message[$pm]);
			}
		}

		return $returnArray;
	}


	public function getErrors($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->handleErrors($this->errors);
		} else {
			$message = $this->_parseErrorresponse($message);
		}

		if (!$this->isError($paymentMethod, $message)) {
			return array();
		}

		$supportedPaymentMethods = $this->_products;

		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}

		$returnArray = array();

		foreach ($supportedPaymentMethods as $pm) {
			if (($paymentMethod == 'all' || $pm == 'global' || $paymentMethod == $pm) && array_key_exists($pm, $message)) {
				$returnArray = array_merge($returnArray, $message[$pm]);
			}
		}

		return $returnArray;
	}


	function handleErrors($errors) {
		$errorKeys = array_keys($errors);

		foreach($errorKeys as $errorKey) {
			$i = 0;

			foreach ($errors[$errorKey] as $partialError) {
				if (!empty($errors[$errorKey][$i]['field']) && $errors[$errorKey][$i]['field'] !== '') {
					$errors[$errorKey][$i]['code'] .= '.'.$errors[$errorKey][$i]['field'];
				}

				$i++;
			};
		}

		return $errors;
	}


	public function getError($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->errors;
		} else{
			$message = $this->_parseErrorresponse($message);
		}

		$supportedPaymentMethods = $this->_products;

		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}

		if (is_array($message)) {
			if ($paymentMethod == 'all') {
				foreach ($message as $key => $error) {
					if (is_array($error) && !empty($error)){
						return 'Error: '.$error[0]['code'].':'.$error[0]['message'];
					}
				}
			} else {
				foreach ($message as $key => $error) {
					if ($key != 'global' && $key != $paymentMethod) {
						continue;
					}

					if (is_array($error) && !empty($error)){
						return 'Error: '.$error[0]['code'].':'.$error[0]['message'];
					}
				}
			}
		}

		return false;
	}


	public function isWarning($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->warnings;
		}

		if (empty($message)) {
			return false;
		}

		if (!in_array($paymentMethod, $this->_products)) {
			$paymentMethod = 'all';
		}

		if ($paymentMethod == 'all') {
			if (is_array($message)) {
				foreach ($message as $error) {
					if (!empty($error)) {
						return true;
					}
				}
			}
		} else {
			if (is_array($message)) {
				if ((isset($message[$paymentMethod]) && !empty($message[$paymentMethod])) ||
						(isset($message['global']) && !empty($message['global']))) {
					return true;
				}
			}
		}

		return false;
	}


	public function isError($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->errors;
		}

		if (empty($message)) {
			return false;
		}

		if (!in_array($paymentMethod, $this->_products)) {
			$paymentMethod = 'all';
		}

		if ($paymentMethod == 'all') {
			if (is_array($message)) {
				foreach ($message as $error) {
					if (!empty($error)) {
						return true;
					}
				}
			}
		} else {
			if (is_array($message)) {
				if ((isset($message[$paymentMethod]) && !empty($message[$paymentMethod])) ||
						(isset($message['global']) && !empty($message['global']))) {
					return true;
				}
			}
		}

		return false;
	}


	public function setError($message, $pos = 'global', $errorCode = '-1', $field = '') {
		$supportedErrorsPos = array('global', 'sr', 'su', 'sv', 'sa', 'ls', 'sl', 'sf');

		if (!in_array($pos, $supportedErrorsPos)) {
			$paymentMethod = 'global';
		}

		if (!isset($this->errors[$pos])) {
			$this->errors[$pos] = array();
		}

		$error = array ('code' => $errorCode, 'message' => $message, 'field' => $field);
		$this->errors[$pos][] = $error;
	}


	public function deleteAllWarnings() {
		$this->errorPos = 'global';
		$this->errorCountTemp = 0;
		$this->warnings = array();
	}


	public function deleteAllErrors() {
		$this->errorPos = 'global';
		$this->errorCountTemp = 0;
		$this->errors = array();
	}


	protected function _sendMessage($message) {
		$response = $this->SofortLibHttp->post($message);

		if ($response === false) {
			return $this->SofortLibHttp->error;
		}

		$http = $this->SofortLibHttp->getHttpCode();

		if (!in_array($http['code'], array('200', '301', '302'))) {
			return $http['message'];
		}

		return $response;
	}


	private function _getHeaders() {
		$header = array();
		$header[] = 'Authorization: Basic '.base64_encode($this->_userId.':'.$this->_apiKey);
		$header[] = 'Content-Type: application/xml; charset=UTF-8';
		$header[] = 'Accept: application/xml; charset=UTF-8';
		$header[] = 'X-Powered-By: PHP/'.phpversion();
		return $header;
	}


	private function _createErrorArrayStructure() {
		if (!isset($this->errors[$this->errorPos])) {
			$this->errors[$this->errorPos] = array();
		}

		if (!isset($this->errors[$this->errorPos][$this->errorCountTemp])) {
			$this->errors[$this->errorPos][$this->errorCountTemp] = array();
		}
	}


	private function _createWarningArrayStructure() {
		if (!isset($this->warnings[$this->errorPos])) {
			$this->warnings[$this->errorPos] = array();
		}

		if (!isset($this->warnings[$this->errorPos][$this->errorCountTemp])) {
			$this->warnings[$this->errorPos][$this->errorCountTemp] = array();
		}
	}


	private function _backtrace($provideObject = false) {
		$last = '';
		$file = __FILE__;
		$args = '';
		$message = '';

		foreach (debug_backtrace($provideObject) as $row) {
			if ($last != $row['file']) {
				$message .= "File: $file<br>\n";
			}

			$last=$row['file'];
			$message .= ' Line: $row[line]: ';

			if ($row['class']!='') {
				$message .= '$row[class]$row[type]$row[function]';
			} else {
				$message .= '$row[function]';
			}

			$message .= '(';
			$message .= join('', '',$args);
			$message .= ")<br>\n";
		}

		return $message;
	}


	public function error($message, $fatal = false){
		$errorArray = array('message' => 'Error: '.$message, 'code' => '10');
		$this->errors['global'][] = $errorArray;
	}


	public function fatalError($message){
		return $this->error($message, true);
	}


	public function enableLog() {
		$this->enableLogging = true;
		return $this;
	}


	public function disableLog() {
		$this->enableLogging = false;
		return $this;
	}


	public function setLogEnabled() {
		$this->enableLogging = true;
		return $this;
	}


	public function setLogDisabled() {
		$this->enableLogging = false;
		return $this;
	}


	public function setLogger($SofortLibLogger) {
		$this->SofortLibLogger = $SofortLibLogger;
	}


	public function logWarning($message) {
		if ($this->enableLogging) {
			$uri = dirname(__FILE__).'/logs/warning_log.txt';
			$this->SofortLibLogger->log($message, $uri);
		}
	}


	public function logError($message) {
		if ($this->enableLogging) {
			$uri = dirname(__FILE__).'/logs/error_log.txt';
			$this->SofortLibLogger->log($message, $uri);
		}
	}


	public function log($message) {
		if ($this->enableLogging) {
			$uri = $this->_logfilePath ? $this->_logfilePath : dirname(__FILE__).'/logs/log.txt';
			$this->SofortLibLogger->log($message, $uri);
		}
	}


	public function setLogfilePath($path) {
		$this->_logfilePath = $path;
	}


	public function setApiVersion($version) {
		$this->_apiVersion = $version;
	}


	protected function _handleErrors() {
		if (isset($this->_response['errors']['error'])) {
			if (!isset($this->_response['errors']['error'][0])) {
				$tmp = $this->_response['errors']['error'];
				unset($this->_response['errors']['error']);
				$this->_response['errors']['error'][0] = $tmp;
			}

			foreach ($this->_response['errors']['error'] as $error) {
				$this->errors['global'][] = $this->_getErrorBlock($error);
			}
		}

		foreach ($this->_products as $product) {
			if (isset($this->_response['errors'][$product])) {
				if (!isset($this->_response['errors'][$product]['errors']['error'][0])) {
					$tmp = $this->_response['errors'][$product]['errors']['error'];
					unset($this->_response['errors'][$product]['errors']['error']);
					$this->_response['errors'][$product]['errors']['error'][0] = $tmp;
				}

				foreach ($this->_response['errors'][$product]['errors']['error'] as $error) {
					$this->errors[$product][] = $this->_getErrorBlock($error);
				}
			}
		}

		if (isset($this->_response['new_transaction']['warnings']['warning'])) {
			if (!isset($this->_response['new_transaction']['warnings']['warning'][0])) {
				$tmp = $this->_response['new_transaction']['warnings']['warning'];
				unset($this->_response['new_transaction']['warnings']['warning']);
				$this->_response['new_transaction']['warnings']['warning'][0] = $tmp;
			}

			foreach ($this->_response['new_transaction']['warnings']['warning'] as $warning) {
				$this->warnings['global'][] = $this->_getErrorBlock($warning);
			}
		}

		foreach ($this->_products as $product) {
			if (isset($this->_response['new_transaction']['warnings'][$product])) {
				if (!isset($this->_response['new_transaction']['warnings'][$product]['warnings']['warning'][0])) {
					$tmp = $this->_response['new_transaction']['warnings'][$product]['warnings']['warning'];
					unset($this->_response['new_transaction']['warnings'][$product]['warnings']['warning']);
					$this->_response['new_transaction']['warnings'][$product]['warnings']['warning'][0] = $tmp;
				}

				foreach ($this->_response['new_transaction']['warnings'][$product]['warnings']['warning'] as $warning) {
					$this->warnings[$product][] = $this->_getErrorBlock($warning);
				}
			}
		}
	}


	protected function _parseXml() {}


	protected function _getErrorBlock($error) {
		$newError['code'] = isset($error['code']['@data']) ? $error['code']['@data'] : '';
		$newError['message'] = isset($error['message']['@data']) ? $error['message']['@data'] : '';
		$newError['field'] = isset($error['field']['@data']) ? $error['field']['@data'] : '';
		return $newError;
	}


	public static function debug($var = false, $showHtml = false) {
		echo "\n<pre class=\"sofort-debug\">\n";
		$var = print_r($var, true);

		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}

		echo $var . "\n</pre>\n";
	}
}

?>
