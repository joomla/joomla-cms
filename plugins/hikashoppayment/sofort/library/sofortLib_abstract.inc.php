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
class SofortLib_Abstract extends SofortLib {

	protected $_validateOnly = false;

	protected $_apiVersion = '1.0';


	protected function _parseXml() {
		trigger_error('Missing implementation of parseXml()', E_USER_NOTICE);
	}


	public function sendRequest() {
		$requestData[$this->_xmlRootTag] = $this->_parameters;
		$requestData = $this->_prepareRootTag($requestData);
		$xmlRequest = ArrayToXml::render($requestData);
		$this->_log($xmlRequest, ' XmlRequest -> ');
		$xmlResponse = $this->_sendMessage($xmlRequest);

		try {
			$this->_response = XmlToArray::render($xmlResponse);
		} catch (Exception $e) {
			$this->_response = array('errors' => array('error' => array('code' => array('@data' => '0999'), 'message' => array('@data' => $e->getMessage()))));
		}

		$this->_log($xmlResponse, ' XmlResponse <- ');
		$this->_handleErrors();
		$this->_parseXml();
		return $this;
	}


	protected function _log($xml, $message) {
		$this->log(get_class($this).$message.$xml);
	}


	private function _prepareRootTag($requestData) {
		if ($this->_apiVersion) {
			$requestData[$this->_xmlRootTag]['@attributes']['version'] = $this->_apiVersion;
		}

		if ($this->_validateOnly) {
			$requestData[$this->_xmlRootTag]['@attributes']['validate_only'] = 'yes';
		}

		return $requestData;
	}
}
?>
