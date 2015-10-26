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
class SofortLib_Notification extends SofortLib_Abstract {

	protected $_parameters = array();

	protected $_response = array();

	private $_transactionId = '';

	private $_time;


	public function __construct() {
		parent::__construct('', '', '');
	}


	public function getNotification($source = 'php://input') {
		$data = file_get_contents($source);

		if (!preg_match('#<transaction>([0-9a-z-]+)</transaction>#i', $data, $matches)) {
			$this->log(__CLASS__.' <- '.$data);
			$this->errors['error']['message'] = 'could not parse message';
			return false;
		}

		$this->_transactionId = $matches[1];
		$this->log(__CLASS__.' <- '.$data);
		preg_match('#<time>(.+)</time>#i', $data, $matches);

		if (isset($matches[1])) {
			$this->_time = $matches[1];
		}

		return $this->_transactionId;
	}


	public function sendRequest() {
		trigger_error('sendRequest() not possible in this case', E_USER_NOTICE);
	}


	public function getTime() {
		return $this->_time;
	}


	public function getTransactionId() {
		return $this->_transactionId;
	}
}
?>
