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

class PaymentUrl {

    public $amount;
    public $currency;
    public $customData;
    public $customer;
    public $email;
    public $firstName;
    public $ipnUrl;
    public $lastName;
    public $order;
    public $origin;
    public $returnUrl;

    public static function generateUrl($params) {
        $config = Payplug::getConfig();
        $data;
        $signature;

        if (! $config) {
            throw new ParametersNotSetException();
        }
        if (! isset($params['amount'])) {
            throw new MissingRequiredParameterException("Missing required parameter: amount");
        }
        if (! isset($params['currency'])) {
            throw new MissingRequiredParameterException("Missing required parameter: currency");
        }
        if (! isset($params['ipnUrl'])) {
            throw new MissingRequiredParameterException("Missing required parameter: ipnUrl");
        }
        if (! preg_match("/^(http|https):\/\//i", $params['ipnUrl'])) {
            throw new MalformedURLException($params['ipnUrl'] . " doesn't starts with 'http://' or 'https://'");
        }
        if ($params['returnUrl'] != null && ! preg_match("/^(http|https):\/\//i", $params['returnUrl'])) {
            throw new MalformedURLException($params['returnUrl'] . " doesn't starts with 'http://' or 'https://'");
        }


        $url_params = http_build_query(array(
            "amount" => $params['amount'],
            "currency" => $params['currency'],
            "custom_data" => $params['customData'],
            "customer" => $params['customer'],
            "email" => $params['email'],
            "first_name" => $params['firstName'],
            "ipn_url" => $params['ipnUrl'],
            "last_name" => $params['lastName'],
            "order" => $params['order'],
            "origin" => $params['origin'] . " payplug-php" . Payplug::VERSION . " PHP" . phpversion(),
            "return_url" => $params['returnUrl']
        ));
        $data = urlencode(base64_encode($url_params));


        $privateKey = openssl_pkey_get_private($config->privateKey);
        openssl_sign($url_params, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        $signature = urlencode(base64_encode($signature));

        return $config->paymentBaseUrl . "?data=" . $data . "&sign=" . $signature;
    }
}
