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
class PnagArticle {

	public $itemId = '';

	public $productNumber = '';

	public $productType = '';

	public $title = '';

	public $description = '';

	public $quantity = '';

	public $unitPrice = '';

	public $tax = '';


	public function __construct($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax) {
		$this->itemId = $itemId;
		$this->productNumber = $productNumber;
		$this->productType = $productType;
		$this->title = $title;
		$this->description = $description;
		$this->quantity = $quantity;
		$this->unitPrice = $unitPrice;
		$this->tax = $tax;
	}


	public function getItemId () {
		return $this->itemId;
	}


	public function getQuantity() {
		return $this->quantity;
	}


	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}


	public function getUnitPrice() {
		return $this->unitPrice;
	}


	public function setUnitPrice($unitPrice) {
		$this->unitPrice = $unitPrice;
	}


	public function getTitle() {
		return $this->title;
	}


	public function getTax() {
		return $this->tax;
	}


	public function setTax($value) {
		$this->tax = $value;
	}


	public function setProductNumber($productNumber) {
		$this->productNumber = $productNumber;
	}


	public function getProductNumber() {
		return $this->productNumber;
	}


	public function setDescription($description) {
		$this->description = $description;
	}


	public function getDescription() {
		return $this->description;
	}
}
?>
