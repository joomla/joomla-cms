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

define('VERSION_CLASSIC','1.2.0');

require_once 'sofortLib_http.inc.php';
require_once 'sofortLib_sofortueberweisung_classic.php';
require_once 'sofortLib_Logger.inc.php';
require_once 'sofortLib_ideal_banks.inc.php';
class SofortLib_iDealClassic extends SofortLib_SofortueberweisungClassic {

	private $_apiUrl = '';

	private $_apiKey = '';

	private $_relatedBanks = array();

	private $_SofortLib_iDeal_Banks = null;

	protected $_password;

	protected $_userId;

	protected $_projectId;

	protected $_paymentUrl = 'https://www.sofort.com/payment/ideal';

	protected $_hashFields = array(
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_code',
		'sender_country_id',
		'amount',
		'reason_1',
		'reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
	);


	public function __construct($configKey, $password, $hashFunction = 'sha1') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$this->_password = $password;
		$this->_userId = $this->params['user_id'] = $userId;
		$this->_projectId = $this->params['project_id'] = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->_paymentUrl = $this->_getPaymentDomain();
		$this->_SofortLib_iDeal_Banks = new SofortLib_iDeal_Banks($configKey, $this->_paymentUrl);
	}


	public function setSenderCountryId($senderCountryId = 'NL') {
		$this->params['sender_country_id'] = $senderCountryId;
	}


	public function setSenderBankCode($senderBankCode) {
		$this->params['sender_bank_code'] = $senderBankCode;
		return $this;
	}


	public function getError(){
		return $this->error;
	}


	public function getRelatedBanks() {
		$this->_SofortLib_iDeal_Banks->sendRequest();
		return $this->_SofortLib_iDeal_Banks->getBanks();
	}


	protected function _getPaymentDomain() {
		return (getenv('idealApiUrl') != '') ? getenv('idealApiUrl') : $this->_paymentUrl;
	}
}
?>
