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
require_once('pnag_article.php');
require_once('pnag_customer.php');

require_once('pnag_article.php');
require_once('pnag_article.php');
class PnagAbstractDocument {

	protected $_items = array();

	protected $_customer = null;

	protected $_currency = 'EUR';

	protected $_amount = 0.00;

	protected $_amountRefunded = 0.00;


	public function setItem($itemId, $productNumber = 0, $productType = 0, $title = '', $description = '', $quantity = 0, $unitPrice = '', $tax = '19') {
		array_push($this->_items, new PnagArticle($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax));
		return $this;
	}


	public function getItems() {
		return $this->_items;
	}


	public function getHighestShoparticleTax() {
		$highestTax = 0;

		foreach ($this->_items as $item) {
			if ($item->getTax() > $highestTax) {
				$highestTax = $item->getTax();
			}
		}

		return $highestTax;
	}


	public function setCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->_customer = new PnagCustomer($name, $lastname, $firstname, $company, $csID, $vatId, $shopId, $Id, $cIP, $streetAddress, $suburb, $city, $postcode, $state, $country, $formatId, $telephone, $emailAddress);
		return $this;
	}


	public function setCurrency($currency) {
		$this->_currency = $currency;
		return $this;
	}


	private function _calcAmount() {
		$this->_amount = 0.0;
		foreach($this->_items as $item) {
			$this->_amount += $item->unitPrice * $item->quantity;
		}
		return $this;
	}


	public function getAmount() {
		return $this->_amount;
	}
}
?>
