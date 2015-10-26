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
class PnagCustomer {

	public $name = '';

	public $lastname = '';

	public $firstname = '';

	public $company = '';

	public $csID = '';

	public $vatId = '';

	public $shopId = '';

	public $Id = '';

	public $cIP = '';

	public $streetAddress = '';

	public $suburb = '';

	public $city = '';

	public $postcode = '';

	public $state = '';

	public $country = '';

	public $formatId = '';

	public $telephone = '';

	public $emailAddress = '';


	public function PnagCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->name = $name;
		$this->lastname = $lastname;
		$this->firstname = $firstname;
		$this->company = $company;
		$this->csID = $csID;
		$this->vatId = $vatId;
		$this->shopId = $shopId;
		$this->Id = $Id;
		$this->cIP = $cIP;
		$this->street_address = $streetAddress;
		$this->suburb = $suburb;
		$this->city = $city;
		$this->postcode = $postcode;
		$this->state = $state;
		$this->country = $country;
		$this->formatId = $formatId;
		$this->telephone = $telephone;
		$this->emailAddress = $emailAddress;
	}
}
?>
