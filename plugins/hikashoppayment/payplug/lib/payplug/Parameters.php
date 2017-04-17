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

class Parameters {

    public static function createFromString($str) {
        $array = json_decode($str, true);

        return new Parameters(
            $array["currencies"],
            $array["maxAmount"],
            $array["minAmount"],
            $array["paymentBaseUrl"],
            $array["payplugPublicKey"],
            $array["privateKey"]
        );
    }

    public static function loadFromFile($path) {
        return self::createFromString(file_get_contents($path));
    }

    public $currencies;
    public $maxAmount;
    public $minAmount;
    public $paymentBaseUrl;
    public $payplugPublicKey;
    public $privateKey;

    public function __construct($currencies, $maxAmount, $minAmount, $paymentBaseUrl, $payplugPublicKey, $privateKey) {
        $this->currencies = $currencies;
        $this->maxAmount = $maxAmount;
        $this->minAmount = $minAmount;
        $this->paymentBaseUrl = $paymentBaseUrl;
        $this->payplugPublicKey = $payplugPublicKey;
        $this->privateKey = $privateKey;
    }

    public function saveInFile($path) {
        file_put_contents($path, $this->toString());
    }

    public function toString() {
        return json_encode($this);
    }
}
