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
class SofortLib_CancelSr extends SofortLib_Abstract {

	protected $_apiVersion = '1.0';

	protected $_parameters = array();

	protected $_response = array();

	protected $_xmlRootTag = 'cancel_sr';

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


	public function setComment($comment, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['comment'] = $comment;
		return $this;
	}


	public function setCreditNoteNumber($creditNoteNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['credit_note_number'] = $creditNoteNumber;
		return $this;
	}


	public function cancelInvoice($transaction = '', $invoice = 0) {
		if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
			$transaction = $this->_parameters['invoice'][$invoice]['transaction'];
		}

		if (!empty($transaction)) {
			$this->_parameters = NULL;
			$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
			$this->_parameters['invoice'][$invoice]['items'] = array();
		}

		return $this;
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
